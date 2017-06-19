///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 3
// File: smallsh.c
// Date: 05 March 2017
// Description: Simple shell interface containing three built-in functions 
// (cd, status, exit) and otherwise uses fork()/exec() to run commands.  Has 
// support for foreground and background processes.  Demonstrates the use of
// signal handlers, although I have had to resort to using deprecated function
// signal() in a couple of places due to errors. Basic program structure based 
// on Hiran Ramankutty "Writing Your Own Shell" tutorial on Linux Gazette:
// http://linuxgazette.net/111/ramankutty.html and 
// http://linuxgazette.net/111/misc/ramankutty/listing9.c.txt
///////////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <signal.h>
#include <string.h>
#include <sys/types.h>
#include <errno.h>
#include <fcntl.h>

#define MAXBUF 2048
#define MAXARGS 512


// struct to hold command-line components after parsing
typedef struct cmdline{
    char *args[MAXARGS];// arguments array; args[0] is the command
    int bg;             // flag whether process is background or not    
    char *infile;       // input filename for redirect
    char *outfile;      // output filename for redirect
} cmdline;

// Global variable flag for SIGTSTP signal handler that toggles fg-enabled mode
// http://beej.us/guide/bgipc/output/html/multipage/signals.html
// http://bytes.com/topic/c/answers/220980-volatile-sig_atomic_t-redundant#post895830
volatile sig_atomic_t bgmode;
volatile sig_atomic_t st;

// function prototypes
void siginthand(int sig);
void sigtstp_handler(int sig);               
void sighand(int sig, siginfo_t *info, void *context);
void parseInput(char *input, cmdline *cl);  
int call_execvp(cmdline *cl);               
void check(int status);
void harvest();
void free_args(char **args);
void prompt();
char *itos(int i, char b[]);


///////////////////////////////////////////////////////////////////
//                            Main                               //
///////////////////////////////////////////////////////////////////                   
int main(){
    char c;                 // for reading input
    char tmp[MAXBUF] = {0}; // input buffer
    char cwd[1024] = {0};   
    int status = -99;

    // handle SIGTERM as part of exit process
    // ignore for now, will reset to default in child process later
    struct sigaction sigterm_action = {0};
    sigterm_action.sa_handler = SIG_IGN;
    sigaction(SIGTERM,&sigterm_action,NULL);

    // handle SIGUSR2 as part of exit process
    // set sa_flags to SA_SIGINFO in order to use sa_sigaction
    struct sigaction sigusr = {0};
    sigusr.sa_sigaction = &sighand;
    sigusr.sa_flags = SA_SIGINFO;
    sigaction(SIGUSR2,&sigusr,NULL);

    // handle SIGTSTP (^Z) to toggle foreground-only mode
    bgmode = 0;                         
    signal(SIGTSTP,sigtstp_handler);        // deprecated, but sigaction keeps resulting in
    //struct sigaction ctrlZ = {0};         // junk input that I have been unable to trace 
    //ctrlZ.sa_handler = &sigtstp_handler; 
    //ctrlZ.sa_flags = 0;                 
    //sigaction(SIGTSTP,&ctrlZ,NULL);
    signal(SIGINT, SIG_IGN);                // similar issues as above        
    //signal(SIGINT, siginthand);
    sigaction(SIGINT, &sigusr,NULL);
    // Initial prompt
    prompt();

    while (1){
        cmdline *cl = malloc(sizeof(cmdline));  // place to hold parsed cmd
        cl->bg = 0;                             // initialize contents to 0
        cl->infile = NULL;
        cl->outfile = NULL;
        memset(cl->args,'\0',sizeof(cl->args));

        getcwd(cwd,1024);                       
        c = getchar();                      // read input
        switch(c){
            case '\n':                      // newline found
                // if blank line, reprompt
                if (tmp[0] == '\0'){        
                    prompt();
                // if comment line, reprompt
                } else if (tmp[0] == '#'){  
                    prompt();
                } else {            
                    // otherwise parse input
                    parseInput(tmp, cl); 
                    // prevent disaster if no args were added  
                    if (cl->args[0] == NULL){
                        free_args(cl->args);
                        free(cl);
                        prompt(); 
                        memset(tmp, 0, sizeof(tmp));
                        break;
                    }
                    
                    // built-in command cd
                    if (!strcmp(cl->args[0], "cd")){
                        // if no args, cd to HOME environment variable
                        if (cl->args[1] == NULL){
                            char *home;
                            home = getenv("HOME");
                            chdir(home);
                        // otherwise cd to the given argument
                        } else {
                            // if unsuccessful print a message
                            if (chdir(cl->args[1]) == -1){
                                printf("Invalid dir %s/%s\n", cwd, cl->args[1]);
                                fflush(stdout);
                            }
                        }

                    // built-in command status
                    } else if (!strcmp(cl->args[0], "status")){
                        // check termination status of last foreground child process
                        check(status);
                    // built-in command exit
                    } else if (!strncmp(tmp, "exit", 4)){
                        // send a SIGTERM which will send SIGINT to all children
                        // in the handler
                        raise(SIGUSR2);
                        free_args(cl->args);
                        free(cl);
                        return 0;

                    // otherwise, not a built-in function, fork a child and
                    // call execvp 
                    } else {
                        status = call_execvp(cl);
                    }
                        // attempt some cleanup
                        free_args(cl->args);
                        free(cl);           
                        // check for zombies 
                        harvest();          
                        prompt();           
                }
                
                // clear out input in prep for next loop
                memset(tmp, 0, sizeof(tmp));
                break;

            // keep building up input string until newline
            default: 
                strncat(tmp, &c, 1);
                break;
        }
    }

    return 0;
}


/*
void siginthand(int sig){
    write(1,"terminated by signal 2\n",23);
    fflush(stdout);                 
}
*/


/////////////////////////////////////////////////////////////////////
// signal handler for SIGUSR2, called at exit
// Sends SIGTERM to all my children
/////////////////////////////////////////////////////////////////////
void sighand(int sig, siginfo_t *info, void *context){
char sn[2] = {0};
    switch(sig){        
        case SIGUSR2:
            // send a kill signal to all processes in this group,
            // thereby killing off any children I spawned
            // SIGTERM reset to default behavior in children during fork
            // but parent will ignore this and return normally
            kill(-info->si_pid,SIGTERM);        
            break;
        case SIGINT:
            write(1,"terminated by signal ",21);
            write(1,itos(sig,sn),1);
            write(1,"\n",1);
            fflush(stdout);                 
            break;
    }
    fflush(stdout);
}


/////////////////////////////////////////////////////////////////////
// ^Z signal handler toggles between fg-only mode and bg-enabled mode
// when sent SIGTSTP
// params: sig number
/////////////////////////////////////////////////////////////////////
void sigtstp_handler(int sig){
    // toggle mode
    bgmode = 1 - bgmode;
    // if we're already in bg-enabled mode, notify we're turning it off
    if(bgmode){
        char *message = "\nEntering foreground-only mode (& is now ignored)\n: ";
        write(STDOUT_FILENO, message, 52);
        fflush(stdout);
    // otherwise notify user it's back on
    } else {
        char *message = "\nExiting foreground-only mode\n: ";
        write(STDOUT_FILENO, message, 32);
        fflush(stdout);
    }
}


/////////////////////////////////////////////////////////////////////
// calls execvp; based on Ramankutty call_execve(char *) function
// with added support for redirection and backgrounding
// params: parsed cmdline struct
// returns status of the waited-on foreground process in order to 
// send that back to main() 
/////////////////////////////////////////////////////////////////////
int call_execvp(cmdline *cl){
    int retval;                 // execvp return value
    pid_t chpid;                // forked child pid
    pid_t wpid;                 // waited-on child pid     
    int stat = -99;             // child return status
    int infd, outfd, result;    // redirection

    // fork a child
    chpid = fork();

    // declare signal handlers
    struct sigaction sigterm_action = {0};
    struct sigaction sigtstp_action = {0};
    struct sigaction sigignore_action = {0};

    switch (chpid){
        case -1:
            perror("Fork error");
            exit(1);
            break;

        // child process
        case 0:
            // children ignore SIGTSTP
            sigignore_action.sa_handler = SIG_IGN;
            sigaction(SIGTSTP,&sigignore_action,NULL);
            // restore default SIGTERM to term children on exit    
            sigterm_action.sa_handler = SIG_DFL;
            sigaction(SIGTERM,&sigterm_action,NULL);
            // ignore SIGINT unless foreground child, then use SIG_DFL
            sigaction(SIGINT,&sigignore_action,NULL);
            if (!cl->bg){
                sigaction(SIGINT,&sigterm_action,NULL);
            }

            // handle redirection
            // if there was an input file specified, redirect stdin to it
            if (cl->infile != NULL){
                infd = open(cl->infile, O_RDONLY);
                if (infd == -1){
                    fprintf(stderr,"cannot open %s for input",cl->infile);
                    //perror(cl->infile);
                    exit(1);
                }
                // clone file descriptors for exec
                result = dup2(infd,0);
                close(infd);
                if (result == -1){
                    perror("input dup2()");
                    exit(2);
                }    
            // if there was no input redirection but the process should be 
            // backgrounded, redirect stdin to /dev/null
            } else {
                if (cl->bg){    
                    infd = open("/dev/null",O_RDONLY);
                    dup2(infd,0);
                    close(infd);
                }
            }

            // if there was an output file specified, redirect stdout
            if (cl->outfile != NULL){
                outfd = open(cl->outfile, O_WRONLY | O_CREAT | O_TRUNC, 0644);
                if (outfd == -1){
                    fprintf(stderr,"cannot open %s for output",cl->outfile);
                    //perror(cl->outfile);
                    exit(1);
                }    
                result = dup2(outfd,1);
                close(outfd);
                if (result == -1){
                    perror("output dup2()");
                    exit(2);
                }    
            // if there was no output redirection but the process should be 
            // backgrounded, redirect stdout to /dev/null
            } else {
                if (cl->bg){    
                    outfd = open("/dev/null",O_WRONLY);
                    dup2(outfd,1);
                    close(outfd);
                }
            }

            // call execvp with cl->args array
            retval = execvp(cl->args[0], cl->args);
            //  exit if unable to execute command
            if (retval < 0) {
                perror(cl->args[0]);
                exit(1);
            }
            break;

        // parent process
        default:       
            if (cl->bg){
                printf("\nbackground pid is %d\n",chpid);
                fflush(stdout);
            }
            // if this should not be backgrounded, then wait for child to finish before
            // continuing parent process
            if (!cl->bg){
                wpid = waitpid(chpid, &stat, 0);
            }       
            break;
    }

    // return the status of the last foreground process
    return stat;
}


/////////////////////////////////////////////////////////////////////
// Parse command line input and fill a cmdline struct with the pieces.
// Each word goes into the args array, with args[0] being the command. 
// If < or > is found, next word goes to infile or outfile instead of 
// the args array.  If & is found, return, since that should be last
// item on the line.
// Tokenizing process based on parse_command function from:
// http://web2.clarkson.edu/class/cs444/labs/lab01/shellWithParser/parse.c
// Arguments: input string, ptr to cmdline struct to hold parsed command
/////////////////////////////////////////////////////////////////////
void parseInput(char *input, cmdline *cl){ 
    int i = 0;              // position in orig input
    int pos = 0;            // position in a word of the cmdline
    int nargs = 0;          // number of args found so far in cmdline
    char word[MAXBUF-6];    // place to hold each word as its parsed 
    int inflag = 0;         // flags for redirection
    int outflag = 0;

    // if nothing to parse, return 
    if (input[i] == '\0')
        return;

    // loop through input line til end is reached
    while (input[i] != '\0'){

        // copy input line char by char into word until you hit a space
        // and make sure to null-terminate
        while (input[i] != '\0' && !isspace(input[i])){
            word[pos++] = input[i++];
        }
        word[pos] = '\0';

        // Variable expansion for $$ in command line
        char *var = NULL;   // place to hold return value for strstr()

        // If $$ is found in the command line
        if ((var = strstr(word, "$$")) != NULL){
            // Insert one string into another one: 
            // http://stackoverflow.com/2016015
            char word2[MAXBUF];         // place to put expanded value
            // copy pid into a string
            char mypid[6];                
            sprintf(mypid, "%d", getpid());
            // copy the word up to where $$ was found into a new word 
            int x = var - word;           
            strncpy(word2, word, x);
            word2[x] = '\0';
            // copy anything after $$ in original word into the new one
            x += 2;
            strcat(word2, mypid);
            strcat(word2, word + x);
            // copy everything back into the original word
            strcpy(word, word2);   
        }

        // set background flag, do not add anything to args array
        if (!strcmp(word, "&")){
            // only set the flag if bgmode (global var) is on 
            if (!bgmode){
                cl->bg = 1;
            }
            return;    
        }

        // flag for < or > redirection
        if (!strcmp(word, "<")){
            inflag = 1;
        } else if (!strcmp(word, ">")){
            outflag = 1;
        } else {
            // if < or > was found in the previous loop (flag set), 
            // copy this word into infile or outfile as appropriate
            // and clear the flag
            if (inflag){
                cl->infile = malloc(sizeof(char) * strlen(word)+1);
                strcpy(cl->infile, word);
                inflag = 0;
            } else if (outflag){
                cl->outfile = malloc(sizeof(char) * strlen(word)+1);
                strcpy(cl->outfile, word);
                outflag = 0;
            // otherwise copy this word into the argument array
            } else {
                cl->args[nargs] = malloc(sizeof(char) * strlen(word)+1);
                strcpy(cl->args[nargs], word);
                nargs++;
            }  
        }
        
        // reset word for next loop through
        word[0] = '\0';
        pos = 0;

        // skip whitespace
        while(isspace(input[i])){
            i++;
        }
    }
}


/////////////////////////////////////////////////////////////////////
// check status of child process that has ended. Based off of 
// Block 3 lecture slides
// params: int status of the process
/////////////////////////////////////////////////////////////////////
void check(int status){
    // junk value
    if (status == -99)
        return;

    // check if exited
    if (WIFEXITED(status)){
        int chExit = WEXITSTATUS(status);
        printf("exit value %d\n",chExit);
        fflush(stdout);
    }
    // check if terminated by signal
    if (WIFSIGNALED(status)){
        int chTerm = WTERMSIG(status);
        printf("terminated by signal %d\n",chTerm);
        fflush(stdout);                 
    }

    return;
}


/////////////////////////////////////////////////////////////////////
// harvest function to wait on dying child processes.
// called before prompt() in control loop in main()
// params: none
/////////////////////////////////////////////////////////////////////
void harvest(){
    int pid;
    int stat;

    while(1){
        // get child pid number
        pid = waitpid(-1,&stat,WNOHANG);
        // if successful, check status of process and print message
        if(pid>0){
            printf("background pid %d is done: ",pid);
            fflush(stdout);
            check(stat);
        } else {
            return;
        }
    }
}


/////////////////////////////////////////////////////////////////////
// Free memory that was allocated for arguments array in cmdline
// based on Ramankutty
// params: arr of strings to be freed
/////////////////////////////////////////////////////////////////////
void free_args(char **args){
    int i;

    // loop through the args string array until you hit an empty one
    for (i = 0; args[i] != NULL; i++){
        // zero out the string in that element of the array
        memset(args[i],0, strlen(args[i])+1);
        args[i] = NULL;
        // free the allocated memory
        free(args[i]);
    }
}


/////////////////////////////////////////////////////////////////////
// Print command prompt
/////////////////////////////////////////////////////////////////////
void prompt(){
    printf(": ");
    fflush(stdout);
}


/////////////////////////////////////////////////////////////////////
// Convert int to string for writing in signal handler, since I can't
// figure out how else to write ints.  Function cam from:
// http://stackoverflow.com/a/9660930
/////////////////////////////////////////////////////////////////////
char *itos(int i, char b[]){
    char const digit[] = "0123456789";
    char *p = b;                // point to first pos in string
    if (i < 0){                 // if i is neg, then the first char will be '-'
        *p++ = '-';
        i *= -1;                // change i to positive number
    }
    int shifter = i;            // figure out how many places are needed for i
    do {
        ++p;                    
        shifter = shifter/10;
    } while (shifter);
    *p = '\0';                  // null-term the "i string" and place 
    do {                        // digits into each place from ones place on up
        *--p = digit[i%10];
        i = i/10;
    } while (i);
    return b;
}

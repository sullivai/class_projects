///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 2
// File: sullivai.adventure.c
// Date: 14 February 2017
// Description: User interface file for adventure game. Takes user input to
// traverse room files in most-recent rooms directory and to check time using
// a mutex lock.
///////////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <dirent.h>
#include <pthread.h>

#define MAX_DIRNAME 22  // sullivai.rooms. = 15 + /proc/sys/kernel/pid_max = 32768 + \0
#define MAX_ROWS 8
#define MAX_DATA 12
#define MAXLINE 80

// Mutex and global resource that needs to be locked
// This was helpful: https://youtu.be/axwGR80q0Qw
pthread_mutex_t lock;
char *timefile = "currentTime.txt";

// Other thread
void *writeTime(void *filename);

// Useful function
int contains (char *arr[], int n, char *x);
// Useless functions (prompts and stuff)
void prompt();
void wrong();
void orientation(char *fileDump[], int rows);
void finish(char *path[], int steps);


///////////////////////////////////////////////////////////////////////////////
//                                MAIN                                       //
///////////////////////////////////////////////////////////////////////////////
int main(){
    // Thread to get system time and write to a file, not spawned yet
    pthread_t writethread;
    int wthret;
    
    // Lock in order to block the second thread
    pthread_mutex_lock(&lock);
 
    // Spawn background thread to sit and wait
    wthret = pthread_create(&writethread,NULL,writeTime,(void *)timefile);
   
    DIR *dirp;              // Open directory stream
    struct dirent *dp;      // Directory entity from which to get path name
    struct stat fileStats;  // Results of stat() call
    char *latestDir;        // Name of dir to look for room files
    time_t latest = 0;      // Used in comparison to find last modified dir

    // Allocate space for name of directory
    latestDir = malloc(MAX_DIRNAME*sizeof(char)); 

    // Open current directory
    dirp = opendir(".");
    if (dirp == NULL){
        perror("Cannot open .");
        exit(1);
    }

    // Read through the files in the directory to look for where
    // the most recently-generated room files are located
    while ((dp = readdir(dirp)) != NULL) {
        // If a directory/file is found that starts with 
        // "<MY ONID USERNAME>.rooms." then call stat() on it
        // to get when it was modified
        if(strncmp(dp->d_name,"sullivai.rooms.",15) == 0){
            stat(dp->d_name, &fileStats);
            // If its mod time is later than whatever was already
            // marked as "latest", then replace the value of "latest"
            // with its mod time and save its name in latestDir
            if (difftime(fileStats.st_mtime,latest) > 0){
               latest = fileStats.st_mtime;
               strcpy(latestDir,dp->d_name);   
            }
        }
    }   
    // Close the stream
    closedir(dirp);


    // Within the directory identified above as the latest, 
    // look for the start room using popen() to pipe grep command
    // result to a string. Source:  http://stackoverflow.com/a/29559542
    // grep -l prints name of file that matches pattern; -r recursively
    // processes all files in the named directory. Since I know there's 
    // only one START_ROOM, grep will return a single filename.
    char getStart[48] = "grep -lr 'START' ";    // Build up command string
    strcat(getStart,latestDir);                 // Add directory to look in
    FILE *cmd;                                  // Command stream
    char result[48] = {0};                      // Store grep result

    // Open stream with command to grep for 'START' in last-mod rooms dir
    cmd = popen(getStart,"r");
    if (cmd == NULL) {
        perror("Cannot process command");
        exit(1);
    }
    // Otherwise read output of the command into a string 
    fgets(result,sizeof(result),cmd);
    // Close the stream
    pclose(cmd);

    // Take off the newline that seems to be at the end of the grep result
    // and use the rest to identify file to open
    char filename[48] = {0};
    strncpy(filename, result,strlen(result)-1);


    // char *route[1000] = {0};    // Array to store route 
    // Allocate space to store the route steps.  Malloc is used so that I can
    // use realloc later to increase the array if necessary.
    int cap = 2;                                // Initial capacity of route array
    char **route = malloc(cap * sizeof(*route));// Route array
    int steps = 0;                              // Step counter
    int endNotFound = 1;                        // Variables used to check 
    char rmType[4] = {0};                       // when the end room is found
    memset(route,'\0',cap);

    do {

        // Initialize space to store user input.  Accepts one longer than 
        // my longest room name in order to check for divergence all the way
        // to the end.
        char *input;
        input = malloc(MAX_DATA*sizeof(char));
        memset(input,'\0',MAX_DATA);

        // You are here. Open room file from filename
        FILE *URHereFile;       
        URHereFile = fopen(filename,"r");
        if(URHereFile == NULL){
            perror("Error opening file");
            exit(1);
        }

        // If file opens successfully, dump contents into an array
        char line[MAXLINE] = {0};   // Line of file
        char data[MAX_DATA] = {0};  // Relevant string I want from each line
        char *URhere[MAX_ROWS]= {0};// Array containing current room file's contents
        int i = 0;                  // Keep track of number of rows read in

        // Read in each line from the file        
        while (fgets(line, MAXLINE, URHereFile) != NULL){
            // Parse the line to get relevant data
            sscanf(line,"%12s %12s %12s", data, data, data);
            char *temp;                           // Temp pointer for data string
            temp = malloc(MAX_DATA*sizeof(char)); // Allocate some space for it
            memset(temp,'\0',MAX_DATA);           // Make it shiny and new
            sprintf(temp,data);                   // Put the string from the file into it
            URhere[i] = temp;                     // Add it to the file contents array
            i++;                            
        }     

        // Close the stream
        fclose(URHereFile);

        int rows = i;   // Keep number of rows read in from the file 
        
        // Compare room type (last row) to see if this is the end room
        strncpy(rmType, URhere[rows-1],3);
        endNotFound = strncmp(rmType,"END",3);

        // If they are the same (this is the end) then quit the while loop 
        if (!endNotFound) {
            // Free up malloc'd memory
            free(input);
            input = 0;
            for (i=0; i<MAX_ROWS; i++){
                free(URhere[i]);
                URhere[i] = 0;
            }
            continue;
        }
        
        // Otherwise print the location, room options, and prompt
        orientation(URhere, rows);
        prompt();

        // Get up to MAX_DATA chars of user input
        fgets(input,MAX_DATA,stdin);
        // Get rid of the newline that fgets stores
        input[strlen(input)-1] = '\0';

        // Check if the user asked for the time
        while (strcmp(input, "time") == 0){
            printf("\n");
            // Unlock the filename, letting the second thread that's been
            // waiting in the wings all this time run
            pthread_mutex_unlock(&lock);
            // Don't do anything else til the get/write time thread is done
            pthread_join(writethread,NULL);
            // Lock the mutex and spawn the other thread to wait in case I need it again
            pthread_mutex_lock(&lock);
            wthret = pthread_create(&writethread,NULL,writeTime,(void *)timefile);

            // Read the file and display the contents
            char line[MAXLINE];

            // Access currentTime.txt filestream for reading current time
            FILE *fp;
            fp = fopen(timefile, "r");
            if (fp == NULL){
                perror("Error opening currentTime");
                exit(1);
            }
            // Display the time
            while (fgets(line,MAXLINE,fp) != NULL){
                puts(line);
            }
            fclose(fp);
            
            // Prompt for next input
            prompt();
            fgets(input,MAX_DATA,stdin);
            input[strlen(input)-1] = '\0';
        }

        // Check if the user input matches one of the available connections
        // Function returns the row the name was found on (1-6) or 0 if 
        // not found (0th row contains current location name, not a connection)
        int found = contains(URhere,rows,input); 

        // If user input was not among possible connections, print a
        // message to that effect and quit the loop without incrementing
        // step count
        if (!found){ 
            printf("\n");
            wrong();
            printf("\n");
            // Free up malloc'd stuff
            free(input);
            input = 0;
            for (i=0; i<MAX_ROWS; i++){
                free(URhere[i]);
                URhere[i] = 0;
            }
            continue;
        }

        // Otherwise connection is valid.  Build up new filepath based on
        // the connection's room name. Start with directory, concatenate
        // file name
        strcpy(filename,latestDir);
        strcat(filename, "/");
        strcat(filename,URhere[found]);

        // Clean up space allocated for user input
        free(input);
        input = 0;

        // If route array is full, double its capacity using realloc
        // http://stackoverflow.com/a/21950559
        if (steps == cap-1){
            route = realloc(route,cap*2*sizeof(char**));
            cap *= 2;
        }
        // Allocate space to put the name of the next room in 
        // the route list and copy the name into that list
        route[steps] = malloc(MAX_DATA*sizeof(char));
        memset(route[steps],'\0',MAX_DATA);
        strcpy(route[steps],URhere[found]);

        // Increment step counter
        steps++;
          
        // Clean up array that URHereFile was dumped into 
        for (i=0; i<MAX_ROWS; i++){
            free(URhere[i]);
            URhere[i] = 0;
        }
          
        printf("\n");   // For readability and to match assignment sample output

    } while (endNotFound);

    // Print ending message, step count, and route taken
    finish(route, steps);

    // Clean up allocated memory
    int i;
    for (i = 0; i < steps; i++){
        free(route[i]);
    }
    free(route);
    free(latestDir);
    latestDir = 0;

    pthread_cancel(writethread);
    pthread_mutex_unlock(&lock);
    pthread_mutex_destroy(&lock);
    return 0;
}


///////////////////////////////////////////////////////////////////////////////
// function: writeTime
// purpose: gets the current time and write it, formatted, to a file
// params: pointer to the filename shared resource
//////////////////////////////////////////////////////////////////////////////
void *writeTime(void *filename){
    // Lock critical area
    pthread_mutex_lock(&lock);

    char buf[MAXLINE];    
    time_t current;
    struct tm *local;

    // Get current time of system
    // Convert to local time
    // Format time string h:mm(am|pm) Dayname, Monthname, dd, yyyy)
    // http://fresh2refresh.com/c-programming/c-time-related-functions
    current = time(NULL);
    local = localtime(&current);
    strftime(buf, MAXLINE, "%-l:%M%P, %A, %B %d, %Y", local);
    //strftime(buf, MAXLINE, "%l:%M%P %A, %B %d, %Y - %s", local);  // Testing

    // Access the currentTime.txt filestream for writing current time
    FILE *fp;
    fp = fopen(filename,"w");
    if (fp == NULL){
        perror("Error in writeTime()\n");
        exit(1);
    }
    // Write the formatted time
    fprintf(fp,"%s", buf);
    fclose(fp);

    // Unlock it
    pthread_mutex_unlock(&lock);
    return NULL;
}


///////////////////////////////////////////////////////////////////////////////
// function: contains
// purpose: determines if one string is found in an array of strings; if so, 
// returns the index number of the found string in the array
// params: array of char* list of viable connections, number of rows to 
// loop through, char* string to search for
///////////////////////////////////////////////////////////////////////////////
int contains (char *arr[], int n, char *x){
    int i = 0;
    for (i = 1; i < n-1; i++){
        // If search string matches an item in the list, return the index
        if (strcmp(arr[i],x) == 0){
            return i;
        }
    }
    // Otherwise return 0
    return 0;
}


///////////////////////////////////////////////////////////////////////////////
// function: prompt
// purpose: display user input prompt
// params: none
//////////////////////////////////////////////////////////////////////////////
void prompt(){
    printf("WHERE TO? >");
}


///////////////////////////////////////////////////////////////////////////////
// function: wrong
// purpose: display a message when the user input doesn't match a viable option
// params: none
///////////////////////////////////////////////////////////////////////////////
void wrong(){
    printf("HUH? I DON'T UNDERSTAND THAT ROOM. TRY AGAIN.\n");
}


///////////////////////////////////////////////////////////////////////////////
// function: orientation
// purpose: prints the room you are in and the available connections
// params: array of char* file contents, int number of rows
//////////////////////////////////////////////////////////////////////////////
void orientation(char *fileDump[], int rows){
    int i;
    // First row is the current room
    printf("CURRENT LOCATION: %s\n", fileDump[0]);
    printf("POSSIBLE CONNECTIONS: ");
    // Loop through remaining rows (excluding the last row which is the
    // room type and 2nd-to-last row to match punctuation) and print the 
    // connections
    for (i = 1; i < rows-2; i++){
        printf("%s, ", fileDump[i]);
    }
    // Print the last connection and match the punctuation
    printf("%s.\n", fileDump[i]);
}


///////////////////////////////////////////////////////////////////////////////
// function: finish
// purpose: display ending message including # of steps taken and listing the
// names of all rooms visited on the way
// params: array of char* path, int number of steps
//////////////////////////////////////////////////////////////////////////////
void finish(char *path[], int steps){
    int i;
    char *plural = (steps > 1 ? "S" : "");
    printf("YOU HAVE FOUND THE END ROOM. CONGRATULATIONS!\n");
    printf("YOU TOOK %d STEP%s. YOUR PATH TO VICTORY WAS:\n",steps, plural);
    // Loop through the path array and print each item
    for (i=0; i < steps; i++){
        printf("%s\n",path[i]);
    }
}


///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 4
// File: otp_enc_d.c
// Date: 17 March 2017
// Description: One-time pad encryption daemon using network sockets.  The 
// structure and body of this code is based closely on the server.c code  
// provided with the assignemnt. Listens on a network port/socket and forks off
// a child process when a connection is made.  In the child process, the client
// is verified before encrypting the message, and the process returns either a 
// rejection message or the ciphertext as appropriate.                    
///////////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include "utils.h"


///////////////////////////////////////////////////////////////////////////////
//                                   MAIN                                    //
///////////////////////////////////////////////////////////////////////////////
int main(int argc, char *argv[]){
    int listenSocketFD, establishedConnectionFD, portNumber, charsRead;
    socklen_t sizeOfClientInfo;
    char buffer[MAXBUF];        // Complete message
    char readbuf[1024];         // Message chunks
    struct sockaddr_in serverAddress, clientAddress;
    int chpid;                  // Forked child process
    int status;
    char *tmp;                  
    char *cipher;

    // Check usage and args
    if (argc < 2) {fprintf(stderr, "USAGE: %s port\n", argv[0]); exit(1); } 

    // Set up the address struct for this process (the server)
    memset((char *)&serverAddress, '\0', sizeof(serverAddress));    // Clear address struct
    portNumber = atoi(argv[1]);                                     // Get port number, convert to integer from string
    serverAddress.sin_family = AF_INET;                             // Create network-capable socket
    serverAddress.sin_port = htons(portNumber);                     // Store the port number
    serverAddress.sin_addr.s_addr = INADDR_ANY;                     // Any address allowed to connect to this process

    // Set up the socket
    listenSocketFD = socket(AF_INET, SOCK_STREAM, 0); // Create socket
    if (listenSocketFD < 0) error("ERROR opening socket");

    // Enable the socket to begin listening
    if (bind(listenSocketFD, (struct sockaddr *)&serverAddress, sizeof(serverAddress)) < 0) // Connect socket to port
        error("ERROR on binding");
    // Flip the socket on, it can receive up to 5 connections
    if (listen(listenSocketFD, 5) < 0){
        error("ERROR on listen()");
    }
   
    // Loop to listen for connections
    while (1){
        // Accept a connection, block if one not available
        // Get size of address for the client that will connect
        sizeOfClientInfo = sizeof(clientAddress); 

        // Accept connection and generate communication socket
        establishedConnectionFD = accept(listenSocketFD, (struct sockaddr *)&clientAddress, &sizeOfClientInfo);
        if (establishedConnectionFD < 0) error("ERROR on accept");

        chpid = fork();
        switch(chpid) {
            case -1:
                error("ERROR on fork");
                break;
            case 0: // child
                // Loop until entire message has been received (look for terminal character #)
                // Lecture 4.2 slides - Receiving Data Using Control Codes
                while (strstr(buffer,"#")==NULL){
                    // Clear the buffer
                    memset(readbuf,'\0',sizeof(readbuf));
                    // Read in a chunk of text
                    charsRead = recv(establishedConnectionFD, readbuf,sizeof(readbuf)-1,0);
                    // Build up the complete message buffer from chunks
                    strcat(buffer,readbuf);
                    // Error out if problem or no chars read
                    if(charsRead < 1){error("ERROR reading from socket"); break;}
                    if(charsRead == 0) break;
                }

                // Verify the client and reject if unauthorized
                if(strncmp(buffer, "ENC@",4)){
                    charsRead = send(establishedConnectionFD, "REJECT&", 7, 0);
                } else {
                    // Copy message buffer because of strtok
                    tmp = malloc(sizeof(buffer));
                    memset(tmp,'\0',sizeof(tmp));
                    strcpy(tmp,buffer);              
                    // Get plaintext
                    char *plain;
                    plain = strtok_r(tmp,"@",&tmp);
                    plain = strtok_r(tmp,"&",&tmp);
                    // Get key
                    char *key = strtok_r(tmp,"#",&tmp);
                    // Encrypt
                    cipher = malloc(sizeof(plain));
                    memset(cipher,'\0', sizeof(cipher));
                    int q;
                    for (q = 0; q < strlen(plain); q++){
                        cipher[q] = reascii(enc(unascii(plain[q]),unascii(key[q])));
                    }

                    // Send back ciphertext 
                    long len = strlen(cipher);
                    long total = 0;
                    while (total < len){
                        charsRead = send(establishedConnectionFD, cipher+total,len-total,0);
                        total += charsRead;
                        if (charsRead < 0) error("ERROR writing to socket");
                    }
                }
                exit(0);
                break;
            default: // parent
                // Close the existing socket connected to the client
                close(establishedConnectionFD); 
                // Wait for zombie children
                do {
                    chpid = waitpid(-1,&status,WNOHANG);
                } while (chpid > 0);
        }   
    } 
    // Close the listening socket
    close(listenSocketFD); 
    return 0;
}

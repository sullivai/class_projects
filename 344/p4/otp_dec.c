///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 4
// File: otp_dec.c
// Date: 17 March 2017
// Description: Client program that connects to otp_dec_d via network sockets
// and sends a ciphertext file and a key file to be decrypted and gets plaintext
// back. The structure and body of this code is based closely on the client.c code  
// provided with the assignemnt. Validates that both ciphertext and key file 
// contain only authorized characters before sending to the server for decryption.
///////////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include "utils.h"


///////////////////////////////////////////////////////////////////////////////
//                                   MAIN                                    //
///////////////////////////////////////////////////////////////////////////////
int main(int argc, char *argv[]){
    int socketFD, portNumber;
    long charsWritten, charsRead;
    struct sockaddr_in serverAddress;
    struct hostent* serverHostInfo;
    char buffer[MAXBUF];
    char readbuf[1024];    

    // Check usage and args
    if (argc < 4){ 
        fprintf(stderr,"USAGE: %s plaintext key port\n", argv[0]); 
        exit(0); 
    } 

    // Read in plain text and key from files
    char *ciphertext = dumpfile(argv[1]);
    char *key = dumpfile(argv[2]);
    
    // If key is shorter than plaintext then exit    
    if(strlen(key) < strlen(ciphertext)){ 
        fprintf(stderr,"Error: key '%s' is too short\n",argv[2]); 
        exit(1); 
    }

    // If key or plaintext have bad chars then exit
    if(!validate(ciphertext) || !validate(key)){
        fprintf(stderr,"otp_enc error: input contains bad characters\n");
        exit(1);
    }

    // Set up the server address struct
    memset((char*)&serverAddress, '\0', sizeof(serverAddress)); // Clear out the address struct
    portNumber = atoi(argv[3]);                                 // Get the port num, convert to int from string
    serverAddress.sin_family = AF_INET;                         // Create a network-capable socket
    serverAddress.sin_port = htons(portNumber);                 // Store the port number
    serverHostInfo = gethostbyname("localhost");                //Convert the machine name into a special form of address
    if (serverHostInfo == NULL) { fprintf(stderr, "CLIENT: ERROR, no such host\n"); exit(0); }
    memcpy((char*)&serverAddress.sin_addr.s_addr, (char*)serverHostInfo->h_addr, serverHostInfo->h_length); // Copy in addr

    // Set up socket
    socketFD = socket(AF_INET, SOCK_STREAM, 0); // Create socket
    if (socketFD < 0) error("CLIENT: ERROR opening socket");

    // Connect to server
    if (connect(socketFD, (struct sockaddr*)&serverAddress, sizeof(serverAddress)) < 0){ // Connect socket to addr
        fprintf(stderr,"Error: could not contact otp_dec_d on port %d\n",portNumber);
        exit(2);
    }

    // Combine client identifier, plaintext string, and key into one string to send to server
    // Class piazza site, answer @487
    memset(buffer, '\0', sizeof(buffer));   // Clear out buffer array
    strcpy(buffer,"DEC@");                  // Client ID
    strcat(buffer,ciphertext);              // Ciphertext string
    strcat(buffer,"&");                     // Delimiter
    strcat(buffer,key);                     // Key string
    strcat(buffer,"#");                     // Terminal char

    long len = strlen(buffer);              // Size of message to be sent
    long total = 0;                         // Amount of message sent so far


    // Keep sending until all has been sent
    while (total < len){
        // Running total of bytes sent
        charsWritten = send(socketFD, buffer+total, len-total, 0); // Write to server
        total += charsWritten;
        if (charsWritten < 0) error("CLIENT: ERROR writing to socket");
        if (charsWritten < strlen(buffer)) printf("CLIENT: WARNING: Not all data written to socket!\n");
    }

    // Get return message from server
    memset(buffer, '\0', sizeof(buffer));   // Clear out buffer for reuse
    total = 0;                              // Reset total and len
    len = strlen(ciphertext);
    // Run until either term char '&' is found or entire string has been received
    while (strstr(buffer,"&")==NULL && total < len){
        memset(readbuf,'\0',sizeof(readbuf));
        charsRead = recv(socketFD, readbuf, sizeof(readbuf)-1, 0);  // Read data from the socket, leaving \0 at the end
        strcat(buffer,readbuf);
        total += charsRead;
        if (charsRead < 0) error("CLIENT: ERROR reading from socket");
    }

    // If tries to connect to otp_dec_d, reject connection, report rejection to stderr, terminate self 
    char msg[8] = {0};
    strncpy(msg,buffer,7);
    if (!strncmp("REJECT&",msg,7)){
        fprintf(stderr,"ERROR: otp_dec cannot use otp_enc_d\n");
        exit(1);
    }
    fprintf(stdout,"%s\n", buffer);

    close(socketFD); // close the socket
    free(ciphertext);
    free(key);
    return 0;
}


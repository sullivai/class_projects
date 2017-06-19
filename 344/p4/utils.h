///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 4
// File: utils.h
// Date: 17 March 2017
// Description: Header file for a few functions that are used in the all five
// programs for manipulating the char values for encrypting/decrypting, 
// generating keys, and printing errors.
///////////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#define MAXBUF 150000


///////////////////////////////////////////////////////////////////////////////
// Error function from the client/server files provided with the assignemtnt
///////////////////////////////////////////////////////////////////////////////
void error(const char *msg){ 
    error(msg); 
    exit(1); 
}


///////////////////////////////////////////////////////////////////////////////
// Make sure input (key or plaintext) contains only authorized characters
// param: input string
///////////////////////////////////////////////////////////////////////////////
int validate(char *str){
    int i = 0;
    char ok[] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ ";  // Acceptable characters

    // For each char of input, check if it can be found in the array
    // of acceptable characters
    for (i = 0; i < strlen(str); i++){
        // If a char is not found, return false
        if (strchr(ok, str[i]) == NULL){
            return 0;            
        }
    }
    // If all chars were found, return true
    return 1;      
}


///////////////////////////////////////////////////////////////////////////////
// Dump file contents (key or plaintext) into an array for easier manipulation
// param: filename string
///////////////////////////////////////////////////////////////////////////////
char* dumpfile(char *filename){
    // Open specified file for reading
    FILE *fp = fopen(filename,"r");
    if (!fp){
        error("no such file");
    }
    // Get the file length
    fseek(fp,0,SEEK_END);
    long fsize = ftell(fp);
    rewind(fp);

    // Read file contents into an array
    char *out = malloc(fsize);
    memset(out, 0, sizeof(out));
    fread(out, fsize-1,1,fp);
    fclose(fp);

    // Return the array
    return out;
}


///////////////////////////////////////////////////////////////////////////////
// "Shift" ASCII value of char so the range is from 0 to 26 rather than 
// from 65 (A) to 90 (Z) and 32 (space)
// parameter: integer value of char to be converted
///////////////////////////////////////////////////////////////////////////////
int unascii(int c){
    // If this char is a space then call it 91 (1 past Z on ASCII table)
    if (c == 32)
        c = 91;

    // Subtract 65 so all acceptable characters are in range 0-26   
    c -= 65;

    return c;
}


///////////////////////////////////////////////////////////////////////////////
// "Shift" value of char back to ascii value and restore space 
// parameter: integer value of char to be converted
///////////////////////////////////////////////////////////////////////////////
int reascii(int c){
    // Add 65 so this is correct value in ASCII table
    c += 65;

    // If this was a space then give it correct ASCII value 
    if (c == 91)
        c = 32;

    return c;
}


///////////////////////////////////////////////////////////////////////////////
// Encode a char using a key 
// parameters: integer value of plaintext char and key char
///////////////////////////////////////////////////////////////////////////////
int enc(int p, int k){
    int c = 0;

    // Cipher char = plain char + key char
    c = p + k;

    // Use modular arithmetic to wrap around the acceptable chars
    if (c > 26)
        c -= 27;

    return c;
}


///////////////////////////////////////////////////////////////////////////////
// Decode a char using a key 
// parameters: integer value of ciphertext char and key char
///////////////////////////////////////////////////////////////////////////////
int dec(int c, int k){
    int p = 0;

    // Plain char = cipher char - key char
    p = c - k;

    // Use modular arithmetic to wrap around
    if (p < 0)
        p += 27;

    return p;
}


///////////////////////////////////////////////////////////////////////////////
// Generate a key of specified length
// parameters: integer length of string to be created, string to store key in
///////////////////////////////////////////////////////////////////////////////
void kg(int len, char *key){
    int i = 0;
    int j = 0;

    // Make array of acceptable chars to use (0 = A ... 25 = Z)
    int ok[27] = {0};
    for (i = 0; i < 27; i++)
        ok[i] = reascii(i);

    // Generate key of required length
    // For each char of the key, generate random number from 0 - 26
    // and use that as the index to pull a char from the acceptable char array
    for (i = 0; i < len; i++){
        j = rand() % 27;
        key[i] = ok[j];
    }
}

///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 4
// File: keygen.c
// Date: 17 March 2017
// Description: Generates a random key of the spcified length from among the 
// authorized characters.
///////////////////////////////////////////////////////////////////////////////
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <time.h>
#include "utils.h"


///////////////////////////////////////////////////////////////////////////////
//                                   MAIN                                    //
///////////////////////////////////////////////////////////////////////////////
int main(int argc, char *argv[]){
    // Seed RNG
    int seed = time(NULL);
    srand(seed);

    // Check that correct number of args was provided
    if (argc != 2) { fprintf(stderr, "USAGE: %s keylength\n", argv[0]); exit(0); }
    
    // If so, convert 2nd arg to integer and add 1 to hold the newline at the end
    int len = atoi(argv[1]) + 1;
    char *key = malloc(len * sizeof(char));
    memset(key,0,sizeof(key));

    // Generate the key
    kg(len, key);

    // Add the newline
    key[len-1] = '\n';

    // Output the key
    fprintf(stdout,"%s",key);

    free(key);
    return 0;
}


///////////////////////////////////////////////////////////////////////////////
// Name: Aimee Sullivan (sullivai)
// Assignment: CS344-400 Winter 2017 Project 2
// File: sullivai.buildrooms.c
// Date: 14 February 2017
// Description: Room building file for dventure game. 
// a mutex lock.
///////////////////////////////////////////////////////////////////////////////
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <string.h>

#define RM_MAX 7    

// Struct to hold room information
struct Room {
    char rmName[10];                    // Name (longest is 9 chars)
    char rmType[11];                    // Type
    struct Room *connections[RM_MAX];   // Array of connection rooms
    int numConn;                        // Number of connections
};


int randBetween(int min, int max);
struct Room* initRm(char *rmName);
void addConn (struct Room *rm1, struct Room *rm2);
int contains (struct Room *rm, struct Room *x);
void printRm(struct Room *rm);



///////////////////////////////////////////////////////////////////////////////
//                                MAIN                                       //
///////////////////////////////////////////////////////////////////////////////
int main(){

    int seed = time(NULL);
    srand(seed);

    // The room names
    char* roomNames[10] = {
        "helmand", 
        "kandahar", 
        "laghman", 
        "parwan", 
        "ghazni", 
        "herat", 
        "nangarhar", 
        "khost", 
        "kabul", 
        "zabul" }; 

    // The room types
    char* roomTypes[3] = { "START_ROOM", "END_ROOM", "MID_ROOM" };

    int i = 0;
    int sel = 0;            // Random selection corresponding to an index
    int selected[10] = {0}; // Array to help determine selection of unique rooms
    struct Room *maze[RM_MAX];   // Array of rooms for the adventure game

    // Chooose 7 random rooms from the 10 room names and add to the maze array
    while(i < RM_MAX){
        // Generate a random number within the index range of the roomNames array
        sel = randBetween(0,9);
        // If this index has not already been chosen, then initialize a new 
        // struct Room with the room name at that index 
        if (selected[sel] == 0 ) {
            struct Room *room = initRm(roomNames[sel]);
            // First two rooms will be assigned types START_ROOM and END_ROOM
            // respectively; all the others will be MID_ROOMs
            if (i < 2) {
                strcpy(room->rmType, roomTypes[i]);
            } else {
                strcpy(room->rmType, roomTypes[2]);
            }
            // Assign the new room into the maze array
            maze[i] = room;
            // Mark that index as already chosen to prevent repeats
            selected[sel] = 1;
            // Keep going until we've got 7 unique rooms
            i++;
        }
    }

    // Loop through each room of the maze to Make connections to other rooms.
    for (i = 0; i < RM_MAX; i++){ 
        // For each room, make 3 connections (later rooms will already have some connections so 
        // the total will be more than three, but they will all have at least three)
        int conn = 0;
        while (conn < 3){
            // Generate random number between 0-6 to get the index of another room in the maze
            sel = randBetween(0,6);
            // If this room doesn't already have 6 connections... 
            if (maze[i]->numConn < 6){    
                // ... and this room isn't already connected to the generated-index room
                // ... and that room isn't already connected to this one
                // ... and the generated index isn't this room's ineax
                // ... and the generated-index room also doesn't already have 6 connections
                if (contains(maze[i],maze[sel]) == 0 && 
                    contains(maze[sel],maze[i]) == 0 && 
                    maze[i] != maze[sel] && 
                    maze[sel]->numConn < 6){
                        // Make a connection between the two rooms and increment the counter
                        addConn(maze[i],maze[sel]);
                        conn++;
                    }
            // Otherwise if this room does have 6 conections, break out of the while loop       
            } else {
                break; 
            }
        }
    }

    char dirname[22];   // Directory name
    char pid[6];        // Process ID

    // Store the pid in a string
    sprintf(pid,"%d",getpid());
    // Concatenate the pid to my onid id rooms directory    
    strcpy(dirname,"sullivai.rooms.");
    strcat(dirname,pid);

    // Make rooms directory in current dir and set permissions
    mkdir(dirname,0777);

    // Make room files
    for (sel = 0; sel < RM_MAX; sel++){
        // Make the full directory path/name for each room file in the directory
        // using the name of the room as the name of the file
        char path[40];
        strcpy(path,dirname);
        strcat(path,"/");
        strcat(path,maze[sel]->rmName);
        // Open a filestream for writing
        FILE *f = fopen(path,"w");
        // Write name, connections list, and type as formatted in assignment specs
        fprintf(f, "ROOM NAME: %s\n", maze[sel]->rmName);
        for (i = 0; i < maze[sel]->numConn; i++){
            fprintf(f, "CONNECTION %d: %s\n",(i+1), maze[sel]->connections[i]->rmName);
        }
        fprintf(f, "ROOM TYPE: %s\n",maze[sel]->rmType);
        fclose(f);
    }

    // Clean up memory
    for (i = 0; i < RM_MAX;i++){
        free(maze[i]);
    }

    return 0;
}


///////////////////////////////////////////////////////////////////////////////
// function: randBetween
// purpose: simple formula to generate random numbers in a range; produces 
// biased results apparently but I'm not fussed about that for this application
// params: int min, int max
//////////////////////////////////////////////////////////////////////////////
int randBetween(int min, int max){
    return rand() % (max - min + 1) + min;
}


///////////////////////////////////////////////////////////////////////////////
// function: initRm
// purpose: initialize a struct room, allocating space for the name and zeroing
// the number of connections and the connection pointers
// params: room name string
//////////////////////////////////////////////////////////////////////////////
struct Room* initRm(char *rmName){
    // Allocate space for name string and copy it into the name member
    struct Room *rm = malloc(sizeof(*rm));
    strcpy(rm->rmName, rmName); 
    // Zero number of connections
    rm->numConn = 0;
    // Set all connections to null
    int i;
    for (i = 0; i < RM_MAX; i++){
        rm->connections[i] = 0;
    }

    return rm;
}


///////////////////////////////////////////////////////////////////////////////
// function: addConn
// purpose: adds a connection between two rooms (reciprocal)
// params: two pointers to room structs to be connected to each other
//////////////////////////////////////////////////////////////////////////////
void addConn (struct Room *rm1, struct Room *rm2){
    // Add second room to next slot in first room's connections array
    rm1->connections[rm1->numConn] = rm2;
    // Increment the room's number of connections
    rm1->numConn += 1;
    // Repeat for the second room
    rm2->connections[rm2->numConn] = rm1;
    rm2->numConn += 1;
}


///////////////////////////////////////////////////////////////////////////////
// function: contains
// purpose: check to see if a particular room is in another room's list of   
// connections
// params: room *x to be checked within connection array of another room *rm
//////////////////////////////////////////////////////////////////////////////
int contains (struct Room *rm, struct Room *x){
    int i = 0;
    // Loop thru rm's connections
    for (i = 0; i < RM_MAX; i++){
        // If x is there, return true
        if (rm->connections[i] == x){
            return 1;
        }
    }
    return 0;
}


///////////////////////////////////////////////////////////////////////////////
// function: printRm
// purpose: print out basic room information mainly for debugging purposes
// params: pointer to a room whose info I want to see
//////////////////////////////////////////////////////////////////////////////
void printRm(struct Room *rm){
    int i = 0;
    // Print atomic ingo
    printf("%s - %d - %s\n", rm->rmName, rm->numConn, rm->rmType);
    // List connections
    for (i = 0; i < rm->numConn; i++){
        printf("     %d - %s\n",i+1, rm->connections[i]->rmName);
    }
}



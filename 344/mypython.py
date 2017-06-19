#!/usr/bin/python3
'''
' Aimee Sullivan (sullivai)
' CS344-400 Project Py
' March 6, 2017
' Python3 script that creates three text files, places a random
' letter string in each and also displays that on-screen. Then
' displays two random integers and their product
'''
import string, random

# select 10 random lowercase letters and add to a string
# finish the string off with a \n
# http://stackoverflow.com/a/1957289
def getStrng():
	strng = ''
	for x in range(10):
		strng += ''.join(random.choice(string.ascii_lowercase))
	strng += '\n'
	return strng
	
# filenames
fnames = ["tom","dick","harry"]

# generate a string, print it to both screen and file, close file
for name in fnames:
	s = getStrng()
	print(s,end="")   # python3; so as not to add extra \n on screen
	file = open(name+".txt","w+")
	file.write(s) 
	file.close()

# get two random numbers between 1-42 inclusive (randrange is [i,j))
i = random.randrange(1,43)
j = random.randrange(1,43)

#print numbers and their product
print(i)
print(j)
print(i*j)

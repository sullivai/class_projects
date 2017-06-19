TITLE Project 04     (SULLIVAN-Project04.asm)

; Author:  Aimee Sullivan (sullivai@oregonstate.edu)
; Course / Project ID : CS271-400 / Prog04               Due Date: 02/14/2016
; Description: This program takes user interger input and validates that it is within
; a specified range.  It then displays that many composite numbers, 10 to a line.
; Implementation notes: This program is implemented with procedures using global
; variables and without parameter passing.

INCLUDE Irvine32.inc

MAX_NUM = 400			; upper limit
DIVISOR = 10			; control how many terms printed per line

.data

num_terms		DWORD	?			; integer entered by user
nth_composite	DWORD	0			; number to be displayed
mod_value		DWORD	0			; inner loop iterator tests this_num by dividing
this_num		DWORD	3			; outer loop iterator being tested for compositeness
loop_count	DWORD	0			; keeps track of the loop for line break purposes
spacer		BYTE		"   ", 0		; spacer between displayed numbers
prog_title	BYTE		"Composite Numbers brought to you by ", 0
programmer	BYTE		"Aimee Sullivan", 0
intro1		BYTE		"Enter the number of composites to display.", 0
intro2		BYTE		"The max is ", 0
prompt_1		BYTE		"How many composites (1-", 0
prompt_2		BYTE		") would you like? ", 0
output1		BYTE		"Your ", 0
output2		BYTE		" composites, Madam:", 0
bad_num		BYTE		"Sorry, that is outside the acceptable range.", 0
valediction1	BYTE		" likes composites just as much as you do.", 0
valediction2	BYTE		"Have a wonderful day.", 0


.code
main PROC

	call	intro
	call	getUserData
	call	showComposites
	call	farewell

	exit	; exit to operating system
main ENDP


; Procedure to display introductory text
; receives: none
; returns: none
; preconditions: none
; registers changed: edx, eax
intro	PROC

; Display title and intro
	mov	edx, OFFSET prog_title
	call	WriteString

	mov	edx, OFFSET programmer
	call	WriteString
	call	CrLf
	call	CrLf

; Display first line of instructions
	mov	edx, OFFSET intro1
	call	WriteString
	call	CrLf

; Display second line of instructions
	mov	edx, OFFSET intro2
	call	WriteString

	mov	eax, MAX_NUM
	call	WriteDec
	call	CrLf

	ret
intro	ENDP


;Procedure to get desired number of composites 
;receives: none
;returns: num_terms is a global variable
;preconditions:  none
;registers changed: eax, ebx, edx

getUserData	PROC

; prompt user for input and show an error message if the number is out of range
	mov	ebx, 0				; set flag to valid initially 

top:
	cmp	ebx, 0				; if flag is valid go on
	je	go_on

	mov	edx, OFFSET bad_num		; otherwise print error text
	call	WriteString
	call	CrLf

go_on:
	mov	edx, OFFSET prompt_1	; prompt for user input
	call	WriteString

	mov	eax, MAX_NUM
	call	WriteDec

	mov	edx,	OFFSET prompt_2
	call	WriteString

	call	ReadInt				; get user input
	call	validate				; validate input

	cmp	ebx, 0				; if flag is not valid
	jne	top					; then go back and re-prompt

	mov	num_terms, eax			; otherwise, copy eax to num_terms

	ret
getUserData	ENDP


;Procedure to display the composites 
;receives: num_terms, nth_composite are global variables
;returns: none
;preconditions:  num_terms is within allowed range
;registers changed: eax, ebx, edx
showComposites	PROC

; display some friendly text	
	call	CrLf
	mov	edx, OFFSET output1
	call	WriteString

	mov	eax, num_terms
	call	WriteDec

	mov	edx, OFFSET output2
	call	WriteString
	call	CrLf

; loop to make formatted display of composites
	mov	ecx, num_terms			

next_composite:
	call	isComposite

	mov	eax, nth_composite		; display "return value" (nth_composite) from isComposite
	call	WriteDec

	mov	edx, OFFSET spacer
	call WriteString

	inc loop_count				; test against this to display only 10 numbers per line
	mov	eax, loop_count	
	cdq
	mov	ebx, DIVISOR
	div	ebx
	cmp	edx, 0
	jne	sameline				; if loop iteration is not a multiple of 10, don't newline
	call	CrLf
		
sameline:
	loop	next_composite			

	ret
showComposites	ENDP


; Procedure to display farewell text
; receives: none
; returns: none
; preconditions: none
; registers changed: edx
farewell	PROC
	call	CrLf
	call	CrLf
	mov	edx, OFFSET programmer
	call	WriteString
	mov	edx, OFFSET valediction1
	call	WriteString
	call	CrLf

	mov	edx, OFFSET valediction2
	call	WriteString
	call	CrLf

	ret
farewell	ENDP


; Procedure to validate user input (1-MAX_NUM)
; receives: none
; returns: none
; preconditions: input to be tested is in eax
; registers changed: ebx
validate	PROC
	cmp	eax, MAX_NUM			; check eax against upper limit
	ja	invalid				; jump to set flag invalid and return if over
	cmp	eax, 1				; check against lower limit 
	jb	invalid				; jump to set flag invalid and return if under

	mov	ebx, 0				; otherwise set valid flag
	jmp	rtn					; and return

invalid:
	mov	ebx, 1				; set invalid flag

rtn:
	ret
validate	ENDP


; Procedure to figure out composites
; receives: mod_value, this_num are global variables
; returns: nth_composite is a global variable
; preconditions: 
; registers changed: eax, edx
isComposite	PROC

; test for next composite (outer loop)	
next_comp:					
	mov	mod_value, 2				; reset inner loop (factor to test) starting number

; test each number from 2... n-1 to see if it's a factor of the outer loop number (inner loop)
next_factor:
	mov	eax, this_num			
	push	eax					; save value being tested for compositeness before division
	cdq
	div	mod_value
	cmp	edx, 0
	je	skip					; if remainder = 0, this is a factor so the number is composite so exit both loops

	inc	mod_value				; otherwise, prepare to test if next value up is a factor
	pop	eax					; retrieve the original value being tested
	cmp	mod_value, eax			; only check factors to n-1 (because obvs the number will divide itself evenly)
	jb	next_factor			; (inner) loop until n-1

	inc	this_num				; otherwise, if the numbers are equal, increment the number being tested
	jmp	next_comp				; outer loop until you find a composite

skip:
	pop	eax					; retrieve the value we were testing
	mov	nth_composite, eax		; save it to "return" to the calling procedure 
	inc	this_num				; prepare to test the next consecutive value if necessary
	
	ret
isComposite	ENDP

END main

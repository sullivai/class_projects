TITLE Project 06b     (SULLIVAN-Project06b.asm)

; Author:  Aimee Sullivan (sullivai@oregonstate.edu)
; Course / Project ID : CS271-400 / Prog06b               Due Date: 03/13/2016
; Description: This program generates a random n within the range to generate a
; factorial that will fit in a DWORD, and a random r from 1 to n for the user to 
; practice calculating nCr. It gets the user's answer to the presented problem,
; validates that the response is numeric, then calculates the result and presents
; the result along with feedback on whether the user's response was correct or 
; not.  The program keeps presenting problems as long as the user selects 'y' or 
; 'Y' at a continuation prompt.  If the user enters 'n' or 'N', then the program
; terminates with a farewell message.  Any other response from the user prints a
; warning message and the user is prompted to enter a new response.
; Implementation notes: This program is implemented with procedures using 
; parameter passing on stack. Strings are declared and used as global variables.

INCLUDE Irvine32.inc

MIN_N = 3						; Lower limit for n
MAX_N = 12					; Upper limit for n
N_RANGE = MAX_N - MIN_N + 1		; Random number range for n


; *********************************************************
; Macro to write strings, from lecture 26
; receives: address of string to write
; returns: none
; preconditions: none
; registers changed: edx 
; *********************************************************
writeStr	MACRO outputStr
	push	edx
	mov	edx, OFFSET outputStr
	call	WriteString
	pop	edx
ENDM


.data
n			DWORD	0			; n in the formula for nCr (random)
r			DWORD	0			; r in the formula for nCr (random)
answer		DWORD	0			; guess input by user
result		DWORD	0			; result calculated by program
input_str		BYTE		10 DUP(0)		; user input string
prog_title	BYTE		"COMBINATORICS DRILLS", 0
programmer	BYTE		"by Aimee Sullivan", 0
intro_1		BYTE		"Prepare to practice combinatorics. ", 0
intro_2		BYTE		"I will tell you if your answer is right.", 0
problemTxt_1	BYTE		"Problem:  ", 0
problemTxt_2	BYTE		"Elements in the set (n): ", 0
problemTxt_3	BYTE		"Elements to choose (r): ", 0
problem_prompt	BYTE		"How many ways can you choose? ", 0
answer_1		BYTE		"There are ", 0
answer_2		BYTE		" combinations of ", 0
answer_3		BYTE		" items from a set of ", 0
answer_wrong	BYTE		"Try to do better next time.", 0
answer_right	BYTE		"Good job!", 0
again_prompt	BYTE		"Would you like to try again? (y/n): ", 0
farewell		BYTE		"Goodbye.", 0
bad_answer	BYTE		"That is not a valid response. ", 0


.code
main PROC
; seed random number generator
	call	Randomize			

; dispay introduction and instructions	
	call	intro

; generate a problem
more:
	push	OFFSET r
	push	OFFSET n
	call	showProblem

; get user's answer
	push	OFFSET answer
	call	getData

; calculate the problem result	
	push	r
	push	n
	push	OFFSET result
	call	combinations

; display result and feedback for user		
	push	result
	push	answer
	push	r
	push	n
	call showResults

; repeat another problem or quit	
	push	OFFSET input_str
	push	OFFSET answer
	call	do_again

	cmp	answer, 121		; if validated user response is 'y'
	je	more				; then loop back to top and go again

; display farewell message and exit
	writeStr	farewell
	call	CrLf
	call	CrLf
	exit	; exit to operating system
main ENDP


; *********************************************************
; Procedure to display introductory text
; receives: none
; returns: none
; preconditions: none
; registers changed: edx via macro
; *********************************************************
intro	PROC
	push	edx

; Display title and intro
	writeStr	prog_title
	call	CrLf
	writeStr	programmer
	call	CrLf
	call	CrLf

; Display instructions
	writeStr	intro_1
	call		CrLf
	writeStr	intro_2
	call		CrLf

	pop	edx
	ret
intro	ENDP


; *********************************************************
; Procedure to generate a combination problem
; receives: address of r and n on system stack
; returns: none
; preconditions: parameters have been pushed on stack
; registers changed: eax, edx via macro, edi
; *********************************************************
showProblem	PROC
	push	ebp
	mov	ebp, esp

	push	eax					; preserve registers
	push	edx
	push	edi

	call		CrLf
	writeStr	problemTxt_1		
	call		CrLf

	mov	eax, N_RANGE			; get random n in range from min to max
	call	RandomRange
	add	eax, MIN_N

	mov	edi, [ebp+8]			; store n at address of output param
	mov	[edi], eax			

	writeStr	problemTxt_2		; display n 
	call		WriteDec
	call		CrLf

	call	RandomRange			; get random r in range from 1 to n
	inc	eax

	mov	edi, [ebp+12]			; store r at address of output param
	mov	[edi], eax

	writeStr	problemTxt_3		; display r
	call		WriteDec

	pop	edi					; restore registers
	pop	edx
	pop	eax

	pop	ebp
	ret	8
showProblem	ENDP


; *********************************************************
; Procedure to get problem answer from user, based on lecture 23
; receives: address of answer on system stack
; returns: answer on stack
; preconditions: parameter have been pushed by reference
; registers changed: al, ax, eax, bx, ebx, ecx, edx, esi
; *********************************************************
getData	PROC
	push	ebp
	mov	ebp, esp

	push	eax					; preserve registers
	push	ebx
	push	ecx
	push	edx
	push	esi

	call	crLf

prmpt:
	writeStr	problem_prompt		; prompt user for input
	mov	ecx, 10				; limit characters to be read
	mov	edx, [ebp+8]			; store address of answer in edx
	call	ReadString			; read user input as string
	mov	ecx, eax				; get length of user input
	mov	esi, edx				; move user input into esi
	cld						; traverse string forward

	mov	ebx, 0				; x = 0

next_digit:
	lodsb					; for k = 0 to len(str)-1
	cmp	al, 48				; if str[k] < 48 break
	jb	error
	cmp	al, 57				; if str[k] > 57 break
	ja	error

	push	ax					; preserve ax (value of str[k])
	mov	ax, bx				; value of "x" in ax
	mov	bx, 10				
	push	edx					
	mul	bx					; x = x * 10
	pop	edx					
	mov	bx, ax				; new value of "x" in bx
	pop	ax					; restore ax
	sub	al, 48				; str[k] - 48
	add	bx, ax				; x = x + (str[k] - 48)
	loop next_digit			; next k
	mov	[edx], ebx			; save "x" in answer location
	jmp	finish

error:
	writeStr	bad_answer		; print invalid entry warning
	jmp	prmpt				; loop back to prompt for new input

finish:	
	pop	esi					; restore registers
	pop	edx
	pop	ecx
	pop	ebx
	pop	eax

	pop	ebp
	ret	4
getData	ENDP


; *********************************************************
; Procedure to calculate combinations
; receives: value of n and r, address of result on system stack
; returns: result on stack
; preconditions: none
; registers changed: eax, ebx, ecx, edx, edi
; *********************************************************
combinations	PROC
	push	ebp
	mov	ebp, esp

	push	eax				; preserve registers
	push	ebx
	push	ecx
	push	edx
	push	edi

	mov	edi, [ebp+8]		; store output param address

	push	[ebp+12]			; call factorial on n
	call	factorial

	mov	ecx, eax			; store value of n! in ecx
	
	push [ebp+16]			; call factorial on r
	call	factorial

	mov	ebx, eax			; store value of r! in ebx

	push	ebx				; save ebx

	mov	eax, [ebp+12]		; get n-r
	sub	eax, [ebp+16]		 

	push	eax				; call factorial on (n-r)
	call	factorial			; eax = (n-r)!

	pop	ebx				; restore ebx
	mul	ebx				; eax = r!(n-r)!
	mov	ebx, eax			; save this value in ebx

	mov	eax, ecx			; move n! to be dividend
	cdq
	div	ebx				; divide n! by r!(n-r)!

	mov	[edi], eax		; save result in output param address

	pop	edi				; restore registers
	pop	edx
	pop	ecx
	pop	ebx
	pop	eax

	pop	ebp
	ret 12
combinations	ENDP


; *********************************************************
; Procedure to calculate factorials, from Irvine p.305
; receives: value of n on system stack
; returns: result in eax
; preconditions: n is non-negative integer
; registers changed: eax, ebx
; *********************************************************
factorial	PROC
	push	ebp
	mov	ebp, esp

	mov	eax, [ebp+8]		; get n
	cmp	eax, 0			; if n == 0, this is base case
	ja	recurse			
	mov	eax, 1			; and n! = 1
	jmp	base

recurse:					; otherwise it's recursive case
	dec	eax				; get n-1
	push	eax				; call factorial on n-1
	call	factorial			

	mov	ebx, [ebp+8]		; multiply current n by result of 
	mul	ebx				; previous call to factorial (in eax)

base:
	pop	ebp
	ret	4

factorial	ENDP


; *********************************************************
; Procedure to display results of calculation and feedback
; receives: values of n, r, answer, and result on system stack
; returns: none
; preconditions: none
; registers changed: eax, ebx, (edx via macro)
; *********************************************************
showResults	PROC
	push	ebp
	mov	ebp, esp

	push	eax					; preserve registers
	push	ebx
	push	edx

	call		CrLf
	writeStr	answer_1			; "There are"
	mov		eax, [ebp+20]		; result
	call		WriteDec			
	writeStr	answer_2			; "combinations of"
	mov		eax,	[ebp+12]		; r
	call		WriteDec
	writeStr	answer_3			; "items from a set of"
	mov		eax, [ebp+8]		; n
	call		WriteDec
	call		CrLf

	mov		eax, [ebp+20]
	mov		ebx, [ebp+16]
	cmp		eax, ebx			; if result == answer
	jne		wrong			
	writeStr	answer_right		; print a nice message
	jmp		skip

wrong:
	writeStr	answer_wrong		; otherwise print other message

skip:
	call	CrLf					; add some whitespace for readability
	call	CrLf					

	pop	edx					; restore registers
	pop	ebx
	pop	eax

	pop	ebp
	ret	16
showResults	ENDP


; *********************************************************
; Procedure to ask user if they want to go again, and validate response (y/n)
; receives: address for input string, address for validated response on stack
; returns: none
; preconditions: parameters pushed onto system stack
; registers changed: eax, ebx, ecx, edx, esi
; *********************************************************
do_again	PROC
	push	ebp
	mov	ebp, esp

	push	eax					; preserve registers 
	push	ebx
	push	ecx
	push	edx
	push	esi
	
prompt:
	writeStr	again_prompt		; prompt user for response
	mov	ebx, [ebp+8]			; save address for validated answer
	mov	edx, [ebp+12]			; save address for input string
	mov	ecx, 3				; limit the number of characters to read
	call	ReadString			; get user input string into edx

	cmp	eax, 1				; if the input string length is > 1
	ja	invalid				; then input is not valid

	mov	esi, edx				; otherwise examine the first character
	lodsb

	cmp	al, 100				; if it's already lowercase (only concerned 
	ja	go_on				; about 110 'n' and 121 'y') then go on
	add	al, 32				; otherwise add 32 to make it lowercase

go_on:
	cmp	al, 121				; input is valid if char is 'y'
	je	ok
	cmp	al, 110				; input is valid if char is 'n'
	je	ok

invalid:				
	writeStr	bad_answer		; otherwise, print a warning
	jmp	prompt				; loop back to prompt for new input

ok:
	mov	[ebx], al				; save the character in the answer address


	pop	esi					; restore registers
	pop	edx
	pop	ecx
	pop	ebx
	pop	eax
	
	pop	ebp
	ret	8					
do_again	ENDP


END main

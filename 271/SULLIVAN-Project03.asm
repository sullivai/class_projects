TITLE Project 03     (SULLIVAN-Project03.asm)

; Author:  Aimee Sullivan (sullivai@oregonstate.edu)
; Course / Project ID : CS271-400 / Prog03               Due Date: 02/07/2016
; Description: This program takes user input of negative integers within a specified
; range and validates that the input is within range. It keeps accepting valid negative
; input, accumulating the total and keeping track of the number of correct entries,
; until the user enters a non-negative integer. It then displays the count, sum, and 
; average of the valid entries and displays a parting message. 

INCLUDE Irvine32.inc

UPPER_LIMIT = -1
LOWER_LIMIT = -100


.data
user_name		BYTE		50 DUP(0)		; string to be entered by user
int_count		DWORD	0			; total number of valid integers the user entered
int_sum		DWORD	0			; sum of entered integers
int_average	DWORD	?			; rounded average of entered integers


prog_title	BYTE		"Integer Accumulator by ", 0
programmer	BYTE		"Aimee Sullivan", 0
intro		BYTE		"Summing and rounded-averaging negative integers since 2016", 0
prompt_name	BYTE		"What is your name? ", 0
salutation	BYTE		"Hello and welcome, ", 0
instruction_1	BYTE		"Please enter a number from -100 to -1, inclusive.", 0
instruction_2	BYTE		"Enter a non-negative to finish.", 0
prompt_num	BYTE		"Enter a number: ", 0
bad_num		BYTE		"That number is less than the acceptable range.", 0
good_num		BYTE		"That number is OK.", 0
no_neg_msg	BYTE		"Negative numbers are not to be feared!", 0
result_1		BYTE		"You entered ", 0
result_2		BYTE		" valid numbers.", 0
result_sum	BYTE		"The sum of your valid numbers is ", 0
result_avg	BYTE		"The rounded average of your valid numbers is ", 0
valediction1	BYTE		"Thank you for visiting the Integer Accumulator, ", 0
valediction2	BYTE		"Live long and prosper.", 0


.code
main PROC

;;; introduction ;;;
; Display program title and programmer name.
	mov	edx, OFFSET prog_title
	call WriteString
	mov	edx, OFFSET programmer
	call	WriteString
	call	CrLf

	mov	edx, OFFSET intro
	call	WriteString
	call	CrLf
	call CrLf


; Get user's name and greet them 
	mov	edx, OFFSET prompt_name
	call	WriteString

	mov	edx, OFFSET user_name
	mov	ecx, 49
	call	ReadString

	mov	edx, OFFSET salutation
	call WriteString

	mov	edx, OFFSET user_name
	call WriteString
	call CrLf
	call CrLf


;;; userInstructions ;;;
; Instruct user to enter numbers within required range

	mov	edx, OFFSET instruction_1
	call	WriteString
	call	CrLf

	mov	edx, OFFSET instruction_2
	call	WriteString
	call	CrLf
	call	CrLf


;;; getUserData ;;;
; Get and validate user input

validate:
	mov	edx, OFFSET prompt_num	; prompt for input number
	call	WriteString
	call	ReadInt				; get user input

	cmp	eax, UPPER_LIMIT		; if user input is not negative
	jg	process_input			; then jump to the next section

	cmp	eax, LOWER_LIMIT		; if input is between acceptable limits
	jge	input_OK				; then do some stuff before looping to top

	mov	edx, OFFSET bad_num		; else if input is below the minimum
	call	WriteString			; print a warning message before continuing the loop
	call	CrLf
	jmp	continue

input_OK:
	inc	int_count				; increment valid input counter
	add	int_sum, eax			; accumulate input values

continue:
	jmp	validate				; loop back to top

	
;;; processInput ;;;
; Process user input (determine if any valid entries, calculate average if so)

process_input:
	cmp	int_count, 0				; if no valid values were entered
	je	no_valid_input				; then jump to the end

	mov	eax, int_sum				; otherwise calculate average of input values
	cdq
	idiv	int_count
	mov	int_average, eax
	

;;; displayResults ;;;
; Display summary of user input, to include count of valid entries, sum, and average
	call	CrLf
	mov	edx, OFFSET result_1
	call	WriteString

	mov	eax, int_count				; number of valid integers entered
	call	WriteDec

	mov	edx, OFFSET result_2
	call	WriteString
	
	call CrLf
	mov	edx, OFFSET result_sum		; display the sum of valid integers
	call	WriteString

	mov	eax, int_sum			
	call	WriteInt

	call	CrLf						; display the average of valid integers
	mov	edx, OFFSET result_avg
	call	WriteString

	mov	eax, int_average			
	call	WriteInt

	jmp	farewell					; jump to end


;;; farewell ;;;
; Display parting message including user's name, and terminate

no_valid_input:					; print special message if there was no valid input
	call	CrLf
	mov	edx, OFFSET no_neg_msg
	call	WriteString


farewell:							; print parting section
	call	CrLf
	call	CrLf
	mov	edx, OFFSET valediction1
	call	WriteString

	mov	edx, OFFSET user_name
	call	WriteString
	call	CrLf

	mov	edx, OFFSET valediction2
	call	WriteString
	call	CrLf


	exit	; exit to operating system
main ENDP

; (insert additional procedures here)

END main

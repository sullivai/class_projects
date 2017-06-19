TITLE Project 05     (SULLIVAN-Project05.asm)

; Author:  Aimee Sullivan (sullivai@oregonstate.edu)
; Course / Project ID : CS271-400 / Prog05               Due Date: 02/28/2016
; Description: This program requests an integer from the user and then generates 
; that many random numbers within a specific range, which are stored in an array.
; The unsorted array contents are displayed, then the array is sorted, the median 
; value is displayed, and finally the sorted array is displayed. The user input is
; validated to ensure it falls within specified min/max limits.
; Implementation notes: This program is implemented with procedures using 
; parameter passing. Strings are declared and used as global variables.

INCLUDE Irvine32.inc

MIN = 10					; minimum array size
MAX = 200					; maximum array size
LO = 100					; lower range limit for random numbers
HI = 999					; upper range limit for random numbers
RANGE = HI - LO + 1			; range of random numbers
DIVISOR = 10				; used for numeric display


.data
num_terms		DWORD	?			; integer entered by user
list_array	DWORD	MAX DUP (0)	; initialize array to hold random numbers
spacer		BYTE	"   ", 0
prog_title	BYTE	"Sorting Random Integers       by       ", 0
programmer	BYTE	"Aimee Sullivan", 0
intro_1		BYTE	"This program generates 'random' numbers in the range ", 0
intro_2		BYTE	"It displays the numbers in a list as-generated, then sorts the list, ", 0
intro_3		BYTE	"finds and displays the median value, and displays the sorted list.", 0
intro_4		BYTE	"We truly live in exciting times.", 0
prompt_1		BYTE	"Enter a number from ", 0
to_text		BYTE	" to ", 0
bad_num		BYTE	"That is not correct.", 0
median_txt	BYTE	"The median is ", 0
valediction	BYTE	"Please watch your step on the way out.", 0
title_1		BYTE	"The unsorted list", 0
title_2		BYTE	"The sorted list", 0


.code
main PROC

	call	Randomize			; seed random number gen

; Introduction
	call	intro

; Get user input
	push	OFFSET num_terms	; output parameter reference
	call	getData

; Fill array
	push	OFFSET list_array	; array reference input param
	push	num_terms			; array count input param
	call	fillList

; Display array
	push	OFFSET title_1		; display title input param
	push	OFFSET list_array	; array reference
	push	num_terms			; array count
	call	dispList

; Sort array
	push	OFFSET list_array	; array reference
	push num_terms			; array count
	call	sortList

; Display median
	push	OFFSET list_array	; array reference
	push	num_terms			; array count
	call	getMedian

; Display array again
	push	OFFSET title_2		; display title
	push	OFFSET list_array	; array reference
	push	num_terms			; array count
	call	dispList

; Say goodbye
	call	farewell

	exit	; exit to operating system
main ENDP


; *********************************************************
; Procedure to display introductory text
; receives: none
; returns: none
; preconditions: none
; registers changed: edx, eax
; *********************************************************
intro	PROC

; Display title and intro
	mov	edx, OFFSET prog_title
	call	WriteString

	mov	edx, OFFSET programmer
	call	WriteString
	call	CrLf
	call	CrLf

; Display first line of instructions
	mov	edx, OFFSET intro_1
	call	WriteString

	mov	eax, LO
	call	WriteDec

	mov	edx, OFFSET to_text
	call	WriteString

	mov	eax, HI
	call	WriteDec
	call	CrLf

; Display second line of instructions
	mov	edx, OFFSET intro_2
	call	WriteString
	call	CrLf

; Display third line of instructions
	mov	edx, OFFSET intro_3
	call	WriteString
	call	CrLf

	mov	edx, OFFSET intro_4
	call	WriteString
	call	CrLf
	call	CrLf

	ret
intro	ENDP


; *********************************************************
; Procedure to validate user input within range
; receives: none
; returns: none
; preconditions: input to be tested is in eax
; registers changed: ecx
; *********************************************************
validate	PROC
	cmp	eax, MAX				; check eax against upper limit
	ja	invalid				; jump to set flag invalid and return if over
	cmp	eax, MIN				; check against lower limit 
	jb	invalid				; jump to set flag invalid and return if under

	mov	ecx, 0				; otherwise set valid flag
	jmp	rtn					; and return

invalid:
	mov	ecx, 1				; set invalid flag

rtn:
	ret
validate	ENDP


; *********************************************************
;Procedure to get desired number of random numbers 
;receives: none
;returns: user input request on system stack
;preconditions:  offset of output parameter passed on stack
; registers changed: eax, ebx, ecx, edx
; *********************************************************
getData		PROC
	push	ebp
	mov	ebp, esp

	mov	ebx, [ebp+8]			; address of output param

; prompt user for input and show an error message if the number is out of range
	mov	ecx, 0				; set flag to valid initially 

top:
	cmp	ecx, 0				; if flag is valid go on
	je	go_on

	mov	edx, OFFSET bad_num		; otherwise print error text
	call	WriteString
	call	CrLf

go_on:
	mov	edx, OFFSET prompt_1	; prompt for user input
	call	WriteString

	mov	eax, MIN
	call	WriteDec

	mov	edx,	OFFSET to_text
	call	WriteString

	mov	eax, MAX
	call	WriteDec

	mov	eax, ':'
	call	WriteChar

	mov	edx, OFFSET spacer
	call	WriteString
	
	call	ReadInt

	call	validate				; validate input
	cmp	ecx, 0				; if flag is not valid
	jne	top					; then go back and re-prompt

	mov	[ebx], eax			; store value in location referenced by ebx
	pop	ebp

	ret	4
getData		ENDP


; *********************************************************
; Procedure to display an array, adapted from lecture 20 slides
; receives: address of array, value of array count, title of display type on system stack
; returns: none
; preconditions: parameters have been pushed onto system stack
; registers changed: eax, ebx, ecx, edx, esi
; *********************************************************
dispList	PROC
	push	ebp
	mov	ebp, esp

	mov	edx, [ebp + 16]	; "title" of display type
	mov	esi, [ebp + 12]	; address of array in esi
	mov	ecx, [ebp + 8]		; count in ecx
	mov	ebx, 0			; set loop counter for line breaks

	call	CrLf
	call	WriteString		; state whether this is sorted or unsorted
	call	CrLf

dispMore:
	mov	eax, [esi]		; display the current element in the array
	call	WriteDec

	mov	edx, OFFSET spacer	; print a spacer
	call	WriteString

	inc	ebx				; increment loop counter
	mov	eax, ebx			; prepare to divide loop counter for determining newlines
	cdq
	push	ebx				; save loop counter from ebx
	mov	ebx, DIVISOR		
	div	ebx
	pop	ebx				; restore loop counter to ebx
	cmp	edx, 0
	jne	sameline			; don't newline if this isn't a multiple of 10
	call	CrLf				; otherwise, do

sameline:	
	add	esi, 4			; move to next element in array
	loop dispMore

endDMore:
	call	CrLf
	pop	ebp
	ret	12

dispList	ENDP


; *********************************************************
; Procedure to fill an array, adapted from lecture 20 slides
; receives: address of array, value of array count on system stack
; returns: none
; preconditions: parameters have been pushed onto system stack
; registers changed: eax, ecx, edi
; *********************************************************
fillList	PROC
	push	ebp
	mov	ebp, esp

	mov	edi, [ebp + 12]	; address of array in edi
	mov	ecx, [ebp + 8]		; count in ecx

fillMore:
	mov	eax, RANGE		; get randum number range from HI/LO limits 
	call	RandomRange		; generate a random number in range
	add	eax, LO
	mov	[edi], eax		; store that number at the location edi is pointing to
	add	edi, 4			; increment edi the size of a DWORD
	loop	fillMore			

endFMore:
	pop	ebp
	ret	8

fillList	ENDP


; *********************************************************
; Procedure to calculate and display array median
; receives: address of array, value of array count on system stack
; returns: none
; preconditions: parameters have been pushed onto system stack
; registers changed: eax, ebx, edx, esi
; *********************************************************
getMedian	PROC
	push	ebp
	mov	ebp, esp

	mov	esi, [ebp + 12]	; address of array in esi
	mov	eax, [ebp + 8]		; count in eax

	mov	edx, OFFSET median_txt
	call	CrLf
	call	WriteString		; print a message and then calculate the median

	inc	eax				; To find the median, first add one to count
	mov	ebx, 2			; then divide by two
	cdq
	div	ebx				
	cmp	edx, 0
	je	odd_count			; if count was odd, the quotient gives the middle element

	push	ebx				; otherwise if count was even....
	dec	eax				; get the lower of the two middle values
	mov	ebx, 4			; multiplier for size of DWORD
	mul	ebx
	add	esi, eax			
	mov	edx, [esi]
	add	esi, ebx			; get the higher of the two middle values
	mov	eax, [esi]
	add	eax, edx			; add the two middle values together
	pop	ebx				; divide them by two to get the median
	cdq
	div	ebx
	jmp	finish

odd_count:
	dec	eax				; compensate for 0-index array
	mov	ebx, 4			; multiplier for size of DWORD
	mul	ebx
	add	esi, eax			; go to the middle element in the array
	mov	eax, [esi]		

finish:
	call	WriteDec			; print the median
	call	CrLf

	pop	ebp				; forgetting this will crash the program
	ret	8
getMedian	ENDP


; *********************************************************
; Procedure to sort integer array, adapted from Irvine textbook p.375
; receives: address of array, value of array count on system stack
; returns: none
; preconditions: parameters have been pushed onto system stack
; registers changed: eax, ebx, ecx, esi, edi
; *********************************************************
sortList	PROC
	push	ebp
	mov	ebp, esp

	mov	ecx, [ebp + 8]		; initialize counter to array count k
	dec	ecx				; counter will loop through k-1

outer:
	push ecx				; preserve outer loop counter
	mov	esi, [ebp + 12]	; start address of array in esi

inner:
	mov	edi, esi			; preserve start of array in edi [i]
	add	esi, 4			; turn esi to next element in array [j]
	mov	eax, [esi]		; store value at esi into eax to compare 
	mov	ebx,	[edi]		; store value at edi into ebx to compare
	cmp	eax, ebx			; if element[j] is greater than element[i]
	jna	go_on
	
	push	esi				; then swap these two elements
	push	edi
	call	swap

go_on:					; otherwise loop j
	loop inner

	pop	ecx				; restore outer counter
	loop	outer			; loop k

	pop	ebp
	ret 8
sortList	ENDP


; *********************************************************
; Procedure to swap values, adapted from Irvine textbook p.320-321
; receives: addresses of two array elements to be swapped on system stack
; returns: none
; preconditions: parameter offsets have been pushed onto system stack
; registers changed: eax, esi, edi
; *********************************************************
swap	PROC
	push	ebp
	mov	ebp, esp

	mov	esi, [ebp + 12]		; get first parameter
	mov	edi, [ebp + 8]			; get second parameter
	mov	eax, [esi]			; move first param value into eax
	xchg	eax, [edi]			; exchange eax with item referenced by edi
	mov	[esi], eax			; move eax into location referenced by esi

	pop	ebp
	ret	8
swap	ENDP


; *********************************************************
; Procedure to display valedictory text
; receives: none
; returns: none
; preconditions: none
; registers changed: edx
; *********************************************************
farewell	PROC

; Display outro
	mov	edx, OFFSET valediction
	call	CrLf
	call	WriteString
	call	CrLf

	ret
farewell	ENDP



END main

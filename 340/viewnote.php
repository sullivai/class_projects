<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
	// Turn on error reporting
	ini_set('display_errors','On');
	// connect to the database
	$mysqli = new mysqli("XXXXXXXXXXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX");
	if($mysqli->connect_errno){
		echo "Connection error " . $mysqli->connect_errno." ".$mysqli->connect_error;
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Note</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Note</h1>
			<form method="post" action="addnote.php">
				<fieldset>
					<legend>Add Note</legend>
					<p><label>Apprx Frequency Integer:</label><input type="number" name="fq" /></p>
					<p><label>Note name:</label><input type-"text" name="name" />
					<input type="submit" name="addNote" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delnote.php">
				<fieldset>
					<legend>Select Note To Remove</legend>
					<p><select name="fq">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT fq, name FROM note"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($fq, $name))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='". $fq ."'>" . $fq ." - " . $name . "</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" value="Delete" /></p>
				</fieldset>
			</form>
		</div>

<!-- DISPLAY ALL -->	
		<div>
			<p><a href="index.html">Return to index</a></p>
			<table>
				<caption>All Notes</caption>
				<tr>
					<th>Apprx freq (as integer)</th>
					<th>Name</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT fq, name FROM note"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($fq, $name))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $fq . "\n</td>\n<td>" . $name . "\n</td></tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>
		</div>
	</body>
</html>



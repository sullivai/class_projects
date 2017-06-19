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
		<title>Role</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Role</h1>
			<form method="post" action="addrole.php">
				<fieldset>
					<legend>Add Role</legend>
					<p><label>Description:</label><input type="text" name="desc" />
					<input type="submit" name="addRole" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delrole.php">
				<fieldset>
					<legend>Select Role To Remove</legend>
					<p><select name="role">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, description FROM role"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($id, $desc))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$id."'>".$desc . "</option>";
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
				<caption>All Roles</caption>
				<tr>
					<th>Description</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT description FROM role"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($desc))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . htmlspecialchars($desc, ENT_QUOTES) . "\n</td></tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>
		</div>
	</body>
</html>



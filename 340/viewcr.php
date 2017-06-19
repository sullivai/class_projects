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
		<title>Character Role</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Character Role</h1>
			<form method="post" action="addcr.php">
				<fieldset>
					<legend>Add New Character Role</legend>
					<p><label>Name:</label><select name="name">					
<?php
	// populate character dropdown
	if (!($stmt = $mysqli->prepare("SELECT c.id, c.name, o.title FROM chracter c INNER JOIN operetta o ON c.oid = o.id ORDER BY o.premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid, $name, $title))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$cid."'>(". $title . ") " . $name."</option>";
	}

	$stmt->close();
?>
					</select></p>
					<p><label>Role:</label><select name="role">
<?php
	// populate role dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, description FROM role"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($rid, $desc))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$rid."'>".$desc."</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" name="addCR" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delcr.php">
				<fieldset>
					<legend>Select Character Role To Remove</legend>
					<p><select name="cr">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT c.id, r.id, o.title, c.name, r.description FROM charrole cr INNER JOIN chracter c ON cr.cid = c.id INNER JOIN role r ON cr.rid = r.id INNER JOIN operetta o ON c.oid = o.id ORDER BY o.premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid, $rid, $title, $name, $desc))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='cid=".$cid."&rid=".$rid."'>(".$title. ") " . $name . " - " . $desc . "</option>";
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
				<caption>All Character Roles</caption>
				<tr>
					<th>Operetta</th>
					<th>Name</th>
					<th>Role</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT o.title, c.name, r.description FROM charrole cr INNER JOIN chracter c ON cr.cid = c.id INNER JOIN role r ON cr.rid = r.id INNER JOIN operetta o ON c.oid = o.id ORDER BY o.premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($title, $name, $role))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $title . "\n</td>\n<td>" . $name . "\n</td>\n<td>" . htmlspecialchars($role, ENT_QUOTES) . "\n</td>\n</tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>
		</div>
	</body>
</html>
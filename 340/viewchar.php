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
		<title>Character</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Information by Character</h1>	
			<form method="post" action="addchar.php">
				<fieldset>
					<legend>Add New Character</legend>
					<p><label>Name:</label><input type="text" name="CharName" /></p>
					<p><label>Description:</label><input type="text" name="desc" /></p>
					<p><label>Sex:</label><input type="text" name="sex" /></p>
					<p><label>Age:</label><input type="text" name="age" /></p>
					<p><label>Nationality:</label><input type="text" name="nat" /></p>
					<p><label>Trope:</label><input type="text" name="trope" /></p>
					<p><label>Voice:</label><input type="text" name="voice" /></p>
					<p><label>Operetta:</label><select name="op">
<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, title FROM operetta"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($oid, $title))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$oid."'>".$title."</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" name="addChar" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delchar.php">
				<fieldset>
					<legend>Select Character To Remove</legend>
					<p><select name="char">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT c.id, o.title, c.name FROM chracter c INNER JOIN operetta o ON c.oid = o.id"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid, $title, $name))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$cid."'>(".$title. ") " . $name . "</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" value="Delete" /></p>
				</fieldset>
			</form>
		</div>

<!-- FILTER -->
		<div>
			<form method="post" action="searchchar.php">
				<fieldset>
				<legend>Find by Character</legend>
					<p><select name="cid">
<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, name, description FROM chracter ORDER BY name"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid, $name, $desc))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$cid."'>".$name. ($desc ? ", " . htmlspecialchars($desc, ENT_QUOTES) : "") .  "</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" name="filter" value="Songs" /><br></p>
					<p><input type="submit" name="filter" value="Entanglements" /><br></p>
				</fieldset>
<!-- SEARCH -->
				<fieldset>
				<legend>Search for Character by Voice Type</legend>
					<p><label>Voice:</label><input type"text" name="voice" /> (e.g. Soprano)</p>
					<p><label>Exact:</label><input type="checkbox" name="exact" />
					<input type="submit" name="filter" value="Search" /></p>
				</fieldset>

			</form>
		</div>

<!-- DISPLAY ALL CHARACTERS -->
		<div>
			<p><a href="index.html">Return to index</a></p>
			<table>
				<caption>All Characters</caption>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Sex</th>
					<th>Age</th>
					<th>Nationality</th>
					<th>Trope</th>
					<th>Voice</th>
					<th>Title</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT name, description, sex, age, nationality, trope, voice, title FROM chracter c INNER JOIN operetta o ON c.oid = o.id"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($name, $description, $sex, $age, $nat, $trope, $voice, $op))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $name . "\n</td>\n<td>" . htmlspecialchars($description, ENT_QUOTES) . "\n</td>\n<td>" . $sex ."\n</td>\n"
		. "<td>" . $age . "\n</td>\n<td>" . $nat . "\n</td>\n<td>" . $trope ."\n</td>\n"
		. "<td>" . $voice . "\n</td>\n<td>" . $op . "\n</td>\n</tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>	
		</div>
	</body>
</html>
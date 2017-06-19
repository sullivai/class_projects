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
		<title>Song</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Information by Song</h1>
			<form method="post" action="addsong.php">
				<fieldset>
					<legend>Add Song</legend>
					<p><label>Title:</label><input type="text" name="sTitle" /></p>
					<p><label>Type:</label><input type="text" name="sType" /></p>
					<p><label>Length:</label><input type="number" name="len" /></p>
					<p><label>Number:</label><input type="number" name="num" /></p>
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
					<input type="submit" name="addSong" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delsong.php">
				<fieldset>
					<legend>Select Song To Remove</legend>
					<p><select name="aSong">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT s.id, o.title, s.title FROM song s INNER JOIN operetta o ON s.oid = o.id"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($sid, $otitle, $stitle))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$sid."'>(".$otitle. ") " . $stitle . "</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" value="Delete" /></p>
				</fieldset>
			</form>
		</div>

<!-- DISPLAY ALL CHARACTERS -->
		<div>
			<p><a href="index.html">Return to index</a></p>
			<table>
				<caption>All Songs</caption>
				<tr>
					<th>Operetta</th>
					<th>Number</th>
					<th>Title</th>
					<th>Type</th>
					<th>Length in bars</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT o.title, s.number, s.title, s.type, s.length FROM song s INNER JOIN operetta o ON s.oid = o.id"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($optitle, $num, $stitle, $type, $len))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $optitle . "\n</td>\n<td>" . $num . "\n</td>\n<td>" . htmlspecialchars($stitle, ENT_QUOTES) ."\n</td>\n"
		. "<td>" . $type . "\n</td>\n<td>" . $len . "\n</td>\n</tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>	
		</div>
	</body>
</html>



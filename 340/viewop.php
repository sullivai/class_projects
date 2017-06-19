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
		<title>Operetta</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Information by Operetta</h1>	
			<form method="post" action="addop.php">
				<fieldset>
					<legend>Add New Operetta</legend>
					<p><label>Title:</label><input type="text" name="opName" /></p>
					<p><label>Subtitle:</label><input type="text" name="subT" /></p>
					<p><label>Premiere:</label><input type="number" name="prem" /></p>
					<p><label>First Run:</label><input type="number" name="run" /></p>
					<p><label>Acts:</label><input type="number" name="acts" /></p>
					<p><label>Theme:</label><input type="textarea" name="theme" /></p>
					<p><label>Period:</label><input type="text" name="period" />
					<input type="submit" name="addOp" /></p>
				</fieldset>		
			</form>
		</div>

<!-- EDIT ENTRY -->
		<div>
			<form method="post" action="selectop.php">
				<fieldset>
					<legend>Select Operetta To Edit</legend>
					<p><select name="op">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, title, premiere FROM operetta ORDER BY premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($oid, $title, $year))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$oid."'>".$title. " (" . $year . ") </option>";
	}

	$stmt->close();
?>

					</select>
					<input type="submit" value="Edit..." /></p>
				</fieldset>
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delop.php">
				<fieldset>
					<legend>Select Operetta To Remove</legend>
					<p><select name="op">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, title, premiere FROM operetta ORDER BY premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($oid, $title, $year))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$oid."'>".$title. " (" . $year . ") </option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" value="Delete" /></p>
				</fieldset>
			</form>
		</div>

<!-- SEARCH/FILTER -->
		<div>
			<form method="post" action="searchop.php">
				<fieldset>
				<legend>Find by Operetta</legend>
					<p><select name="op">
<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, title, premiere FROM operetta ORDER BY premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($oid, $title, $year))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$oid."'>".$title. " (" . $year . ") </option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" name="filter" value="Characters" /></p>
					<p><input type="submit" name="filter" value="Songs" /><br></p>
					<p><input type="submit" name="filter" value="Notes" /><br></p>
					<p><input type="submit" name="filter" value="Settings" /><br></p>
					<p><input type="submit" name="filter" value="Entanglements" /><br></p>
				</fieldset>
			</form>
		</div>

<!-- DISPLAY ALL OPERETTAS -->
		<div>
			<p><a href="index.html">Return to index</a></p>
			<table>
				<caption>All Operettas</caption>
				<tr>
					<th>Title</th>
					<th>Subtitle</th>
					<th>Premiere</th>
					<th>First Run</th>
					<th>Acts</th>
					<th>Theme</th>
					<th>Period</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT title, subtitle, premiere, first_run, acts, theme, period FROM operetta ORDER BY premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($title, $sub, $prem, $firstrun, $acts, $theme, $pd))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $title . "\n</td>\n<td>" . htmlspecialchars($sub, ENT_QUOTES) . "\n</td>\n<td>" . $prem ."\n</td>\n"
		. "<td>" . $firstrun . "\n</td>\n<td>" . $acts . "\n</td>\n<td>" . htmlspecialchars($theme, ENT_QUOTES) ."\n</td>\n"	. "<td>" . $pd . "\n</td>\n</tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>			
		</div>
	</body>
</html>
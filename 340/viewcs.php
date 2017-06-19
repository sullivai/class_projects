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
		<title>Character Song</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Character-Song Part Information</h1>		
			<form method="post" action="selectcs.php">
				<fieldset>
					<legend>Add New Character Song Part</legend>
					<p><label>Name:</label><select name="chr">					
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
					</select>
					<input type="submit" value="Select..."/></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delcs.php">
				<fieldset>
					<legend>Select Character Song Part To Remove</legend>
					<p><select name="cs">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT c.id, s.id, o.title, c.name, s.title FROM charsong cs INNER JOIN chracter c ON cs.cid = c.id INNER JOIN song s ON cs.sid = s.id INNER JOIN operetta o ON c.oid = o.id ORDER BY o.premiere, s.number, c.name"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid, $sid, $otitle, $name, $stitle))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='cid=".$cid."&sid=".$sid."'>(".$otitle. ") " . $name . " - " . $stitle . "</option>";
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
				<tr>
					<caption>All Character Song Parts</caption>
					<th>Operetta</th>
					<th>Name</th>
					<th>Song Title</th>
					<th>Length of Part</th>
					<th>High Note</th>
					<th>Low Note</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT o.title, c.name, s.title, cs.bars, hi.name, lo.name FROM charsong cs INNER JOIN chracter c ON cs.cid = c.id INNER JOIN song s ON cs.sid = s.id INNER JOIN operetta o ON c.oid = o.id INNER JOIN note hi ON cs.hi = hi.fq INNER JOIN note lo ON cs.lo = lo.fq ORDER BY o.premiere, s.number, c.name"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($title, $name, $song, $len, $hi, $lo))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $title . "\n</td>\n<td>" . $name . "\n</td>\n<td>" . htmlspecialchars($song, ENT_QUOTES) . "\n</td>\n<td>" . $len . "\n</td>\n<td>" . $hi . "\n</td>\n<td>" . $lo . "\n</td>\n</tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>
		</div>
	</body>
</html>
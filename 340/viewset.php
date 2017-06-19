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
		<title>Setting</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>

<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Setting</h1>
			<form method="post" action="addsetting.php">
				<fieldset>
					<legend>Add Setting</legend>
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
					</select></p>
					<p><label>Act:</label><input type="text" name="act" /></p>
					<p><label>Location:</label><input type="text" name="loc" /></p>
					<p><label>Detail:</label><input type="text" name="set" />					
					<input type="submit" name="addSetting" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delsetting.php">
				<fieldset>
					<legend>Select Setting To Remove</legend>
					<p><select name="set">

<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT s.oid, s.act, o.title, s.detail FROM setting s INNER JOIN operetta o ON s.oid = o.id"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($oid, $act, $title, $detail))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='oid=".$oid."&act=".$act."'>(".$title. " " . $act . ") " . $detail . "</option>";
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
				<caption>All Settings</caption>
				<tr>
					<th>Operetta</th>
					<th>Act</th>
					<th>Location</th>
					<th>Detail</th>
				</tr>

<?php 
	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare("SELECT o.title, s.act, s.location, s.detail FROM setting s INNER JOIN operetta o ON s.oid = o.id"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($optitle, $act, $loc, $set))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $optitle . "\n</td>\n<td>" . $act . "\n</td>\n<td>" . htmlspecialchars($loc, ENT_QUOTES) ."\n</td>\n"
		. "<td>" . htmlspecialchars($set, ENT_QUOTES) . "\n</td></tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>
		</div>
	</body>
</html>



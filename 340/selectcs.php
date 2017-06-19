<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
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
		<title>Add Character Song</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div>
			<form method="post" action="addcs.php">
				<fieldset>
					<legend>Add Character Song Information</legend>

<?php 
	// get character selection from previous page
	if (!($stmt = $mysqli->prepare("SELECT id, name FROM chracter WHERE id = ?"))) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	} 

	if (!($stmt->bind_param("i",$_POST['chr']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($id, $name))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
		echo " <input type='hidden' name='cid' value='". $id ."' />\n"
				. "<p>Character: <input type='text' name='name' value='". $name ."' readonly='readonly' /></p>\n";
	}

	$stmt->close();
?>

					<p>Song: <select name="song">
<?php
	// populate song dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, title FROM song WHERE oid = (SELECT oid FROM chracter WHERE id = ?)"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_param("i",$_POST['chr']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($sid, $stitle))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$sid."'>".$stitle."</option>";
	}

	$stmt->close();
?>
					</select></p>
					<p>Length of part in bars: <input type="number" name="len" /></p>
					<p>Highest Note sung: <select name="hi">					
<?php
	// populate character dropdown
	if (!($stmt = $mysqli->prepare("SELECT fq, name FROM note"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($hid, $hiname))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$hid."'>". $hiname . "</option>";
	}

	$stmt->close();
?>
					</select></p>
					<p>Lowest Note sung: <select name="lo">					
<?php
	// populate character dropdown
	if (!($stmt = $mysqli->prepare("SELECT fq, name FROM note"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($lid, $loname))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$lid."'>". $loname . "</option>";
	}

	$stmt->close();
?>
					</select>				
					<input type="submit" name="addCS" /></p>
				</fieldset>		
			</form>
		</div>
	</body>
</html>
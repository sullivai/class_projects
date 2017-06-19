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
		<title>Add Entanglement</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div>
			<form method="post" action="addent.php">
				<fieldset>
					<legend>Add Entanglement</legend>

<?php 
	// get opera selection from previous page
	if (!($stmt = $mysqli->prepare("SELECT title FROM operetta WHERE id = ?"))) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	} 

	if (!($stmt->bind_param("i",$_POST['op']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($title))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	$stmt->fetch();
	echo "<p><input type='text' name='op' value='". $title ."' readonly='readonly' /></p>\n";
	$stmt->close();
?>

					<p>Character Role 1: <select name="cr1">
<?php
	// restrict character-roles to one operetta
	$longQuery = "SELECT cr.cid, cr.rid, c.name, r.description FROM charrole cr "
		. "INNER JOIN chracter c on cr.cid = c.id "
		. "INNER JOIN role r ON cr.rid = r.id "
		. "INNER JOIN operetta o ON c.oid = o.id "
		. "WHERE o.id = ?";

	// populate dropdown
	if (!($stmt = $mysqli->prepare($longQuery))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_param("i",$_POST['op']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid1, $rid1, $name1, $desc1))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='cid1=".$cid1."&rid1=".$rid1."'>" . $name1 . " - " . $desc1 ."</option>";
	}

	$stmt->close();
?>
					</select></p>

					<p>Character Role 2: <select name="cr2">
<?php
	// restrict character-roles to one operetta
	$longQuery = "SELECT cr.cid, cr.rid, c.name, r.description FROM charrole cr "
		. "INNER JOIN chracter c on cr.cid = c.id "
		. "INNER JOIN role r ON cr.rid = r.id "
		. "INNER JOIN operetta o ON c.oid = o.id "
		. "WHERE o.id = ?";

	// populate dropdown
	if (!($stmt = $mysqli->prepare($longQuery))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_param("i",$_POST['op']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($cid2, $rid2, $name2, $desc2))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='cid2=".$cid2."&rid2=".$rid2."'>" . $name2 . " - " . $desc2 ."</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" /></p>
				</fieldset>		
			</form>
		</div>
	</body>
</html>
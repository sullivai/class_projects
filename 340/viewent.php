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
		<title>Entanglement</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
	
<!-- ADD ENTRY -->
		<div>
			<p><a href="index.html">Return to index</a></p>	
			<h1>Entanglement</h1>
			<form method="post" action="selectent.php">
				<fieldset>
					<legend>Add New Entanglement</legend>
					<p><label>Operetta:</label><select name="op">					
<?php
	// populate dropdown
	if (!($stmt = $mysqli->prepare("SELECT id, title FROM operetta ORDER BY premiere"))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($oid, $title))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='".$oid."'>". $title . "</option>";
	}

	$stmt->close();
?>
					</select>
					<input type="submit" /></p>
				</fieldset>		
			</form>
		</div>

<!-- DELETE ENTRY -->
		<div>
			<form method="post" action="delent.php">
				<fieldset>
					<legend>Select Entanglement To Remove</legend>
					<p><select name="ent">

<?php
	$superLongQuery = "SELECT o.title, e.cid1, c1.name, e.rid1, r1.description, e.cid2, c2.name, e.rid2, r2.description "
		. "FROM entanglement e "
		. "INNER JOIN charrole cr1 ON e.cid1 = cr1.cid AND e.rid1 = cr1.rid "
		. "INNER JOIN charrole cr2 ON e.cid2 = cr2.cid AND e.rid2 = cr2.rid "
		. "INNER JOIN chracter c1 ON cr1.cid = c1.id "
		. "INNER JOIN chracter c2 ON cr2.cid = c2.id "
		. "INNER JOIN role r1 ON cr1.rid = r1.id "
		. "INNER JOIN role r2 ON cr2.rid = r2.id "
		. "INNER JOIN operetta o ON c1.oid = o.id ORDER BY o.premiere, c1.name";

	// populate dropdown
	if (!($stmt = $mysqli->prepare($superLongQuery))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($title, $cid1, $name1, $rid1, $desc1, $cid2, $name2, $rid2, $desc2))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<option value='cid1=".$cid1."&rid1=".$rid1."&cid2=".$cid2."&rid2=".$rid2."'>(".$title. ") " . $name1 . " - " . $desc1 . " / " . $name2 . " - " . $desc2 . "</option>";
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
					<caption>All Entanglements</caption>
					<th>Operetta</th>
					<th>Name</th>
					<th>Role</th>
					<th>Name</th>
					<th>Role</th>					
				</tr>

<?php 
	$superLongQuery = "SELECT o.title, c1.name, r1.description, c2.name, r2.description "
		. "FROM entanglement e "
		. "INNER JOIN charrole cr1 ON e.cid1 = cr1.cid AND e.rid1 = cr1.rid "
		. "INNER JOIN charrole cr2 ON e.cid2 = cr2.cid AND e.rid2 = cr2.rid "
		. "INNER JOIN chracter c1 ON cr1.cid = c1.id "
		. "INNER JOIN chracter c2 ON cr2.cid = c2.id "
		. "INNER JOIN role r1 ON cr1.rid = r1.id "
		. "INNER JOIN role r2 ON cr2.rid = r2.id "
		. "INNER JOIN operetta o ON c1.oid = o.id ORDER BY o.premiere, c1.name";

	// Get values from db and display in table
	if (!($stmt = $mysqli->prepare($superLongQuery))){
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($title, $name1, $role1, $name2, $role2))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
	echo "<tr>\n<td>" . $title . "\n</td>\n<td>" . $name1 . "\n</td>\n<td>" . htmlspecialchars($role1, ENT_QUOTES) 
		. "\n</td>\n<td>" . $name2 . "\n</td>\n<td>" . htmlspecialchars($role2, ENT_QUOTES) . "\n</td>\n</tr>";
	}

	$stmt->close();
?>

			</table>
			<p><a href="index.html">Return to index</a></p>
		</div>
	</body>
</html>
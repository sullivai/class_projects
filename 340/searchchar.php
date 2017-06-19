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
		<title>Character Filter Results</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div>
			<p><a href="viewchar.php">Return to characters</a></p>
			<table>
				<tr>

<?php
	// search button clicked
	if ($_POST['filter']=='Search') {
		// modify search term for exact or fuzzy search
		if(isset($_POST['exact'])) {
			$v = $_POST['voice'];
		} else {
			$v = '%' . $_POST['voice'] . '%';
		}

		// search query
		$qry = "SELECT name, description, sex, age, nationality, trope, voice, o.title FROM chracter c INNER JOIN operetta o ON c.oid = o.id "
			. "WHERE voice LIKE ?";
		if (!($stmt = $mysqli->prepare($qry))) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		} 
		if (!($stmt->bind_param("s",$v))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 	
		if (!($stmt->execute())) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		}
		if (!($stmt->bind_result($name, $desc, $sex, $age, $nationality, $trope, $voice, $title))) {
			echo "Bind failed: " . $stmt->errno." ".$stmt->error;
		}

		// display results
		echo "<th>Character</th>\n<th>Description</th>\n<th>Sex</th>\n<th>Age</th>\n<th>Nationality</th>\n<th>Trope</th>\n<th>Voice</th>\n<th>Operetta</th>\n</tr>";
		while ($stmt->fetch()){
		echo "<tr>\n<td>" . $name . "\n</td>\n<td>" . htmlspecialchars($desc, ENT_QUOTES) . "\n</td>\n<td>" . $sex . "\n</td>\n<td>" . $age . "\n</td>\n<td>"
			. $nationality . "\n</td>\n<td>" . $trope . "\n</td>\n<td>" . $voice ."\n</td>\n<td>" . $title . "\n</td>\n</tr>";
		}	
		$stmt->close();
	} else {
		switch ($_POST['filter']){
			// song button query
			case 'Songs':
				$qry = "SELECT s.title, o.title, cs.bars, hi.name, lo.name FROM song s INNER JOIN operetta o ON s.oid = o.id "
					. "INNER JOIN charsong cs ON cs.sid = s.id INNER JOIN note hi ON cs.hi = hi.fq INNER JOIN note lo ON cs.lo = lo.fq WHERE cs.cid = ?";
				break;
			// entanglement button query
			case 'Entanglements':
				$qry = "SELECT c1.name, r1.description, c2.name, r2.description FROM entanglement e INNER JOIN charrole cr1 ON e.cid1 = cr1.cid AND e.rid1 = cr1.rid "
					. "INNER JOIN charrole cr2 ON e.cid2 = cr2.cid AND e.rid2 = cr2.rid INNER JOIN chracter c1 ON cr1.cid = c1.id INNER JOIN chracter c2 ON cr2.cid = c2.id "
					. "INNER JOIN role r1 ON cr1.rid = r1.id INNER JOIN role r2 ON cr2.rid = r2.id WHERE c1.id = ? OR c2.id = ?";
				break;
		}

		// query database		
		if (!($stmt = $mysqli->prepare($qry))) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		} 
		if ($_POST['filter'] == 'Entanglements'){
			if (!($stmt->bind_param("ii",$_POST['cid'],$_POST['cid']))) {
				echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
			} 				
		} else {
			if (!($stmt->bind_param("i",$_POST['cid']))) {
				echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
			} 						
		}

		if (!($stmt->execute())) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		}

		switch ($_POST['filter']){
			// display results for song search
			case 'Songs':
				if (!($stmt->bind_result($stitle, $otitle, $bars, $hi, $lo))) {
					echo "Bind failed: " . $stmt->errno." ".$stmt->error;
				}
				echo "<th>Song Title</th>\n<th>Operetta</th>\n<th>Length of Part in Bars</th>\n<th>High Note</th>\n<th>Low Note</th>\n</tr>";
				while ($stmt->fetch()){
				echo "<tr>\n<td>" . htmlspecialchars($stitle, ENT_QUOTES) . "\n</td>\n<td>" .$otitle . "\n</td>\n<td>" . $bars ."\n</td>\n"
					. "<td>" . $hi . "\n</td>\n<td>" .$lo ."\n</td>\n</tr>";
				}
				break;
			// display results for entanglement search
			case 'Entanglements':
				if (!($stmt->bind_result($name1, $desc1, $name2, $desc2))) {
					echo "Bind failed: " . $stmt->errno." ".$stmt->error;
				}
				echo "<th>Character 1</th>\n<th>Role</th>\n<th>Character 2</th>\n<th>Role</th>\n</tr>";
				while ($stmt->fetch()){
				echo "<tr>\n<td>" . $name1 . "\n</td>\n<td>" . $desc1 . "\n</td>\n<td>" . $name2 ."\n</td>\n"
					. "<td>" . $desc2 . "\n</td>\n</tr>";
				}	
				break;
		}

		$stmt->close();
	} 
?>

			</table>
			<p><a href="viewchar.php">Return to characters</a></p>		
		</div>
	</body>
</html>
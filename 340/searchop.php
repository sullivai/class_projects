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
		<title>Operetta Filter Results</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div>
			<p><a href="viewop.php">Return to operettas</a></p>
			<table>
				<tr>

<?php
	switch ($_POST['filter']){
		// character search button query
		case 'Characters':
			$qry = "SELECT d.name, d.description, d.sex, d.age, d.nationality, d.trope, d.voice, lo.name, hi.name FROM (SELECT c.name, c.description, c.sex, c.age, c.nationality, "
				. "c.trope, c.voice, MIN(lo.fq) AS lofq, MAX(hi.fq) AS hifq FROM (chracter c INNER JOIN operetta o ON c.oid = o.id) "
				. "LEFT JOIN (charsong cs INNER JOIN note lo ON cs.lo = lo.fq INNER JOIN note hi ON cs.hi = hi.fq) ON c.id = cs.cid WHERE oid = ? "
				. "GROUP BY c.name) d LEFT JOIN note lo ON lo.fq = d.lofq LEFT JOIN note hi ON hi.fq = d.hifq";
			break;
		// song search button query	
		case 'Songs':
			$qry = "SELECT number, title, type, length  FROM song s WHERE oid = ?";
			break;
		// notes search button query (return highest and lowest note in opera along with who sung it in which song)
		case 'Notes':
			$qry = "SELECT c.name, s.title, hi.name, lo.name FROM charsong cs INNER JOIN chracter c ON cs.cid = c.id "
				. "INNER JOIN song s ON cs.sid = s.id INNER JOIN note hi ON cs.hi = hi.fq INNER JOIN note lo ON cs.lo = lo.fq INNER JOIN operetta o ON c.oid = o.id "
				. "WHERE hi = (SELECT MAX(hi) from charsong cs INNER JOIN song s ON cs.sid = s.id INNER JOIN operetta o ON s.oid = o.id WHERE o.id = ?) AND o.id = ? "
				. "UNION ALL SELECT c.name, s.title, hi.name, lo.name FROM charsong cs INNER JOIN chracter c ON cs.cid = c.id "
				. "INNER JOIN song s ON cs.sid = s.id INNER JOIN note hi ON cs.hi = hi.fq INNER JOIN note lo ON cs.lo = lo.fq INNER JOIN operetta o ON c.oid = o.id "
				. "WHERE lo = (SELECT MIN(lo) from charsong cs INNER JOIN song s ON cs.sid = s.id INNER JOIN operetta o ON s.oid = o.id WHERE o.id = ?) AND o.id = ?";
			break;
		// settings search button query
		case 'Settings':
			$qry = "SELECT o.period, s.act, s.location, s.detail FROM setting s INNER JOIN operetta o ON s.oid = o.id WHERE o.id = ?";
			break;
		// entanglement search button query
		case 'Entanglements':
			$qry = "SELECT c1.name, r1.description, c2.name, r2.description FROM entanglement e INNER JOIN charrole cr1 ON e.cid1 = cr1.cid AND e.rid1 = cr1.rid "
				. "INNER JOIN charrole cr2 ON e.cid2 = cr2.cid AND e.rid2 = cr2.rid INNER JOIN chracter c1 ON cr1.cid = c1.id INNER JOIN chracter c2 ON cr2.cid = c2.id "
				. "INNER JOIN role r1 ON cr1.rid = r1.id INNER JOIN role r2 ON cr2.rid = r2.id INNER JOIN operetta o ON c1.oid = o.id WHERE o.id = ?";
			break;
	}

	// query database
	if (!($stmt = $mysqli->prepare($qry))) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	} 
	if ($_POST['filter'] == 'Notes'){
		if (!($stmt->bind_param("iiii",$_POST['op'],$_POST['op'],$_POST['op'],$_POST['op']))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 				
	} else {
		if (!($stmt->bind_param("i",$_POST['op']))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 						
	}

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	switch ($_POST['filter']){
		// display character search results
		case 'Characters':
			if (!($stmt->bind_result($name, $desc, $sex, $age, $nat, $trope, $voice, $lo, $hi))) {
				echo "Bind failed: " . $stmt->errno." ".$stmt->error;
			}
			echo "<th>Name</th>\n<th>Description</th>\n<th>Sex</th>\n<th>Age</th>\n<th>Nationality</th>\n"
				. "<th>Trope</th>\n<th>Voice</th>\n<th>Range</th>\n</tr>";
			while ($stmt->fetch()){
			echo "<tr>\n<td>" . $name . "\n</td>\n<td>" . htmlspecialchars($desc, ENT_QUOTES) . "\n</td>\n<td>" . $sex ."\n</td>\n"
				. "<td>" . $age . "\n</td>\n<td>" . $nat . "\n</td>\n<td>" . $trope . "\n</td>\n<td>" . $voice 
				. "\n</td>\n<td>" . $lo . " - " . $hi ."\n</td>\n</tr>";
			}
			break;

		case 'Songs':
		// display song search results
			if (!($stmt->bind_result($num, $title, $type, $len))) {
				echo "Bind failed: " . $stmt->errno." ".$stmt->error;
			}
			echo "<th>Number</th>\n<th>Title</th>\n<th>Type</th>\n<th>Length (bars)</th>\n</tr>";
			while ($stmt->fetch()){
			echo "<tr>\n<td>" . $num . "\n</td>\n<td>" . htmlspecialchars($title, ENT_QUOTES) . "\n</td>\n<td>" . $type ."\n</td>\n"
				. "<td>" . $len . "\n</td>\n</tr>";
			}
			break;
		// display note search results
		case 'Notes':
			if (!($stmt->bind_result($name, $title, $hi, $lo))) {
				echo "Bind failed: " . $stmt->errno." ".$stmt->error;
			}
			echo "<th>Character</th>\n<th>Title</th>\n<th>Highest Note</th>\n<th>Lowest Note</th>\n</tr>";
			while ($stmt->fetch()){
			echo "<tr>\n<td>" . $name . "\n</td>\n<td>" . htmlspecialchars($title, ENT_QUOTES) . "\n</td>\n<td>" . $hi ."\n</td>\n"
				. "<td>" . $lo . "\n</td>\n</tr>";
			}		
			break;	
		// display setting search results
		case 'Settings':
			if (!($stmt->bind_result($pd, $act, $loc, $detail))) {
				echo "Bind failed: " . $stmt->errno." ".$stmt->error;
			}
			echo "<th>Time period</th>\n<th>Act</th>\n<th>Location</th>\n<th>Setting</th>\n</tr>";
			while ($stmt->fetch()){
			echo "<tr>\n<td>" . $pd . "\n</td>\n<td>" . $act . "\n</td>\n<td>" . $loc ."\n</td>\n"
				. "<td>" . htmlspecialchars($detail, ENT_QUOTES) . "\n</td>\n</tr>";
			}	
			break;
		// display entanglement search results
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
?>

			</table>
			<p><a href="viewop.php">Return to operettas</a></p>			
		</div>
	</body>
</html>
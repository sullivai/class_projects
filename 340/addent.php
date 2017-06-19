<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
	// Redirect back to table view page
	echo '<meta http-equiv="refresh" content="2;URL=\'viewent.php\'">';
	// Turn on error reporting
	ini_set('display_errors','On');
	// connect to the database
	$mysqli = new mysqli("XXXXXXXXXXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX");
	if($mysqli->connect_errno){
		echo "Connection error " . $mysqli->connect_errno." ".$mysqli->connect_error;
	}

	// Get all values for primary key
	parse_str($_POST['cr1'], $params1);
	parse_str($_POST['cr2'], $params2);
	// Insert new values into table
	if (!($stmt = $mysqli->prepare("INSERT INTO entanglement (cid1, rid1, cid2, rid2) VALUES (?,?,?,?)"))) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	} 

	if (!($stmt->bind_param("iiii",$params1['cid1'],$params1['rid1'],$params2['cid2'],$params2['rid2']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())){
		echo "Execute failed: " . $stmt->errno." ".$stmt->error;	
	} else {
		echo "Added " . $stmt->affected_rows . " row to entanglement";
	}
?>
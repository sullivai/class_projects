<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
	// Redirect back to table view page
	echo '<meta http-equiv="refresh" content="2;URL=\'viewset.php\'">';
	// Turn on error reporting
	ini_set('display_errors','On');
	// connect to the database
	$mysqli = new mysqli("XXXXXXXXXXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX");
	if($mysqli->connect_errno){
		echo "Connection error " . $mysqli->connect_errno." ".$mysqli->connect_error;
	}
	// extract both values of primary key	
	parse_str($_POST['set'], $params);

	// Delete row from table	
	if (!($stmt = $mysqli->prepare("DELETE FROM setting WHERE oid = ? AND act = ?"))) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	} 

	if (!($stmt->bind_param("is", $params['oid'], $params['act']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())){
		echo "Execute failed: " . $stmt->errno." ".$stmt->error;	
	} else {
		echo "Deleted " . $stmt->affected_rows . " row from setting";
	}
?>
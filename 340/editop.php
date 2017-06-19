<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
	// Redirect back to table view page
	echo '<meta http-equiv="refresh" content="2;URL=\'viewop.php\'">';
	// Turn on error reporting
	ini_set('display_errors','On');
	// connect to the database
	$mysqli = new mysqli("XXXXXXXXXXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX");
	if($mysqli->connect_errno){
		echo "Connection error " . $mysqli->connect_errno." ".$mysqli->connect_error;
	}

	// Make sure operetta name is not null
	if (!$_POST['opName']) {
		echo 'Operetta name required';
	} else {
		// Update values in given row
		if (!($stmt = $mysqli->prepare("UPDATE operetta SET title=?, subtitle=?, premiere=?, first_run=?, acts=?, theme=?, period=? WHERE id = ?"))) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		} 

		if (!($stmt->bind_param("ssiiissi",$_POST['opName'],$_POST['subT'],$_POST['prem'],$_POST['run'],$_POST['acts'],$_POST['theme'],$_POST['period'],$_POST['opid']))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 

		if (!($stmt->execute())){
			echo "Execute failed: " . $stmt->errno." ".$stmt->error;	
		} else {
			echo "Updated " . $stmt->affected_rows . " row in operetta";
		}
	}
?>
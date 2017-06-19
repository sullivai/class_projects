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
		// Insert new values into table
		if (!($stmt = $mysqli->prepare("INSERT INTO operetta (title, subtitle, premiere, first_run, acts, theme, period) VALUES (?,?,?,?,?,?,?)"))) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		} 

		if (!($stmt->bind_param("ssiiiss",$_POST['opName'],$_POST['subT'],$_POST['prem'],$_POST['run'],$_POST['acts'],$_POST['theme'],$_POST['period']))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 

		if (!($stmt->execute())){
			echo "Execute failed: " . $stmt->errno." ".$stmt->error;	
		} else {
			echo "Added " . $stmt->affected_rows . " row to operetta";
		}
	}	
?>
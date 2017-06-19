<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
	// Redirect back to table view page
	echo '<meta http-equiv="refresh" content="2;URL=\'viewchar.php\'">';
	// Turn on error reporting
	ini_set('display_errors','On');
	// connect to the database
	$mysqli = new mysqli("XXXXXXXXXXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX");
	if($mysqli->connect_errno){
		echo "Connection error " . $mysqli->connect_errno." ".$mysqli->connect_error;
	}

	// Make sure character name is not null
	if (!$_POST['CharName']) {
		echo 'Character name required';
	} else {
		// Insert new values into table
		if (!($stmt = $mysqli->prepare("INSERT INTO chracter (name, description, sex, age, nationality, trope, voice, oid) VALUES (?,?,?,?,?,?,?,?)"))) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		} 

		if (!($stmt->bind_param("sssssssi",$_POST['CharName'],$_POST['desc'],$_POST['sex'],$_POST['age'],$_POST['nat'],$_POST['trope'],$_POST['voice'],$_POST['op']))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 

		if (!($stmt->execute())){
			echo "Execute failed: " . $stmt->errno." ".$stmt->error;	
		} else {
			echo "Added " . $stmt->affected_rows . " row to chracter";
		}
	}
?>
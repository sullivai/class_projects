<!-- Aimee Sullivan CS340 Final Project 05 June 2016 -->

<?php 
	// Redirect back to table view page
	echo '<meta http-equiv="refresh" content="2;URL=\'viewsong.php\'">';
	// Turn on error reporting
	ini_set('display_errors','On');
	// connect to the database
	$mysqli = new mysqli("XXXXXXXXXXXXXXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX","XXXXXXXXXXXXXXXXXXXXXX");
	if($mysqli->connect_errno){
		echo "Connection error " . $mysqli->connect_errno." ".$mysqli->connect_error;
	}

	// Make sure song name is not null
	if (!$_POST['sTitle']) {
		echo 'Song title required.';
	} else {
		// Insert new values into table
		if (!($stmt = $mysqli->prepare("INSERT INTO song (title, type, length, number, oid) VALUES (?,?,?,?,?)"))) {
			echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
		} 

		if (!($stmt->bind_param("ssiii",$_POST['sTitle'],$_POST['sType'],$_POST['len'],$_POST['num'],$_POST['op']))) {
			echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
		} 

		if (!($stmt->execute())){
			if($stmt->errno == 1062) {
				echo "Song number in operetta already exists.";
			} else {
				echo "Execute failed: " . $stmt->errno." ".$stmt->error;				
			}
		} else {
			echo "Added " . $stmt->affected_rows . " row to song";
		}
	}
?>
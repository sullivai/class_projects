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
		<title>Edit Operetta</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div>
			<form method="post" action="editop.php">
				<fieldset>
					<legend>Update Opera Information</legend>

<?php 
	// Populate form with old values from table
	if (!($stmt = $mysqli->prepare("SELECT id, title, subtitle, premiere, first_run, acts, theme, period FROM operetta WHERE id = ?"))) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	} 

	if (!($stmt->bind_param("i",$_POST['op']))) {
		echo "Bind failed: " . $stmt->errno ." ".$stmt->error;
	} 

	if (!($stmt->execute())) {
		echo "Prepare failed: " . $stmt->errno." ".$stmt->error;
	}

	if (!($stmt->bind_result($id, $title, $sub, $prem, $firstrun, $acts, $theme, $pd))) {
		echo "Bind failed: " . $stmt->errno." ".$stmt->error;
	}

	while ($stmt->fetch()){
		echo " <input type='hidden' name='opid' value='". $id ."' />\n"
				. "<p>Title: <input type='text' name='opName' value='". $title ."' /></p>\n"
				. "<p>Subtitle: <input type='text' name='subT' value='". htmlspecialchars($sub, ENT_QUOTES) . "' /></p>\n"
				. "<p>Premiere: <input type='number' name='prem' value='" . $prem . "' /></p>\n" 
				. "<p>First Run: <input type='number' name='run' value='" . $firstrun ."' /></p>\n"
				. "<p>Acts: <input type='number' name='acts' value='" . $acts ."' /></p>\n"
				. "<p>Theme: <textarea name='theme' >" . htmlspecialchars($theme, ENT_QUOTES) . "</textarea></p>\n"
				. "<p>Period: <input type='text' name='period' value='" . $pd ."' />\n"
				. "<input type='submit' name='updateOp' value='Update' />";
	}

	$stmt->close();
?>

				</fieldset>		
			</form>
		</div>
	</body>
</html>
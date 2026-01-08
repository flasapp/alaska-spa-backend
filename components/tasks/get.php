<?php
	// INCLUDE CLASS
	require("classes/Task.php");

	$conn 		= new Connection();
	$objTask	= new Task();

	$response = $objTask->getAll($conn);
	echo json_encode($response);
?>

<?php
	// INCLUDE CLASS
	require("classes/Task.php");

	$conn 		= new Connection();
	$objTask	= new Task();

	$body 	= json_decode(file_get_contents('php://input'), true);
	
    $response = $objTask->create($conn, $body);
    echo json_encode($response);
?>

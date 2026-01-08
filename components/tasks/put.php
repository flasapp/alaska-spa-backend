<?php
	// INCLUDE CLASS
	require("classes/Task.php");

	$conn 		= new Connection();
	$objTask	= new Task();

	$body 	= json_decode(file_get_contents('php://input'), true);
	$id     = $match['params']['id'];

    $response = $objTask->update($conn, $id, $body);
    echo json_encode($response);

?>

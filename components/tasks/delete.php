<?php
	// INCLUDE CLASS
	require("classes/Task.php");

	$conn 		= new Connection();
	$objTask	= new Task();

	$id     = $match['params']['id'];

    $response = $objTask->delete($conn, $id);
    echo json_encode($response);

?>

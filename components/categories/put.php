<?php
	// INCLUDE CLASS
	require("classes/Categories.php");

	$conn 		= new Connection();
	$objCategory = new Category();

	$id = $match['params']['id'];
	$body = json_decode(file_get_contents('php://input'), true);
	
    $response = $objCategory->update($conn, $id, $body);
    echo json_encode($response);
?>

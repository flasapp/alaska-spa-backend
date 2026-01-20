<?php
	// INCLUDE CLASS
	require("classes/Categories.php");

	$conn 		= new Connection();
	$objCategory = new Category();

	$body 	= json_decode(file_get_contents('php://input'), true);
	
    $response = $objCategory->create($conn, $body);
    echo json_encode($response);
?>

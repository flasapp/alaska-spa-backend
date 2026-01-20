<?php
	// INCLUDE CLASS
	require("classes/Products.php");

	$conn 		= new Connection();
	$objProduct = new Product();

	$data = json_decode(file_get_contents('php://input'), true);
	
    $response = $objProduct->create($conn, $data);
    echo json_encode($response);
?>

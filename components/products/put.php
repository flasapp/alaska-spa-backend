<?php
	// INCLUDE CLASS
	require("classes/Products.php");

	$conn 		= new Connection();
	$objProduct = new Product();

	$id = $match['params']['id'];
	$data = json_decode(file_get_contents('php://input'), true);
	
    $response = $objProduct->update($conn, $id, $data);
    echo json_encode($response);
?>

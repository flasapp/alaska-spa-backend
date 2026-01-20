<?php
	// INCLUDE CLASS
	require("classes/Products.php");

	$conn 		= new Connection();
	$objProduct = new Product();

	$id = $match['params']['id'];
	
    $response = $objProduct->delete($conn, $id);
    echo json_encode($response);
?>

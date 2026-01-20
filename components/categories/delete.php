<?php
	// INCLUDE CLASS
	require("classes/Categories.php");

	$conn 		= new Connection();
	$objCategory = new Category();

	$id = $match['params']['id'];
	
    $response = $objCategory->delete($conn, $id);
    echo json_encode($response);
?>

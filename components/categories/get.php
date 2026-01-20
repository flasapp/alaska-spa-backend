<?php
	// INCLUDE CLASS
	require("classes/Categories.php");

	$conn 		= new Connection();
	$objCategory = new Category();

	if(isset($match['params']['id'])){
		$id = $match['params']['id'];
		$response = $objCategory->getById($conn, $id);
	} else {
		$params = $_GET;
		$response = $objCategory->getAll($conn, $params);
	}
	
	echo json_encode($response);
?>

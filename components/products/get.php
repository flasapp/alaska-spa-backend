<?php 
	// INCLUDE CLASS
	require("classes/Products.php");

	$conn 		= new Connection();
	$obj		= new Product();
	$params 	= $match['params'];
	$name 		= $match['name'];
	
	if($name == 'featured'){
		$response 	= $obj->getFeaturedProducts($conn);
		echo json_encode($response);
	}else {
		$response 	= "No name";
		echo json_encode($name);
	}
	
?>
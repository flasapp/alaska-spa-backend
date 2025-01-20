<?php 
	// INCLUDE CLASS
	require("classes/Categories.php");

	$conn 		= new Connection();
	$obj		= new Category();
	$params 	= $match['params'];
	$name 		= $match['name'];
	
	if($name == 'categories-all'){
		$id 		= $params["id"];
		$response 	= $obj->getAllCategories($conn);
		echo json_encode($response);
	}else {
		$response 	= "No name";
		echo json_encode($name);
	}
	
?>
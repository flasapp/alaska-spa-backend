<?php 
	// INCLUDE CLASS
	require("classes/Categories.php");

	$conn 		= new Connection();
	$obj		= new Category();
	$params 	= $match['params'];
	$name 		= $match['name'];
	
	if($name == 'categories-all'){
		$response 	= $obj->getAllCategories($conn);
		echo json_encode($response);
	}else if($name == 'get-cat'){
		$response 	= $obj->getCategoryById($conn, $params['id']);
		echo json_encode($response);
	}else {
		$response 	= "No name";
		echo json_encode($name);
	}
	
?>
<?php
	// INCLUDE CLASS
	require("classes/Products.php");

	$conn 		= new Connection();
	$objProduct = new Product();

	$params = $_GET;

	if($match['name'] == 'get-by-category'){
		$categoryId = $match['params']['id'];
		$response = $objProduct->getByCategory($conn, $categoryId, $params);
	} else if($match['name'] == 'featured') {
		$response = $objProduct->getFeatured($conn, $params);
	} else if($match['name'] == 'get-by-name'){
		$params['where']['name'] = $match['params']['name'];
		$response = $objProduct->getAll($conn, $params);
	} else if(isset($match['params']['id']) && ($match['name'] == 'get-product' || $match['name'] == 'product-by-id-new')){
		$id = $match['params']['id'];
		$response = $objProduct->getById($conn, $id);
	} else {
		$response = $objProduct->getAll($conn, $params);
	}
	
	echo json_encode($response);
?>
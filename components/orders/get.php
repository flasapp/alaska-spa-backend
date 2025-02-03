<?php 
	// INCLUDE CLASS
	require("classes/Orders.php");

	$conn 		= new Connection();
	$obj		= new Order();
	$params 	= $match['params'];
	$name 		= $match['name'];
	
	// GET ALL ORDERS BY USER
	if($name == 'orders-by-user'){
		$id 		= $params["id"];
		$response 	= $obj->getOrdersByUser($conn, $id);
		echo json_encode($response);
	}
	
?>
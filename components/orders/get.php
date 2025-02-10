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
	}else if($name == 'order-by-user'){
		$id 		= $params["id"];
		$user 		= $params["user"];
		$response 	= $obj->getOrderByUser($conn, $id, $user);
		echo json_encode($response);
	}
?>
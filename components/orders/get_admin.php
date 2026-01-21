<?php
	// INCLUDE CLASS
	require_once("classes/Orders.php");

	$conn 		= new Connection();
	$objOrder   = new Order();

	if($match['name'] == 'order-detail-admin'){
		$id = $match['params']['id'];
		$response = $objOrder->getById($conn, $id);
	} else {
		$params = $_GET;
		$response = $objOrder->getAll($conn, $params);
	}

	echo json_encode($response);
?>

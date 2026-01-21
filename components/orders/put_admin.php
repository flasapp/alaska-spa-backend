<?php
	// INCLUDE CLASS
	require_once("classes/Orders.php");

	$conn 		= new Connection();
	$objOrder   = new Order();

	$id = $match['params']['id'];
	
	// Get body data
	$data = json_decode(file_get_contents('php://input'), true);
	
	if (!$data) {
		// Fallback to $_POST if json_decode fails (though normally it's JSON)
		$data = $_POST;
	}

	$response = $objOrder->update($conn, $id, $data);
	
	echo json_encode($response);
?>

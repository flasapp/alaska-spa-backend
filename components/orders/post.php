<?php
	// INCLUDE CLASS
	require("classes/Orders.php");

	$conn 		= new Connection();
	$obj		= new Order();

	$params 	= json_decode(file_get_contents('php://input'), true);
	$name 		= $match['name'];

	if($name == 'order-new'){
		$response = $obj->newOrder($conn,$params);
		echo json_encode($response);
	}else {
		echo json_encode( array("response" => 'err post Order') );
	}

?>

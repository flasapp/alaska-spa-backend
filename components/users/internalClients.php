<?php 
	// INCLUDE CLASS
	require("classes/User.php");

	$conn 		= new Connection();
	$objUsr		= new User();
	$offset 		= $_GET['offset'];
	$limit 		= $_GET['limit'];
	$response 	= $objUsr->getIntClients($offset, $limit, $conn);
	
	echo json_encode($response);
?>
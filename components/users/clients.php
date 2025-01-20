<?php 
	// INCLUDE CLASS
	require("classes/User.php");

	$conn 		= new Connection();
	$objUsr		= new User();
	$offset 		= $_GET['offset'];
	$limit 		= $_GET['limit'];
	$name 		= $_GET['name'];
	$email 		= $_GET['email'];
	$response 	= $objUsr->getAllClients($offset, $limit, $name, $email, $conn);
	

	echo json_encode($response);
?>
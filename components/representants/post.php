<?php
	// INCLUDE CLASS
	require_once("classes/Representants.php");

	$conn 		= new Connection();
	$objRep   	= new Representant();
	$data 		= json_decode(file_get_contents('php://input'), true);

	$response 	= $objRep->create($conn, $data);

	echo json_encode($response);
?>

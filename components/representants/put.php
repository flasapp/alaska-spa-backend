<?php
	// INCLUDE CLASS
	require_once("classes/Representants.php");

	$conn 		= new Connection();
	$objRep   	= new Representant();
	$id 		= $match['params']['id'];
	$data 		= json_decode(file_get_contents('php://input'), true);

	$response 	= $objRep->update($conn, $id, $data);

	echo json_encode($response);
?>

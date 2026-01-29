<?php
	// INCLUDE CLASS
	require_once("classes/Representants.php");

	$conn 		= new Connection();
	$objRep   	= new Representant();
	$id 		= $match['params']['id'];

	$response 	= $objRep->delete($conn, $id);

	echo json_encode($response);
?>

<?php
	// INCLUDE CLASS
	require_once("classes/Representants.php");

	$conn 		= new Connection();
	$objRep   	= new Representant();

	// MATCH ROUTING
	if($match['name'] == 'representant-detail'){
		$id = $match['params']['id'];
		$response = $objRep->getById($conn, $id);
	} else {
		$params = $_GET;
		$response = $objRep->getAll($conn, $params);
	}

	echo json_encode($response);
?>

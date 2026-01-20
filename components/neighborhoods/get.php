<?php
	// INCLUDE CLASS
	require("classes/Neighborhood.php");

	$conn 		= new Connection();
	$objNeighborhood = new Neighborhood();

	if(isset($match['params']['id'])){
		$id = $match['params']['id'];
		$response = $objNeighborhood->getById($conn, $id);
	} else {
		$params = $_GET;
		$response = $objNeighborhood->getAll($conn, $params);
	}
	
	echo json_encode($response);
?>

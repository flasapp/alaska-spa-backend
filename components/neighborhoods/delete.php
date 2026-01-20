<?php
	// INCLUDE CLASS
	require("classes/Neighborhood.php");

	$conn 		= new Connection();
	$objNeighborhood = new Neighborhood();

	$id = $match['params']['id'];
	
    $response = $objNeighborhood->delete($conn, $id);
    echo json_encode($response);
?>

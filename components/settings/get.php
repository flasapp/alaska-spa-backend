<?php 
	// INCLUDE CLASS
	require("classes/Settings.php");

	$conn 		= new Connection();
	$obj		= new Setting();
	$params 	= $match['params'];
	$name 		= $match['name'];
	
	if($name == 'neighbourhoods'){
		$response 	= $obj->getNeigbourhoods($conn);
		echo json_encode($response);
	}else if($name == 'configs'){
		$response 	= $obj->getSettings($conn);
		echo json_encode($response);
	}else {
		$response 	= "No name";
		echo json_encode($name);
	}
	
?>
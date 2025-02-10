<?php
	// INCLUDE CLASS
	require("classes/User.php");

	$conn 		= new Connection();
	$objUsr		= new User();

	$body 	= json_decode(file_get_contents('php://input'), true);
	$name 		= $match['name'];

	if($name == 'update-profile'){
		$response = $objUsr->updateProfile($conn, $body);
		echo json_encode($response);
	}else {
		echo json_encode( array("response" => 'err post users') );
	}

?>

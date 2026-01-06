<?php
	// INCLUDE CLASS
	require("classes/User.php");

	$conn 		= new Connection();
	$objUsr		= new User();

	$params 	= json_decode(file_get_contents('php://input'), true);
	$name 		= $match['name'];

	if($name == 'user-login'){
		$response = $objUsr->loginFrontend($conn,$params);
		echo json_encode($response);
	}else if($name == 'admin-login'){
		$response = $objUsr->loginAdmin($conn,$params);
		echo json_encode($response);
	}else if($name == 'user-signup'){
		$response = $objUsr->signupFrontend($conn,$params);
		echo json_encode($response);
	}else if($name == 'user-update'){
		$response = $objUsr->updateUser($conn,$params);
		echo json_encode($response);
	}else if($name == 'request-code-password'){
		$response = $objUsr->requestCodePassword($conn,$params);
		echo json_encode($response);
	}else if($name == 'change-password'){
		$response = $objUsr->resetPassword($conn,$params);
		echo json_encode($response);
	}else {
		echo json_encode( array("response" => 'err post users') );
	}

?>

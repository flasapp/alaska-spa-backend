<?php
	// INCLUDE CLASS
	require("classes/User.php");

	$conn 		= new Connection();
	$objUsr		= new User();

	$params 	= json_decode(file_get_contents('php://input'), true);
	$name 		= $match['name'];

	if($params){
		// GET USER BY ID
		if($name == 'user-new'){
			$response = $objUsr->insertUser($conn,$params);
			echo json_encode($response);
		}
		if($name == 'client-new'){
			$response = $objUsr->insertClient($conn,$params);
			echo json_encode($response);
		}
		if($name == 'user-update'){
			$response = $objUsr->updateUser($conn,$params);
			echo json_encode($response);
		}
		if($name == 'user-login'){
			$response = $objUsr->login($conn,$params);
			echo json_encode($response);
		}
		if($name == 'google-login'){
			$response = $objUsr->googleLogin($conn,$params);
			echo json_encode($response);
		}
		if($name == 'facebook-login'){
			$response = $objUsr->facebookLogin($conn,$params);
			echo json_encode($response);
		}
		if($name == 'schedule-new'){
			$response = $objUsr->newScheduleByAdmin($conn,$params);
			echo json_encode($response);
		}
		if($name == 'request-code-password'){
			$response = $objUsr->requestCodePassword($conn,$params);
			echo json_encode($response);
		}
		if($name == 'change-password'){
			$response = $objUsr->resetPassword($conn,$params);
			echo json_encode($response);
		}
		
	} else {
		echo json_encode( array("response" => 'err') );
	}

?>

<?php 
	// INCLUDE CLASS
	require("classes/User.php");

	$conn 		= new Connection();
	$objUsr		= new User();
	$params 	= $match['params'];
	$name 		= $match['name'];
	


	if($params){
		
		// GET USER BY ID
		if($name == 'user-by-id'){
			$id 		= $params["id"];
			$response 	= $objUsr->getUserById($conn,$id);
			echo json_encode($response);
		}
		if($name == 'schedule-by-admin'){
			$id 		= $params["id"];
			$response 	= $objUsr->getScheduleByAdmin($conn,$id);
			echo json_encode($response);
		}
		

		

	} else {
		if($name == 'all-schedules-by-days'){
			$initDay 		= $_GET['initDay'];
			$finalDay 		= $_GET['finalDay'];
			$response 	= $objUsr->getAllSchedulesByDays($conn, $initDay, $finalDay);
			echo json_encode($response);
		} elseif($name == 'users-all'){
			$params = $_GET;
			$response = $objUsr->getAll($conn, $params);
			echo json_encode($response);
		}else{
			echo json_encode( array("response" => 'err') );
		}
	}
	
?>
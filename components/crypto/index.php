<?php 
	require("classes/User.php");

	$objUsr		= new User();
	$params 	= $match['params'];

	echo json_encode( array("psw" => $objUsr->cryptoPsw($params["psw"])) );
?>
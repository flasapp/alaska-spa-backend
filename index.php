<?php

require('config/conn.php');
require('classes/AltoRouter.php');
require('classes/Token.php');


	// CALL OBJS
	$router 	= new AltoRouter();
	$conn 		= new Connection();
	$objToken	= new Token();
	//DEV
	// $router->setBasePath('/flasapp/api/cesarrosano-api');
	//PD
	$router->setBasePath('/backend/api-spa');

	if($_SERVER["HTTP_TOKEN"]){
		$token 	= $_SERVER["HTTP_TOKEN"];
		$access = $objToken->checkToken($conn, $token);
		$access = $access["find_token"];

		if($access){
			// $userId = $access["userId"];
			// $userRole = $access["userRole"];
			// CRYPTO ALGORITHM
			$router->map('GET','/crypto/[a:psw]', 'components/crypto/index.php', 'crypto');
			// ALL USERS
			$router->map('GET','/user', 'components/users/index.php', 'users-all');
			// GET USERS BY ID	
			$router->map('GET','/user/[i:id]', 'components/users/get.php', 'user-by-id');
			// INSERT NEW USER
			$router->map('POST','/user', 'components/users/post.php', 'user-new');
			// UPDATE USER
			$router->map('PUT','/user/[i:id]', 'components/users/post.php', 'user-update');
			// NEW SCHEDULE
			$router->map('POST','/schedule', 'components/users/post.php', 'schedule-new');
			// DELETE SCHEDULE
			$router->map('DELETE','/schedule/[i:id]', 'components/users/delete.php', 'schedule-delete');

			// match current request
			$match = $router->match();

			if($match) {
			  require $match['target'];
			} else {
			   echo json_encode( array("error" => "Error: no existe la API access.") );
			}

		} else {
			echo json_encode( array("error" => "Error: token incorrecto.") );
		}
	} else {

		// MATCH ROUTING - DEFAULT
		$router->map('GET','/', 'components/home/index.php', 'home');
		//ALL CATEGORIES
		$router->map('GET','/categories', 'components/categories/get.php', 'categories-all');
		//GET CATEGORY BY ID
		$router->map('GET','/categories/[i:id]', 'components/categories/get.php', 'get-cat');
		//FEATURED PRODUCTS
		$router->map('GET','/products-featured', 'components/products/get.php', 'featured');
		//GET PRODUCT BY ID
		$router->map('GET','/product/[i:id]', 'components/products/get.php', 'get-product');
		//GET PRODUCTS BY CATEGORY
		$router->map('GET','/products-category/[i:id]', 'components/products/get.php', 'get-by-category');
		//GET PRODUCTS BY NAME
		$router->map('GET','/products-by-name/[a:name]', 'components/products/get.php', 'get-by-name');
		//SETTINGS NEIGHBOURHOODS
		$router->map('GET','/settings/neighbourhoods', 'components/settings/get.php', 'neighbourhoods');


		// LOGIN
		$router->map('POST','/login', 'components/users/post.php', 'user-login');
		// NEW CLIENT
		$router->map('POST','/user-new', 'components/users/post.php', 'client-new');
		// REQUEST NEW PASSWORD
		$router->map('POST','/request-code-password', 'components/users/post.php', 'request-code-password');
		// REQUEST NEW PASSWORD
		$router->map('POST','/change-password', 'components/users/post.php', 'change-password');

		// match current request
		$match = $router->match();

		if($match) {
		  require $match['target'];
		} else {
		   echo json_encode( array("error" => "Error: no existe la API SIN access.", $match) );
		}
	}



?>

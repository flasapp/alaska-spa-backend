<?php
	ini_set('display_errors', 'On');
	error_reporting(-1);
	
	require('config/conn.php');
	require('classes/AltoRouter.php');
	require('classes/Token.php');
	require('config/env.php');

	// CALL OBJS
	$router 	= new AltoRouter();
	$conn 		= new Connection();
	$objToken	= new Token();
	$router->setBasePath(CONTEXT);

	//Protected Routes
	if($_SERVER["HTTP_TOKEN"]){
		
		$token 	= $_SERVER["HTTP_TOKEN"];
		$tokenResponse = $objToken->checkToken($conn, $token);
		$access = $tokenResponse["find_token"];

		if($access){
			// $userId = $access["userId"];
			// $userRole = $access["userRole"];
			// CRYPTO ALGORITHM
			$router->map('GET','/crypto/[a:psw]', 'components/crypto/index.php', 'crypto');
			//Orders by user
			$router->map('GET','/orders-user/[i:id]', 'components/orders/get.php', 'orders-by-user');
			// match current request
			$match = $router->match();

			if($match) {
			  require $match['target'];
			} else {
			   echo json_encode( array("error" => "Error: no existe la API access.") );
			}

		} else {
			echo json_encode( array("error" => "Error: token incorrecto.", "tokenResponse" => $tokenResponse));
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
		//SETTINGS CONFIGS
		$router->map('GET','/settings/configs', 'components/settings/get.php', 'configs');
		//ORDERS BY USER
		$router->map('GET','/orders-user/[i:id]', 'components/orders/get.php', 'orders-by-user');
		//ORDERS BY ID
		$router->map('GET','/order-user/[i:id]/[a:user]', 'components/orders/get.php', 'order-by-user');
		//UPDATE PROFILE
		$router->map('POST','/update-profile', 'components/users/put.php', 'update-profile');

		
		//NEW ORDER
		$router->map('POST','/order-new', 'components/orders/post.php', 'order-new');
		// LOGIN
		$router->map('POST','/login', 'components/users/post.php', 'user-login');
		// LOGIN
		$router->map('POST','/signup', 'components/users/post.php', 'user-signup');
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

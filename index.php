<?php
	// CORS HANDLING
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
		// you want to allow, and if so:
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');    // cache for 1 day
	}

	// Access-Control headers are received during OPTIONS requests
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");         
		
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
			header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	
		exit(0);
	}

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
	// file_put_contents('debug.txt', print_r($_SERVER, true)); // Debug line commented out
	$token = null;
	$headers = null;
	if(isset($_SERVER["HTTP_AUTHORIZATION"])){
		$token = $_SERVER["HTTP_AUTHORIZATION"];
	} else if(isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
		$token = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
	} else if(isset($_SERVER["HTTP_TOKEN"])){
		$token = $_SERVER["HTTP_TOKEN"];
	}

	// Fallback for Apache
	if(!$token && function_exists('apache_request_headers')){
		$headers = apache_request_headers();
		if(isset($headers['Authorization'])){
			$token = $headers['Authorization'];
		}
	}

	$validToken = false;
	if($token){
		// Cleaning token "Bearer "
		if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
			$token = $matches[1];
		}

		$tokenResponse = $objToken->checkToken($conn, $token);
		if($tokenResponse["find_token"]){
			$validToken = true;
		}
	}

	if($validToken){
		// CRYPTO ALGORITHM
		$router->map('GET','/crypto/[a:psw]', 'components/crypto/index.php', 'crypto');
		// USERS ALL
		$router->map('GET','/users', 'components/users/get.php', 'users-all');
		// UPDATE USER (Admin)
		$router->map('PUT', '/users/[i:id]', 'components/users/put.php', 'update-user');
		//Orders by user
		$router->map('GET','/orders-user/[i:id]', 'components/orders/get.php', 'orders-by-user');
		// match current request
		$match = $router->match();

		if($match) {
			require $match['target'];
			exit();
		}
	}

	// MATCH ROUTING - DEFAULT (Public Routes)
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
	$router->map('GET','/orders-user/[i:id]', 'components/orders/get.php', 'orders-by-user-public');
	//ORDERS BY ID
	$router->map('GET','/order-user/[i:id]/[a:user]', 'components/orders/get.php', 'order-by-user');
	//UPDATE PROFILE
	$router->map('POST','/update-profile', 'components/users/put.php', 'update-profile');

	
	//NEW ORDER
	$router->map('POST','/order-new', 'components/orders/post.php', 'order-new');
	// LOGIN
	$router->map('POST','/admin-login', 'components/users/post.php', 'admin-login');
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
		// If custom handling for invalid token on protected route is needed, logic could go here.
		// Currently returning generic error.
		echo json_encode( array("error" => "Error: no existe la API.") );
	}



?>

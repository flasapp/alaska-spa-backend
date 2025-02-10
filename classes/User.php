<?php

require("config/curl.php");

class User {
	private $model = "users";

	public function getStructure($conn,$column){
		$sql	="SHOW COLUMNS FROM ".$this->model;
		$d 		= $conn->query($sql);
		$r 		= false;

		for ($i=0; $i < count($d) ; $i++) {
			if($d[$i]["Field"] == $column){
				$r = true;
			}
		}
		return $r;
	}
	// CRYPTO FUNCTION
	public function cryptoPsw($psw){
		$key 	= "y0ur.k3y";
		$ps 	= strtoupper(sha1($psw.$key));
		return 	$ps;
	}
	// GET USER BY ID
	public function getUserById($conn,$id){
		$sql	="SELECT `id`, `name`, `email`, `role`, `image`, `type`, `useWhatsapp` FROM ".$this->model." WHERE id='$id'";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			return $d[0];
		} else {
			return array("error" => "Error: no se encuentra el user.");
		}
	}
	//Login for e-commerce
	public function loginFrontend($conn, $user){
		// CLEAR FIELDS
		$pass = md5($user[pass]);
		//$ci = $user[ci];
		
		$sql 	= "SELECT * FROM usuarios WHERE mail = '$user[mail]' AND pass = '$pass'";
		$datos 	= $conn->query($sql);
		if($datos == ""){
			$d = array("response" => "err: to get user by ID:");	
			return $d;
		} else {
			$datos[0]["token"] =  $this->cryptoPsw($d[0]["pass"].$d[0]["mail"]);
			unset($datos[0]["pass"]);
			return $datos[0];	
		}
	}
	//Signup for e-commerce
	public function signupFrontend($conn, $user){
		//Check every field is on user
		if (empty($user['nomUsuario']) || empty($user['apellido']) || empty($user['tel']) || empty($user['mail']) || empty($user['pass']) || empty($user['fechaAlta'])) {
			return array("error" => "Faltan datos", "code" => "MISSING_DATA");
		}

		//First of all check if the email already exist
		$sqlExist =  "SELECT * FROM usuarios WHERE mail = '$user[mail]'";
    	$dataExist = $conn->query($sqlExist);

    	if (!empty($dataExist)) {
    		return array("error" => "Ya existe un usuario con ese email", "code" => "USER_EXISTS");
    	}

		//If doeasnt exist insert into tbale
		$user['pass'] = md5($user['pass']);
		$sql 	= "INSERT INTO usuarios 
					(nomUsuario, apellido, tel, mail, pass, fechaAlta, rol, estado)
					VALUES 
					('$user[nomUsuario]', '$user[apellido]', '$user[tel]', '$user[mail]', '$user[pass]', '$user[fechaAlta]', 0, 1)";
		
		$datos 	= $conn->query($sql);
		if($datos == ""){
			$d = array("response" => "Ok", success => true);
			return $d;
		} else {
			$d = array("response" => "err: to add client");
			return $d;
		}
		

	}
	//Update user Profile
	public function updateUserProfile($conn, $user){

			$sql = "UPDATE ".$this->model." SET name = '$user[name]', role = '$user[role]', image = '$user[image]', type = '$user[type]', useWhatsapp = '$user[useWhatsapp]'";
			//Hash password
			if (!empty($user["password"]) || $user["password"]!="" ) {
	        	$user["password"]=$this->cryptoPsw($user["password"]);
	        	$sql .=", password = '$user[password]'";
	    	};
	    	if($user["deleted"]==0){
				$sql .=", deleted = 0";
	    	}
	    	if($user["deleted"]==1){
				$sql .=", deleted = 1";
	    	}
			$sql .=", updateAt = CURRENT_TIMESTAMP WHERE id='$user[id]'";
			$d 	= $conn->query($sql);
			// CALLBACK
			if(empty($d)){
				return array("response" => $sql, "user" => $user);
			} else {
				return array("error" => "Error: al actualizar el user.", "sql" => $sql);
			}

	}
	//Delete (inactivate) user
	public function deleteUser($conn, $id){

		$sql = "UPDATE ".$this->model." SET deleted = 1, updateAt = CURRENT_TIMESTAMP WHERE id='$id'";
		$d 	= $conn->query($sql);
		// CALLBACK
		if(empty($d)){
			return array("response" => 'OK', "sql" => $sql);
		} else {
			return array("error" => "Error: al actualizar el user.", "sql" => $sql);
		}
	}
	//Request password code
	public function requestCodePassword($conn, $data){
		$email = $data["email"];
		if($email == "" || is_null($email)){
			return array("error" => "Email incorrecto");
		}
		//Check if role is client and is not a google user
		$sql	="SELECT * FROM users WHERE email='$email' AND role = 'CLIENT' AND deleted = 0 AND googleId IS NULL";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$client = $d[0];
			//Code must be a number of 6 digits
			$code = rand(100000, 999999);
			//Here we should create a register on the DB with a token, createdAt and the user id
			$sqlToken = "INSERT INTO reset_password (userId, token, createdAt) VALUES ('".$client["id"]."', '".$code."', CURRENT_TIMESTAMP)";
			$dToken = $conn->query($sqlToken);
			if(empty($dToken)){
				//Then we should send an email to the user with a link to reset the password with the token
				//ENVIAR MAIL CLIENTE
				$email_to = $client["email"];

				$email_subject = "Peluquería Cesar Rosano - Cambio de contraseña";
				$titulo = "<h1 >Peluqueria Cesar Rosano</h1><div style='width:100%; min-width:80%; height:auto; display:block; color:#696763; font-family: sans-serif;'>
				  <h2>Hola ".$client["name"].",</h2>";
				$email_message = $titulo;
				$email_message .= "<h2>Ya falta menos para terminar de recuperar tu contraseña.</h2>";
				$email_message .= "Para terminar de recuperar tu contraseña, ingresa al siguiente link <a href='https://peluqueriacesarrosano.com.uy?resetCode=".$code."&email=".$email."'>Peluquería</a> y sigue los pasos.";
				$email_message .= "Recuerda que el código es válido por una hora.";
				// $email_message .= "<small>Vuelve a la web de la peluquería e inserta el código de arriba en la sección de <b>Cambiar contraseña</b> <a href='https://peluqueriacesarrosano.com.uy?resetCode=".$code."'>Peluquería</a></small>";
				$email_message .= "</div>";
				$obj=array(
					"site_title"=> "peluqueriacesarrosano.com.uy",
					"topic"=> $email_subject,
					"to_email"=>$email_to,
					"headers"=> array(
						"from_email"=> "mail-api@lexartlabs.com",
						"bcc_emails"=> "bcc_emails@lexartlabs.com"
					),
					"body"=> array(
						"big_logo"=> "https://peluqueriacesarrosano.com.uy/img/logo_for_email.png",
						"little_logo" => "",
						"slogan"=> "",
						"html_body"=> $email_message,
						"footer_color"=> "#ffffff",
						"footer_one"=> "",
						"footer_two"=> ""
					)

				);
				$curl = new Curl();
				$sendEmail1 = $curl->postCurl('https://mail-api.lexartlabs.com/mail/smtp/new',$obj);
				//Create a password based on the code and the timestamp
				$psw = $this->cryptoPsw($code.date("Y-m-d H:i:s"));
				return array("response" => 'OK');

			} else {
				return array("error" => "Error: al crear el token.", "sql" => $sqlToken);
			}
		} else {
			return array("error" => "Ha ocurrido un error.");
		}
	}
	//Reset password
	public function resetPassword($conn, $params){
		$token = $params["token"];
		$email = $params["email"];
		$password = $params["password"];
		//Check if the token is valid
		$sql	="SELECT * FROM reset_password WHERE token='$token' AND createdAt > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$reset = $d[0];
			//Update the user password
			$sql = "UPDATE users SET password = '".$this->cryptoPsw($password)."' WHERE email = '".$email."'";
			$d 	= $conn->query($sql);
			// CALLBACK
			if(empty($d)){
				//Delete the token
				$sql = "DELETE FROM reset_password WHERE token = '".$token."'";
				$d 	= $conn->query($sql);
				// CALLBACK
				if(empty($d)){
					return array("response" => 'OK');
				} else {
					return array("error" => "Error: al eliminar el token.", "sql" => $sql);
				}
			} else {
				return array("error" => "Error: al actualizar el password.", "sql" => $sql);
			}
		} else {
			return array("error" => "El código ha expirado.");
		}
	}
	//Update profile
	public function updateProfile($conn, $userInfo){
		$sql = "UPDATE usuarios 
        SET nomUsuario = '" . $userInfo['name'] . "', 
            apellido = '" . $userInfo['lastName'] . "', 
            mail = '" . $userInfo['email'] . "', 
            tel = '" . $userInfo['phone'] . "', 
            calle = '" . $userInfo['address']['street'] . "', 
            numero = '" . $userInfo['address']['number'] . "', 
            apto = '" . $userInfo['address']['depto'] . "', 
            esquina = '" . $userInfo['address']['corner'] . "', 
            idBarrio = '" . $userInfo['address']['neighbourhood'] . "'
        WHERE idUsuario = '" . $userInfo['id'] . "'";
		// $sql = "UPDATE 
		// 			usuarios SET 
		// 			nomUsuario = '$userInfo[name]', 
		// 			apellido = '$userInfo[lastName]', 
		// 			mail = '$userInfo[email]', 
		// 			tel = '$userInfo[phone]', 
		// 			calle = '$userInfo[address][street]', 
		// 			numero = '$userInfo[address][number]', 
		// 			apto = '$userInfo[address][depto]', 
		// 			esquina = '$userInfo[address][corner]', 
		// 			idBarrio = '$userInfo[address][neighbourhood]'
		// 			WHERE idUsuario = '$userInfo[id]'";
		$d 	= $conn->query($sql);
		// CALLBACK
		if(empty($d)){
			return array("response" => "OK", "user" => $d, success => true);
		} else {
			return array("error" => "Error: al actualizar el user.", "sql" => $sql);
		}
	}
}
?>

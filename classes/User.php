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
		$sql	="SELECT * FROM usuarios WHERE mail='$email'";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$client = $d[0];
			//Code must be a number of 6 digits
			$code = rand(000001, 999999);
			//Here we should create a register on the DB with a token, createdAt and the user id
			$sqlToken = "INSERT INTO reset_password (userId, token, createdAt) VALUES ('".$client["idUsuario"]."', '".$code."', CURRENT_TIMESTAMP)";
			$dToken = $conn->query($sqlToken);
			if(empty($dToken)){
				//Then we should send an email to the user with a link to reset the password with the token
				$site_url = "https://spa.alaskacongelados.com.uy";
				//ENVIAR MAIL CLIENTE
				$email_to = $client["mail"];
				$email_subject = "Cambio de contraseña";
				$header_body_email = "<div style='max-width: 600px; margin: auto; font-family: Arial, sans-serif; background: #f9f9f9; border-radius: 10px; overflow: hidden;'><div style='background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; text-align: center; padding: 25px;'><h1 style='margin: 0; font-size: 24px;'>Cambio de contraseña</h1></div><div style='padding: 20px; color: #333; text-align: center;'><p style='font-size: 18px;'>Código generado satisfactoriamente. 🎉</p><p style='font-size: 16px;'>Haz click en el link debajo e ingresa el código.</p>";
					$email_message = $header_body_email;
					// $email_message .= "<br>";
					$email_message .= "<div style='background: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px auto; display: inline-block;'><p style='margin: 0; font-size: 16px;'><strong>Código generado:</strong>".$code."</p></div>";
						$email_message .= "<a href='".$site_url."/recuperar-usuario/".$client["mail"]."' target='_blank' style='background: #6a11cb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-top: 15px;'>Ir a cambiar contraseña</a>";
						$email_message .="<p style='margin-top: 20px; font-size: 14px; color: #666;'>Ya estás solamente a un paso de poder cambiar tu contraseña y continuar comprando 😀</p>";
					$email_message .= "</div>"; //TO CLOSE
				$email_message .= "<div style='background: #eef2ff; text-align: center; padding: 15px; font-size: 14px; color: #666;'>© 2025 Alaska Congelados | Todos los derechos reservados</div></div>";
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// More headers
				$headers .= 'From: Alaska Congelados<alaskacongelados@gmail.com>' . "\r\n";
				@mail($email_to, $email_subject, $email_message, $headers);
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

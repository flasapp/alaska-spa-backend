<?php
require dirname(__DIR__) . '/config/env.php';

class User {
	private $model = "usuarios";
	//Get structure of db table
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
	//Login for admin
	public function loginAdmin($conn, $user){
		// CLEAR FIELDS
		$pass = md5($user['password']);
		
		$sql 	= "SELECT mail as email, nomUsuario as name, apellido as lastName, tel as phone, idUsuario as userId, estado as status, rol as role, pass FROM usuarios WHERE mail = '$user[email]' AND pass = '$pass' AND rol = 1 AND estado = 1";
		$datos 	= $conn->query($sql);
		if($datos == "" || empty($datos)){
			$d = array("response" => "err: invalid admin credentials or access denied", "error" => true);	
			return $d;
		} else {
			$datos[0]["token"] =  $this->cryptoPsw($datos[0]["pass"].$datos[0]["email"]);
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
				//ENVIAR MAIL CLIENTE
				$email_to = $client["mail"];
				$email_subject = "Cambio de contraseña";
				$header_body_email = "<div style='max-width: 600px; margin: auto; font-family: Arial, sans-serif; background: #f9f9f9; border-radius: 10px; overflow: hidden;'><div style='background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; text-align: center; padding: 25px;'><h1 style='margin: 0; font-size: 24px;'>Cambio de contraseña</h1></div><div style='padding: 20px; color: #333; text-align: center;'><p style='font-size: 18px;'>Código generado satisfactoriamente. 🎉</p><p style='font-size: 16px;'>Haz click en el link botón e ingresa el código.</p>";
					$email_message = $header_body_email;
					// $email_message .= "<br>";
					$email_message .= "<div style='background: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px auto; display: inline-block;'><p style='margin: 0; font-size: 16px;'><strong>Código:</strong> ".$code." </p></div>";
						$email_message .= "<a href='".SITE_URL."/recuperar-usuario/".$client["mail"]."' target='_blank' style='background: #6a11cb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-top: 15px;'>Cambiar contraseña</a>";
						$email_message .="<p style='margin-top: 20px; font-size: 14px; color: #666;'>Ya estás solamente a un paso de poder cambiar tu contraseña y continuar comprando 😀</p>";
						$email_message .="<p style='margin-top: 20px; font-size: 12px; color: #808080;'>⚠️ Recuerda que el código es válido por una hora ⚠️</p>";
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
			return array("error" => "Ha ocurrido un error.", "sql" => $sql);
		}
	}
	//Reset password
	public function resetPassword($conn, $params){
		$token = $params["token"];
		$mail = $params["mail"];
		$password = $params["password"];
		//Check if the token is valid
		$sql	="SELECT * FROM reset_password WHERE token='$token' AND createdAt > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$reset = $d[0];
			//Update the user password
			$passHashed = md5($password);
			$sql = "UPDATE usuarios SET pass = '".$passHashed."' WHERE mail = '".$mail."'";
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
	public function updateUser($conn, $id, $data){
		$updates = [];

		if(isset($data['name'])) $updates[] = "nomUsuario = '".$data['name']."'";
		if(isset($data['lastName'])) $updates[] = "apellido = '".$data['lastName']."'";
		if(isset($data['email'])) $updates[] = "mail = '".$data['email']."'";
		if(isset($data['phone'])) $updates[] = "tel = '".$data['phone']."'";
		if(isset($data['role'])) $updates[] = "rol = '".$data['role']."'";
		if(isset($data['status'])) $updates[] = "estado = '".$data['status']."'";

		if(isset($data["password"]) && $data["password"] != "" && !empty($data["password"])){
			$passHashed = md5($data["password"]);
			$updates[] = "pass = '$passHashed'";
		}

		if (empty($updates)) {
			return array("response" => "OK", "message" => "No fields to update", "success" => true);
		}

		$sql = "UPDATE ".$this->model." SET " . implode(', ', $updates) . " WHERE idUsuario = '$id'";
		
		$d = $conn->query($sql);
		
		if(empty($d)){
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al actualizar el usuario.", "sql" => $sql);
		}
	}

	//Get All Users
	public function getAll($conn, $params){
		$limit 	= (isset($params["limit"])) ? $params["limit"] : 10;
		$offset = (isset($params["offset"])) ? $params["offset"] : 0;
		$whereBase = "WHERE 1=1";

		if(isset($params["where"]["name"]) && $params["where"]["name"] != ""){
			$whereBase .= " AND (nomUsuario LIKE '%".$params["where"]["name"]."%' OR apellido LIKE '%".$params["where"]["name"]."%' OR CONCAT(nomUsuario, ' ', apellido) LIKE '%".$params["where"]["name"]."%')";
		}
		if(isset($params["where"]["email"]) && $params["where"]["email"] != ""){
			$whereBase .= " AND mail LIKE '%".$params["where"]["email"]."%'";
		}

		// Count Items
		$sqlCount = "SELECT COUNT(*) as total FROM ".$this->model." ".$whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		// Get Items
		$sql = "SELECT idUsuario as id, nomUsuario as name, apellido as lastName, mail as email, rol as role, estado as status, tel as phone FROM ".$this->model." ".$whereBase." LIMIT $offset, $limit";
		$d 	 = $conn->query($sql);
		if ($d == "") {
			$d = array();
		}
		
		return array("data" => $d, "count" => $total);
	}
}
?>

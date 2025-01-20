<?php

//
// EXAMPLE CLASS
//
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
	// GET ALL USERES
	public function getAllUsers($offset, $limit, $conn){
		//ENVIO COUNT TOTAL
		$sql1 	= "SELECT COUNT(*) FROM ".$this->model." WHERE deleted = 0";
		$datos1 	= $conn->query($sql1);
		if($datos1 == ""){
			$countItems = 0;
		} else {
			$countItems = $datos1[0]["COUNT(*)"];
		};

		$sql	="SELECT `id`, `name`, `email`, `role` FROM ".$this->model." WHERE deleted = 0 LIMIT $limit OFFSET $offset ";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("countItems" => $countItems, "data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen users.");
		}
	}
	// GET ALL ADMINS
	public function getAllAdmins($offset, $limit, $conn){
		//ENVIO COUNT TOTAL
		$sql1 	= "SELECT COUNT(*) FROM ".$this->model." WHERE role = 'ADMIN' AND deleted = 0";
		$datos1 	= $conn->query($sql1);
		if($datos1 == ""){
			$countItems = 0;
		} else {
			$countItems = $datos1[0]["COUNT(*)"];
		}

		$sql	="SELECT `id`, `name`, `email`, `image`, `useWhatsapp` FROM ".$this->model." WHERE role = 'ADMIN' AND deleted = 0 LIMIT $limit OFFSET $offset ";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("countItems" => $countItems, "data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen users.");
		}
	}
	// GET ALL CLIENTS
	public function getAllClients($offset, $limit, $name, $email, $conn){
		//ENVIO COUNT TOTAL
		$sql1 	= "SELECT COUNT(*) FROM ".$this->model." WHERE role = 'CLIENT'";
		$sqlName = "";
		$sqlEmail = "";
		$sqlFilter = "";
		if($name && $name!=""){
			$sqlName = " AND name LIKE '%".$name."%'";
			$sql1 = $sql1.$sqlName;
		}
		if($email && $email!=""){
			$sqlEmail = " AND email LIKE '%".$email."%'";
			$sql1 = $sql1.$sqlEmail;
		}

		$datos1 	= $conn->query($sql1);
		if($datos1 == ""){
			$countItems = 0;
		} else {
			$countItems = $datos1[0]["COUNT(*)"];
		}

		$sql	="SELECT `id`, `name`, `email`, `image`, `deleted`, `role`, `type` FROM ".$this->model." WHERE role = 'CLIENT'";
		$sqlName = "";
		$sqlEmail = "";
		if($name && $name!=""){
			$sqlName = " AND name LIKE '%".$name."%'";
			$sql = $sql.$sqlName;
		}
		if($email && $email!=""){
			$sqlEmail = " AND email LIKE '%".$email."%'";
			$sql = $sql.$sqlEmail;
		}
		$sql = $sql." LIMIT $limit OFFSET $offset ";
		//
		// $d = array("countItems" => $countItems,"sql" => $sql, "name" => $name, "email" => $email);
		// return $d;
		//
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("countItems" => $countItems, "data" => $d, "name" => $name, "email" => $email);
			return $d;
		} else {
			return array("error" => "Error: no existen users.");
		}
	}
	// GET INT CLIENTS
	public function getIntClients($offset, $limit, $conn){
		//ENVIO COUNT TOTAL
		$sql1 	= "SELECT COUNT(*) FROM ".$this->model;
		$datos1 	= $conn->query($sql1);
		if($datos1 == ""){
			$countItems = 0;
		} else {
			$countItems = $datos1[0]["COUNT(*)"];
		}

		$sql	="SELECT `id`, `name`, `email`, `image`, `deleted`, `role`, `type` FROM ".$this->model." WHERE role = 'CLIENT' AND type = 'INT' LIMIT $limit OFFSET $offset ";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("countItems" => $countItems, "data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen users.");
		}
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

	public function googleLogin($conn, $user){
		// CLEAR FIELDS
		$user["email"] 		= mysql_real_escape_string($user["email"]);
		$user["password"] 	= $this->cryptoPsw(mysql_real_escape_string($user["password"]));
		$sql	="SELECT `id`, `name`, `email`, `image`, `role`, `googleId`, `password` FROM ".$this->model." WHERE email='$user[email]' AND password = '$user[password]' AND deleted = 0 AND googleId = '$user[googleId]'";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			// SET TOKEN
			$d[0]["token"] =  $this->cryptoPsw($d[0]["password"].$d[0]["email"]);
			unset($d[0]["password"]);
			return array("response" => $d[0]);
		} else {
			return array("error" => "NO_GOOGLE_USER", "sql" =>$sql );
		}
	}

	public function facebookLogin($conn, $user){
		// CLEAR FIELDS
		$user["email"] 		= mysql_real_escape_string($user["email"]);
		$user["password"] 	= $this->cryptoPsw(mysql_real_escape_string($user["password"]));
		$sql	="SELECT `id`, `name`, `email`, `image`, `role`, `facebookId`, `password` FROM ".$this->model." WHERE email='$user[email]' AND password = '$user[password]' AND deleted = 0 AND facebookId = '$user[facebookId]'";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			// SET TOKEN
			$d[0]["token"] =  $this->cryptoPsw($d[0]["password"].$d[0]["email"]);
			unset($d[0]["password"]);
			return array("response" => $d[0]);
		} else {
			return array("error" => "NO_FACEBOOK_USER" );
		}
	}

	public function login($conn, $user){
		// CLEAR FIELDS
		$user["email"] 		= mysql_real_escape_string($user["email"]);
		$user["password"] 	= $this->cryptoPsw(mysql_real_escape_string($user["password"]));
		$sql	="SELECT `id`, `name`, `email`, `image`, `role`, `googleId`, `password` FROM ".$this->model." WHERE email='$user[email]' AND password = '$user[password]' AND deleted = 0";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			// SET TOKEN
			$d[0]["token"] =  $this->cryptoPsw($d[0]["password"].$d[0]["email"]);
			unset($d[0]["password"]);
			return array("response" => $d[0]);
		} else {
			return array("error" => "Error: email o clave incorrecta." );
		}
	}

	public function insertUser($conn,$user){
		//Hash password
		if (!empty($user["password"]) || $user["password"]!="" ) {
        	$user["password"]=$this->cryptoPsw($user["password"]);
    	}

    	$sqlExist =  "SELECT * FROM users WHERE email = '$user[email]'";
    	$dataExist = $conn->query($sqlExist);

    	if (empty($dataExist)) {
    		$md   	 = $this->model;
			$head 	 ="INSERT INTO ".$this->model;
			$insert .="(deleted, createdAt, ";
			$body 	.=" VALUES (0, CURRENT_TIMESTAMP,";
			$last 	 = count($user);

			$ind 	 = 1;
			foreach ($user as $key => $vle) {
				if($this->getStructure($conn,$key)){
					if($ind==$last){
						$insert .=$key;
						$body 	.="'".$vle."'";
					} else {
						$insert .=$key.", ";
						$body 	.="'".$vle."', ";
					}
				}
				$ind++;
			}

			$insert .=")";
			$body 	.=")";
			$sql 	 = $head.$insert.$body;
			$d 		 = $conn->query($sql);

			if(empty($d)){
				return array("response" => $d);
			} else {
				return array("error" => "Error: al ingresar el user.", "sql" => $sql);
			}
    	}else{
    		return array("error" => "Ya existe un usuario con ese email");
    	}


	}

	public function insertClient($conn,$user){
		//Email not NULL verification
		if ($user["email"]=="" || is_null($user["email"])) {
			return array("error" => "Email incorrecto");
		}

		if ($user["name"]=="" || is_null($user["name"])) {
			return array("error" => "Nombre incorrecto");
		}
		//Hash password
		if (!empty($user["password"]) || $user["password"]!="" ) {
        	$user["password"]=$this->cryptoPsw($user["password"]);
    	}

    	$sqlExist =  "SELECT * FROM users WHERE email = '$user[email]'";
    	$dataExist = $conn->query($sqlExist);

    	if (empty($dataExist)) {
    		$md   	 = $this->model;
			$head 	 ="INSERT INTO ".$this->model;
			$insert .="(deleted, createdAt, role, type,";
			$body 	.=" VALUES (0, CURRENT_TIMESTAMP, 'CLIENT', 'EXT', ";
			$last 	 = count($user);

			$ind 	 = 1;
			foreach ($user as $key => $vle) {
				if($this->getStructure($conn,$key)){
					if($ind==$last){
						$insert .=$key;
						$body 	.="'".$vle."'";
					} else {
						$insert .=$key.", ";
						$body 	.="'".$vle."', ";
					}
				}
				$ind++;
			}

			$insert .=")";
			$body 	.=")";
			$sql 	 = $head.$insert.$body;
			$d 		 = $conn->query($sql);

			if(empty($d)){
				return array("response" => $d);
				// $user["password"] 	= $this->login()
			} else {
				return array("error" => "Error: al ingresar el user.", "sql" => $sql);
			}
    	}else{
    		return array("error" => "Ya existe un usuario con ese email");
    	}


	}

	public function updateUser($conn, $user){

		// $sqlExist =  "SELECT * FROM users WHERE email = '$user[email]'";
  //   	$dataExist = $conn->query($sqlExist);

  //   	if (empty($dataExist)) {
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
		// }else{
  //   		return array("error" => "Ya existe un usuario con ese email");
  //   	}
	}

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

	public function getScheduleByAdmin($conn, $admin){
		$sql	="SELECT * FROM schedules WHERE adminId='$admin'";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			return $d;
		} else {
			return array("error" => "Error: no se encontraron horarios para este usuario.");
		}
	}

	public function getAllSchedulesByDays($conn, $initDay, $finalDay){
		//Generate an array with $initDay and $finalDay to create an SQL query with the days IN
		$days = [];
		if ($initDay <= $finalDay) {
			// Si inday es menor o igual a endday, simplemente genera un rango desde inday hasta endday
			for ($day = $initDay; $day <= $finalDay; $day++) {
				$days[] = $day;
			}
		} else {
			// Si inday es mayor que endday, genera dos rangos y los concatena:
			// desde inday hasta 6, y desde 0 hasta endday
			for ($day = $initDay; $day <= 6; $day++) {
				$days[] = $day;
			}
			for ($day = 0; $day <= $finalDay; $day++) {
				$days[] = $day;
			}
		}
		$days = implode("','", $days);
		$sql	="SELECT * FROM schedules WHERE day IN ('$days')";
		// $sql	="SELECT * FROM schedules WHERE day='$initDay' OR day='$finalDay'";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			return $d;
		} else {
			return array("error" => "Error: no se encontraron horarios para este usuario.");
		}
	}

	public function newScheduleByAdmin($conn, $item){
		$md   	 = "schedules";
		$sql 	 ="INSERT INTO schedules (adminId, day, hour) VALUES ('$item[adminId]', '$item[day]', '$item[hour]')";
		
		$d 	= $conn->query($sql);
		// CALLBACK
		if(empty($d)){
			return array("response" => $sql, "item" => $item);
		} else {
			return array("error" => "Error: al actualizar el schedule.", "sql" => $sql);
		}
	}
	public function deleteSchedule($conn, $id){
		$sql 	 ="DELETE FROM schedules WHERE id = ".$id;
		
		$d 	= $conn->query($sql);
		// CALLBACK
		if(empty($d)){
			return array("response" => $sql, "item" => $item);
		} else {
			return array("error" => "Error: al actualizar el schedule.", "sql" => $sql);
		}
	}
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
}

?>

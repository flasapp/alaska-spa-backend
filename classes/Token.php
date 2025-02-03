<?php
/**
*
*/
class Token {

	// CRYPTO FUNCTION
	public function cryptoPsw($psw){
		$key 	= "y0ur.k3y";
		$ps 	= strtoupper(sha1($psw.$key));
		return 	$ps;
	}

	public function checkToken($conn, $token){
		$sql = "SELECT pass, mail FROM usuarios";
		$d 	 = $conn->query($sql);
		$cnt = count($d);
		$find= false;

		for ($i=0; $i < $cnt; $i++) {
			if($token == $this->cryptoPsw($d[$i]["pass"].$d[$i]["mail"])){
				$find = true;
				$userId = $d[$i]["idUsuario"];
				// $userRole = $d[$i]["role"];
				break;
			}
		}

		return array("find_token" => $find, "userId" => $userId);
	}

}


?>

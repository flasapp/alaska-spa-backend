<?php

class Setting {

	private $model = "settings";

	// GET ALL USERES
	public function getNeigbourhoods($conn){
		//ENVIO COUNT TOTAL
		// $sql1 	= "SELECT COUNT(*) FROM ".$this->model." WHERE deleted = 0";
		// $datos1 	= $conn->query($sql1);
		// if($datos1 == ""){
		// 	$countItems = 0;
		// } else {
		// 	$countItems = $datos1[0]["COUNT(*)"];
		// };

		$sql	="SELECT * FROM barrios WHERE estado = 1 ORDER BY title;";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen Settingos en oferta.");
		}
	}
	public function getSettings($conn){
		$sql 	= "SELECT * FROM configuraciones WHERE id='1'";
		$datos 	= $conn->query($sql);
		if($datos == ""){
			$d = array("response" => "err: to get config by ID:");
			return $d;
		} else {
			return $datos[0];	
		}
	}
	
}

?>

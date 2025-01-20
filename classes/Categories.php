<?php

//
// EXAMPLE CLASS
//
require("config/curl.php");

class Category {

	private $model = "categories";

	// GET ALL USERES
	public function getAllCategories($conn){
		//ENVIO COUNT TOTAL
		// $sql1 	= "SELECT COUNT(*) FROM ".$this->model." WHERE deleted = 0";
		// $datos1 	= $conn->query($sql1);
		// if($datos1 == ""){
		// 	$countItems = 0;
		// } else {
		// 	$countItems = $datos1[0]["COUNT(*)"];
		// };

		$sql	="SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre;";
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

	
}

?>

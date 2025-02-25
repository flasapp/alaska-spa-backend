<?php

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
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen users.");
		}
	}

	public function getCategoryById($conn, $id){
		
		$sql	="SELECT * FROM categorias WHERE idCategoria = ".${id}.";";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existe la categoria.");
		}
	}

	
}

?>

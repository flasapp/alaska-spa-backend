<?php

//
// EXAMPLE CLASS
//
require("config/curl.php");

class Product {

	private $model = "categories";

	// GET ALL USERES
	public function getFeaturedProducts($conn){
		//ENVIO COUNT TOTAL
		// $sql1 	= "SELECT COUNT(*) FROM ".$this->model." WHERE deleted = 0";
		// $datos1 	= $conn->query($sql1);
		// if($datos1 == ""){
		// 	$countItems = 0;
		// } else {
		// 	$countItems = $datos1[0]["COUNT(*)"];
		// };

		$sql	="SELECT * FROM productos WHERE estado = 1 AND oferta = 1 ORDER BY productos.nombre;";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen productos en oferta.");
		}
	}

	public function getProductById($conn, $id){
		
		$sql	="SELECT productos.*, categorias.nombre AS nombre_categoria
				FROM productos
				JOIN categorias ON productos.idCategoria = categorias.idCategoria
				WHERE productos.estado = 1 AND productos.idProducto = ".${id}.";";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existe el producto.");
		}
	}

	public function getProductsByCategory($conn, $id){
		$sql	="SELECT productos.*, categorias.nombre AS nombre_categoria
				FROM productos
				JOIN categorias ON productos.idCategoria = categorias.idCategoria
				WHERE productos.estado = 1 AND productos.idCategoria = ".${id}." ORDER BY productos.nombre;";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen productos en esta categoria.");
		}
	}

	public function getProductsByName($conn, $name){
		$sql	="SELECT productos.*, categorias.nombre AS nombre_categoria
				FROM productos
				JOIN categorias ON productos.idCategoria = categorias.idCategoria
				WHERE productos.estado = 1 AND (productos.nombre LIKE '%".${name}."%' OR productos.descripcion LIKE '%".${name}."%') ORDER BY productos.nombre;";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			$d = array("data" => $d);
			return $d;
		} else {
			return array("error" => "Error: no existen productos con ese nombre.");
		}
	}
	
}

?>

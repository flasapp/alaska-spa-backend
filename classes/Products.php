<?php

class Product {

	private $model = "productos";

	// Get All Products
	public function getAll($conn, $params = []) {
		$limit 	= (isset($params["limit"])) ? $params["limit"] : 100;
		$offset = (isset($params["offset"])) ? $params["offset"] : 0;
		$whereBase = "WHERE 1=1";

		if(isset($params["where"]["name"]) && $params["where"]["name"] != ""){
			$search = $params["where"]["name"];
			$whereBase .= " AND (nombre LIKE '%$search%' OR descripcion LIKE '%$search%' OR descripcionLarga LIKE '%$search%')";
		}
		
		if(isset($params["where"]["categoryId"]) && $params["where"]["categoryId"] != ""){
			$whereBase .= " AND idCategoria = '".$params["where"]["categoryId"]."'";
		}

		if(isset($params["where"]["status"]) && $params["where"]["status"] != ""){
			$whereBase .= " AND estado = '".$params["where"]["status"]."'";
		}

		// Count Items
		$sqlCount = "SELECT COUNT(DISTINCT idProducto) as total FROM ".$this->model." ".$whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		// Get Items
		$sql = "SELECT DISTINCT
					idProducto as id, 
					nombre as name, 
					descripcion as description, 
					precio as price, 
					estado as status, 
					oferta as offer, 
					stock as stock, 
					descripcionLarga as longDescription, 
					idCategoria as categoryId, 
					foto as image 
				FROM ".$this->model." ".$whereBase." ORDER BY nombre ASC LIMIT $offset, $limit";
		
		$d 	 = $conn->query($sql);
		if ($d == "") {
			$d = array();
		}
		
		return array("data" => $d, "count" => (int)$total);
	}

	// Get Product by ID
	public function getById($conn, $id){
		$sql = "SELECT 
					idProducto as id, 
					nombre as name, 
					descripcion as description, 
					precio as price, 
					estado as status, 
					oferta as offer, 
					stock as stock, 
					descripcionLarga as longDescription, 
					idCategoria as categoryId, 
					foto as image 
				FROM ".$this->model." WHERE idProducto='$id'";
		
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			return $d[0];
		} else {
			return array("error" => "Error: no se encuentra el producto.");
		}
	}

	// Create Product
	public function create($conn, $data){
		// Check required fields
		if (empty($data['name']) || empty($data['categoryId'])) {
			return array("error" => "Faltan datos obligatorios (name, categoryId)", "code" => "MISSING_DATA");
		}

		$name = $data['name'];
		$categoryId = $data['categoryId'];
		$description = isset($data['description']) ? $data['description'] : '';
		$price = isset($data['price']) ? $data['price'] : 0;
		$status = isset($data['status']) ? $data['status'] : 1;
		$offer = isset($data['offer']) ? $data['offer'] : 0;
		$stock = isset($data['stock']) ? $data['stock'] : 0;
		$longDescription = isset($data['longDescription']) ? $data['longDescription'] : '';
		$image = isset($data['image']) ? $data['image'] : '';
		$userId = isset($data['userId']) ? $data['userId'] : 0;

		$sql 	= "INSERT INTO ".$this->model." 
					(idCategoria, nombre, descripcion, precio, estado, oferta, stock, descripcionLarga, foto, modifyAt, modifyBy)
					VALUES 
					('$categoryId', '$name', '$description', '$price', '$status', '$offer', '$stock', '$longDescription', '$image', NOW(), '$userId')";
		
		$d 	= $conn->query($sql);
		if($d == ""){
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al crear el producto", "sql" => $sql);
		}
	}

	// Update Product
	public function update($conn, $id, $data){
		$updates = [];

		if(isset($data['name'])) $updates[] = "nombre = '".$data['name']."'";
		if(isset($data['description'])) $updates[] = "descripcion = '".$data['description']."'";
		if(isset($data['price'])) $updates[] = "precio = '".$data['price']."'";
		if(isset($data['status'])) $updates[] = "estado = '".$data['status']."'";
		if(isset($data['offer'])) $updates[] = "oferta = '".$data['offer']."'";
		if(isset($data['stock'])) $updates[] = "stock = '".$data['stock']."'";
		if(isset($data['longDescription'])) $updates[] = "descripcionLarga = '".$data['longDescription']."'";
		if(isset($data['categoryId'])) $updates[] = "idCategoria = '".$data['categoryId']."'";
		if(isset($data['image'])) $updates[] = "foto = '".$data['image']."'";
		if(isset($data['userId'])) $updates[] = "modifyBy = '".$data['userId']."'";
		
		$updates[] = "modifyAt = NOW()";

		if (count($updates) <= 1) { // Only modifyAt
			return array("response" => "OK", "message" => "No fields to update", "success" => true);
		}

		$sql = "UPDATE ".$this->model." SET " . implode(', ', $updates) . " WHERE idProducto = '$id'";
		
		$d = $conn->query($sql);
		
		if(empty($d)){
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al actualizar el producto.", "sql" => $sql);
		}
	}

	// Delete Product (Soft Delete)
	public function delete($conn, $id){
		$sql = "UPDATE ".$this->model." SET estado = 0 WHERE idProducto='$id'";
		$d 	= $conn->query($sql);
		
		// CALLBACK
		if(empty($d)){
			return array("response" => 'OK', "success" => true);
		} else {
			return array("error" => "Error: al eliminar el producto.", "sql" => $sql);
		}
	}

	// Get Products by Category for Frontend (Active only)
	public function getByCategory($conn, $categoryId, $params = []) {
		$limit 	= (isset($params["limit"])) ? $params["limit"] : 100;
		$offset = (isset($params["offset"])) ? $params["offset"] : 0;
		
		// Forced filters: category and active status
		$whereBase = "WHERE idCategoria = '$categoryId' AND estado = 1";

		if(isset($params["where"]["name"]) && $params["where"]["name"] != ""){
			$search = $params["where"]["name"];
			$whereBase .= " AND (nombre LIKE '%$search%' OR descripcion LIKE '%$search%' OR descripcionLarga LIKE '%$search%')";
		}

		// Count Items
		$sqlCount = "SELECT COUNT(DISTINCT idProducto) as total FROM ".$this->model." ".$whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		// Get Items
		$sql = "SELECT DISTINCT
					idProducto as id, 
					nombre as name, 
					descripcion as description, 
					precio as price, 
					estado as status, 
					oferta as offer, 
					stock as stock, 
					descripcionLarga as longDescription, 
					idCategoria as categoryId, 
					foto as image 
				FROM ".$this->model." ".$whereBase." ORDER BY nombre ASC LIMIT $offset, $limit";
		
		$d 	 = $conn->query($sql);
		if ($d == "") {
			$d = array();
		}
		
		return array("data" => $d, "count" => (int)$total);
	}

	// Get Featured Products for Frontend (Active & Offer only)
	public function getFeatured($conn, $params = []) {
		$limit 	= (isset($params["limit"])) ? $params["limit"] : 100;
		$offset = (isset($params["offset"])) ? $params["offset"] : 0;
		
		// Forced filters: featured and active status
		$whereBase = "WHERE oferta = 1 AND estado = 1";

		if(isset($params["where"]["name"]) && $params["where"]["name"] != ""){
			$search = $params["where"]["name"];
			$whereBase .= " AND (nombre LIKE '%$search%' OR descripcion LIKE '%$search%' OR descripcionLarga LIKE '%$search%')";
		}

		// Count Items
		$sqlCount = "SELECT COUNT(DISTINCT idProducto) as total FROM ".$this->model." ".$whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		// Get Items
		$sql = "SELECT DISTINCT
					idProducto as id, 
					nombre as name, 
					descripcion as description, 
					precio as price, 
					estado as status, 
					oferta as offer, 
					stock as stock, 
					descripcionLarga as longDescription, 
					idCategoria as categoryId, 
					foto as image 
				FROM ".$this->model." ".$whereBase." ORDER BY nombre ASC LIMIT $offset, $limit";
		
		$d 	 = $conn->query($sql);
		if ($d == "") {
			$d = array();
		}
		
		return array("data" => $d, "count" => (int)$total);
	}
}
?>

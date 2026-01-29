<?php

class Representant {

	private $model = "representantes";

	// Get All Representatives
	public function getAll($conn, $params = array()){
		$limit 	= (isset($params["limit"])) ? (int)$params["limit"] : 100;
		$offset = (isset($params["offset"])) ? (int)$params["offset"] : 0;
		$whereBase = "WHERE 1=1";

		if(isset($params["where"]["name"]) && $params["where"]["name"] != ""){
			$whereBase .= " AND nombre LIKE '%".$params["where"]["name"]."%'";
		}
		
		// Count Items
		$sqlCount = "SELECT COUNT(*) as total FROM ".$this->model." ".$whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		$sql 	= "SELECT idRepresentante as id, nombre as name, precio as price, estado as status FROM ".$this->model." ".$whereBase." LIMIT $offset, $limit";
		$d 		= $conn->query($sql);
		
		if(empty($d)){
			$d = array();
		} 

		return array("data" => $d, "count" => (int)$total);
	}

	// Get Representative by ID
	public function getById($conn, $id){
		$sql	= "SELECT idRepresentante as id, nombre as name, precio as price, estado as status FROM ".$this->model." WHERE idRepresentante='$id'";
		$d 		= $conn->query($sql);

		if(!empty($d)){
			$representant = $d[0];
			
			// Get associated products
			$sql_prods = "SELECT idProducto as id, nombre as name, descripcion as description, precio as price, foto as image FROM productos WHERE idRepresentante = '$id'";
			$prods 	= $conn->query($sql_prods);
			
			$representant["products"] = (!empty($prods)) ? $prods : array();
			return $representant;
		} else {
			return array("error" => "Error: representante no encontrado.");
		}
	}

	// Create Representative
	public function create($conn, $data){
		// Check required fields
		if (empty($data['name']) || !isset($data['price'])) {
			return array("error" => "Faltan datos obligatorios (name, price)", "code" => "MISSING_DATA");
		}

		$name = $data['name'];
		$price = $data['price'];
		$status = isset($data['status']) ? $data['status'] : 1;

		$sql 	= "INSERT INTO ".$this->model." 
					(nombre, precio, estado)
					VALUES 
					('$name', '$price', '$status')";
		
		$d 	= $conn->query($sql);
		
		if(empty($d)){
			$idLast = mysql_insert_id();

			// Assign products if provided
			if(isset($data['products']) && is_array($data['products'])){
				$this->assignProducts($conn, $data['products'], $idLast, $price);
			}

			return array("response" => "OK", "id" => $idLast, "success" => true);
		} else {
			return array("error" => "Error al crear el representante", "sql" => $sql);
		}
	}

	// Update Representative
	public function update($conn, $id, $data){
		$updates = array();

		if(isset($data['name'])) $updates[] = "nombre = '".$data['name']."'";
		if(isset($data['price'])) $updates[] = "precio = '".$data['price']."'";
		if(isset($data['status'])) $updates[] = "estado = '".$data['status']."'";

		// If nothing to update on the rep itself, check if we just want to update/assign products
		if (empty($updates) && (!isset($data['products']) || !is_array($data['products']))) {
			return array("response" => "OK", "message" => "No fields to update", "success" => true);
		}

		if(!empty($updates)){
			$sql = "UPDATE ".$this->model." SET " . implode(', ', $updates) . " WHERE idRepresentante = '$id'";
			$d = $conn->query($sql);
			if(!empty($d)){
				return array("error" => "Error al actualizar el representante.", "sql" => $sql);
			}
		}

		// Update/Assign products logic
		// If products array is sent -> Assign specific products and set price
		if(isset($data['products']) && is_array($data['products'])){
			// Use the new price if provided, otherwise retrieve current price? 
			// Attempting to use provided price, if not, use 0 or fetch from DB? 
			// Usually price is sent with update. If not, we might need to fetch it.
			// Optimistically assuming price is sent or we use provided value. 
			// To be safe, if price is not in data, we should fetch it.
			$price = isset($data['price']) ? $data['price'] : $this->getPriceById($conn, $id);
			$this->assignProducts($conn, $data['products'], $id, $price);
		} 
		// Fallback: If no products array but price changed, update OLD associated products (original logic)
		else if(isset($data['price'])){
			$this->updateProductsPrice($conn, $data['price'], $id);
		}

		// Logic to remove products
		if(isset($data['products_remove']) && is_array($data['products_remove'])){
			$this->removeProducts($conn, $data['products_remove']);
		}
		
		return array("response" => "OK", "success" => true);
	}

	// Helper to get price (needed if update doesnt send price but sends products)
	private function getPriceById($conn, $id){
		$sql = "SELECT precio FROM ".$this->model." WHERE idRepresentante='$id'";
		$d = $conn->query($sql);
		if(!empty($d) && isset($d[0]['precio'])){
			return $d[0]['precio'];
		}
		return 0;
	}

	// Remove products from rep
	private function removeProducts($conn, $products){
		$ids = array();
		foreach($products as $prod){
			$ids[] = is_array($prod) ? $prod['id'] : $prod;
		}

		if(!empty($ids)){
			$idsStr = implode(",", $ids);
			$sql = "UPDATE productos SET idRepresentante = NULL WHERE idProducto IN ($idsStr)";
			$conn->query($sql);
		}
	}

	// Assign list of products to rep and update price
	private function assignProducts($conn, $products, $repId, $repPrice){
		$ids = array();
		foreach($products as $prod){
			$ids[] = is_array($prod) ? $prod['id'] : $prod;
		}

		if(!empty($ids)){
			$idsStr = implode(",", $ids);
			$sql = "UPDATE productos SET precio = '$repPrice', idRepresentante = '$repId' WHERE idProducto IN ($idsStr)";
			$conn->query($sql);
		}
	}

	// Update Products Price (Legacy/Fallback)
	private function updateProductsPrice($conn, $price, $representantId){
		$sql = "UPDATE productos SET precio = '$price' WHERE idRepresentante = '$representantId' AND idRepresentante != 0";
		$conn->query($sql);
	}

	// Delete Representative (Soft Delete)
	public function delete($conn, $id){
		$sql = "UPDATE ".$this->model." SET estado = 0 WHERE idRepresentante='$id'";
		$d 	= $conn->query($sql);
		
		if(empty($d)){
			return array("response" => 'OK', "success" => true);
		} else {
			return array("error" => "Error: al eliminar el representante.", "sql" => $sql);
		}
	}

}
?>

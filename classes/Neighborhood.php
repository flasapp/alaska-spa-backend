<?php
require dirname(__DIR__) . '/config/env.php';

class Neighborhood {
	private $model = "barrios";

	// Get All Neighborhoods
	public function getAll($conn, $params) {
		$limit 	= (isset($params["limit"])) ? $params["limit"] : 10;
		$offset = (isset($params["offset"])) ? $params["offset"] : 0;
		$whereBase = "WHERE 1=1";

		if(isset($params["where"]["name"]) && $params["where"]["name"] != ""){
			$whereBase .= " AND title LIKE '%".$params["where"]["name"]."%'";
		}

		// Count Items
		$sqlCount = "SELECT COUNT(*) as total FROM ".$this->model." ".$whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		// Get Items
		$sql = "SELECT idBarrio as id, title as name, monto as amount, estado as status FROM ".$this->model." ".$whereBase." LIMIT $offset, $limit";
		$d 	 = $conn->query($sql);
		if ($d == "") {
			$d = array();
		}
		
		return array("data" => $d, "count" => $total);
	}

	// Get Neighborhood by ID
	public function getById($conn, $id){
		$sql	= "SELECT idBarrio as id, title as name, monto as amount, estado as status FROM ".$this->model." WHERE idBarrio='$id'";
		$d 		= $conn->query($sql);

		// CALLBACK
		if(!empty($d)){
			return $d[0];
		} else {
			return array("error" => "Error: no se encuentra el barrio.");
		}
	}

	// Create Neighborhood
	public function create($conn, $data){
		// Check required fields
		if (empty($data['name'])) {
			return array("error" => "Faltan datos obligatorios (name)", "code" => "MISSING_DATA");
		}

		$name = $data['name'];
		$amount = isset($data['amount']) ? $data['amount'] : 0;
		$status = isset($data['status']) ? $data['status'] : 1;

		$sql 	= "INSERT INTO ".$this->model." 
					(title, monto, estado)
					VALUES 
					('$name', '$amount', '$status')";
		
		$d 	= $conn->query($sql);
		if($d == ""){
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al crear el barrio", "sql" => $sql);
		}
	}

	// Update Neighborhood
	public function update($conn, $id, $data){
		$updates = [];

		if(isset($data['name'])) $updates[] = "title = '".$data['name']."'";
		if(isset($data['amount'])) $updates[] = "monto = '".$data['amount']."'";
		if(isset($data['status'])) $updates[] = "estado = '".$data['status']."'";

		if (empty($updates)) {
			return array("response" => "OK", "message" => "No fields to update", "success" => true);
		}

		$sql = "UPDATE ".$this->model." SET " . implode(', ', $updates) . " WHERE idBarrio = '$id'";
		
		$d = $conn->query($sql);
		
		if(empty($d)){
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al actualizar el barrio.", "sql" => $sql);
		}
	}

	// Delete Neighborhood (Soft Delete)
	public function delete($conn, $id){
		$sql = "UPDATE ".$this->model." SET estado = 0 WHERE idBarrio='$id'";
		$d 	= $conn->query($sql);
		
		// CALLBACK
		if(empty($d)){
			return array("response" => 'OK', "success" => true);
		} else {
			return array("error" => "Error: al eliminar el barrio.", "sql" => $sql);
		}
	}
}
?>

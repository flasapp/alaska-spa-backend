<?php

class Category
{

	private $model = "categorias";

	// Get All Categories
	public function getAll($conn, $params = array())
	{
		$limit = (isset($params["limit"])) ? (int) $params["limit"] : 100;
		$offset = (isset($params["offset"])) ? (int) $params["offset"] : 0;
		$whereBase = "WHERE 1=1";

		if (isset($params["where"]["name"]) && $params["where"]["name"] != "") {
			$whereBase .= " AND nombre LIKE '%" . $params["where"]["name"] . "%'";
		}

		if (isset($params["where"]["status"]) && $params["where"]["status"] != "") {
			$whereBase .= " AND estado = '" . $params["where"]["status"] . "'";
		}

		if (isset($params["where"]["favorite"]) && $params["where"]["favorite"] != "") {
			$whereBase .= " AND fav = '" . $params["where"]["favorite"] . "'";
		}

		// Count Items
		$sqlCount = "SELECT COUNT(*) as total FROM " . $this->model . " " . $whereBase;
		$countData = $conn->query($sqlCount);
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		// Get Items
		$sql = "SELECT idCategoria as id, nombre as name, descripcion as description, imagen as image, fav as favorite, estado as status, orden as `order` FROM " . $this->model . " " . $whereBase . " ORDER BY nombre ASC LIMIT $offset, $limit";
		$d = $conn->query($sql);
		if ($d == "") {
			$d = array();
		}

		return array("data" => $d, "count" => (int) $total);
	}

	// Get Category by ID
	public function getById($conn, $id)
	{
		$sql = "SELECT idCategoria as id, nombre as name, descripcion as description, imagen as image, fav as favorite, estado as status, orden as `order` FROM " . $this->model . " WHERE idCategoria='$id'";
		$d = $conn->query($sql);

		// CALLBACK
		if (!empty($d)) {
			return $d[0];
		} else {
			return array("error" => "Error: no se encuentra la categoria.");
		}
	}

	// Create Category
	public function create($conn, $data)
	{
		// Check required fields
		if (empty($data['name'])) {
			return array("error" => "Faltan datos obligatorios (name)", "code" => "MISSING_DATA");
		}

		$name = $data['name'];
		$description = isset($data['description']) ? $data['description'] : '';
		$image = isset($data['image']) ? $data['image'] : '';
		$favorite = isset($data['favorite']) ? (int) $data['favorite'] : 0;
		$status = isset($data['status']) ? $data['status'] : 1;
		$orden = isset($data['order']) ? (int) $data['order'] : 0;

		$sql = "INSERT INTO " . $this->model . " 
					(nombre, descripcion, imagen, fav, estado, orden)
					VALUES 
					('$name', '$description', '$image', '$favorite', '$status', '$orden')";

		$d = $conn->query($sql);
		if ($d == "") {
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al crear la categoria", "sql" => $sql);
		}
	}

	// Update Category
	public function update($conn, $id, $data)
	{
		$updates = array();

		if (isset($data['name']))
			$updates[] = "nombre = '" . $data['name'] . "'";
		if (isset($data['description']))
			$updates[] = "descripcion = '" . $data['description'] . "'";
		if (isset($data['image']))
			$updates[] = "imagen = '" . $data['image'] . "'";
		if (isset($data['favorite']))
			$updates[] = "fav = '" . (int) $data['favorite'] . "'";
		if (isset($data['status']))
			$updates[] = "estado = '" . $data['status'] . "'";
		if (isset($data['order']))
			$updates[] = "orden = '" . (int) $data['order'] . "'";

		if (empty($updates)) {
			return array("response" => "OK", "message" => "No fields to update", "success" => true);
		}

		$sql = "UPDATE " . $this->model . " SET " . implode(', ', $updates) . " WHERE idCategoria = '$id'";

		$d = $conn->query($sql);

		if (empty($d)) {
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al actualizar la categoria.", "sql" => $sql);
		}
	}

	// Delete Category (Soft Delete)
	public function delete($conn, $id)
	{
		$sql = "UPDATE " . $this->model . " SET estado = 0 WHERE idCategoria='$id'";
		$d = $conn->query($sql);

		// CALLBACK
		if (empty($d)) {
			return array("response" => 'OK', "success" => true);
		} else {
			return array("error" => "Error: al eliminar la categoria.", "sql" => $sql);
		}
	}
}
?>
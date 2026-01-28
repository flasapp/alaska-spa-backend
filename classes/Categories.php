<?php

class Category {

	private $model = "categorias";

	/**
	 * Get all active categories
	 * 
	 * @param PDO $conn Database connection
	 * @return array Categories data or error
	 */
	public function getAllCategories(PDO $conn): array {
		try {
			$sql = "SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre";
			$stmt = $conn->prepare($sql);
			$stmt->execute();
			$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			if (!empty($categories)) {
				return ["data" => $categories];
			} else {
				return ["error" => "No categories found"];
			}
		} catch (PDOException $e) {
			throw new Exception("Database error: " . $e->getMessage());
		}
	}

	/**
	 * Get category by ID
	 * 
	 * @param PDO $conn Database connection
	 * @param int $id Category ID
	 * @return array Category data or error
	 */
	public function getCategoryById(PDO $conn, int $id): array {
		try {
			$sql = "SELECT * FROM categorias WHERE idCategoria = :id";
			$stmt = $conn->prepare($sql);
			$stmt->execute(['id' => $id]);
			$category = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($category) {
				return ["data" => $category];
			} else {
				return ["error" => "Category not found"];
			}
		} catch (PDOException $e) {
			throw new Exception("Database error: " . $e->getMessage());
		}
	}
}

?>

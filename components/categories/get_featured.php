<?php
// INCLUDE CLASS
require("classes/Categories.php");

$conn = new Connection();
$objCategory = new Category();

$response = $objCategory->getFavorites($conn);

echo json_encode($response);
?>
<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

try {
  
  if ($data["category_id"]) {
    $stmt = $conn->prepare(
      "SELECT * FROM products WHERE category_id = ?"
    );
    $stmt->execute([$data["category_id"]]);
  } else {
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
  }
  
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  sendResponse(200, "Products retrieved successfully", $products);
  
} catch (Exception $e) {
  sendResponse(500, "Failed to retrieve products");
}
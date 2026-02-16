<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";


// Get pagination params from query string
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn);

try {


  if (isset($data['category_id'])) {
    $check = $conn->prepare("SELECT id FROM categories WHERE id=?");
    $check->execute([$data['category_id']]);
    if ($check->rowCount() === 0) {
      sendResponse(404, "Category not found", null, ["category_id" => "Not found"]);
    }
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $data['category_id'], PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
  }

  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  sendResponse(200, "Products retrieved successfully", $products);
} catch (Exception $e) {
  sendResponse(500, "Failed to retrieve products", null, ["exception" => $e->getMessage()]);
}

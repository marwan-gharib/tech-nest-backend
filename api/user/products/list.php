<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

// Pagination
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$category_id = $_GET['category_id'] ?? null;
$search      = $_GET['search'] ?? null;
$min_price   = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price   = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$status      = $_GET['status'] ?? null;

// Sorting
$sort  = $_GET['sort'] ?? null;
$order = strtoupper($_GET['order'] ?? 'ASC');

try {

  $where = [];
  $params = [];

  if ($category_id) {
    $where[] = "category_id = ?";
    $params[] = $category_id;
  }

  if ($search) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
  }

  if ($min_price !== null) {
    $where[] = "price >= ?";
    $params[] = $min_price;
  }

  if ($max_price !== null) {
    $where[] = "price <= ?";
    $params[] = $max_price;
  }

  if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
  }

  $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

  // Safe sorting
  $allowedSort = ['name', 'price'];
  $allowedOrder = ['ASC', 'DESC'];

  $sort_sql = "";
  if ($sort && in_array($sort, $allowedSort) && in_array($order, $allowedOrder)) {
    $sort_sql = "ORDER BY LOWER($sort) $order";
  }

  // Main Query
  $sql = "SELECT * FROM products $where_sql $sort_sql LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);

  $allParams = array_merge($params, [$limit, $offset]);

  foreach ($allParams as $index => $param) {
    $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($index + 1, $param, $type);
  }

  $stmt->execute();
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Count Query
  $count_sql = "SELECT COUNT(*) FROM products $where_sql";
  $count_stmt = $conn->prepare($count_sql);

  foreach ($params as $index => $param) {
    $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $count_stmt->bindValue($index + 1, $param, $type);
  }

  $count_stmt->execute();
  $total = $count_stmt->fetchColumn();

  $response = [
    "products" => $products,
    "pagination" => [
      "total" => (int)$total,
      "page"  => $page,
      "limit" => $limit,
      "pages" => ceil($total / $limit)
    ]
  ];

  sendResponse(200, "Products retrieved successfully", $response);
} catch (Exception $e) {
  sendResponse(500, "Failed to retrieve products", null, [
    "error" => $e->getMessage()
  ]);
}

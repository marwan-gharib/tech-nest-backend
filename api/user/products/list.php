<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

// ================= Pagination =================
$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0)
  ? (int)$_GET['limit']
  : 10;

$page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0)
  ? (int)$_GET['page']
  : 1;

$offset = ($page - 1) * $limit;

// ================= Filters =================
$category_id = (isset($_GET['category_id']) && is_numeric($_GET['category_id']) && $_GET['category_id'] > 0)
  ? (int)$_GET['category_id']
  : null;

$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$min_price = (isset($_GET['min_price']) && is_numeric($_GET['min_price']) && $_GET['min_price'] >= 0)
  ? (float)$_GET['min_price']
  : null;

$max_price = (isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] >= 0)
  ? (float)$_GET['max_price']
  : null;

// ================= Sorting =================
$sort  = $_GET['sort'] ?? 'name';
$order = strtoupper($_GET['order'] ?? 'ASC');

$allowedSort  = ['name', 'price'];
$allowedOrder = ['ASC', 'DESC'];

// Fallback to defaults if provided values are invalid
if (!in_array($sort, $allowedSort)) {
  $sort = 'name';
}
if (!in_array($order, $allowedOrder)) {
  $order = 'ASC';
}

// ================= Validation =================
if ($min_price !== null && $max_price !== null && $min_price > $max_price) {
  sendResponse(400, "Invalid price range", null, [
    "min_price" => "Must be less than max_price"
  ]);
}

try {

  $where = [];
  $params = [];

  // ===== category filter =====
  if ($category_id !== null) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
  }

  // ===== search filter =====
  if ($search !== null && $search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
  }

  // ===== price filter (optimized) =====
  if ($min_price !== null && $max_price !== null) {
    $where[] = "p.price BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
  } else {
    if ($min_price !== null) {
      $where[] = "p.price >= ?";
      $params[] = $min_price;
    }

    if ($max_price !== null) {
      $where[] = "p.price <= ?";
      $params[] = $max_price;
    }
  }

  $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

  // ===== sorting =====
  if ($sort === 'name') {
    $sort_sql = "ORDER BY LOWER(p.name) $order";
  } else {
    $sort_sql = "ORDER BY p.$sort $order";
  }

  // ================= Main Query =================
  $sql = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where_sql $sort_sql LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);

  $allParams = array_merge($params, [$limit, $offset]);

  foreach ($allParams as $index => $param) {
    if (is_int($param)) {
      $stmt->bindValue($index + 1, $param, PDO::PARAM_INT);
    } else {
      $stmt->bindValue($index + 1, $param);
    }
  }

  $stmt->execute();
  $products_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Format products to include nested category
  $products = array_map(function($product) {
    $category_id = $product['category_id'] !== null ? (int)$product['category_id'] : null;
    $category_name = $product['category_name'];

    unset($product['category_id']);
    unset($product['category_name']);

    $product['category'] = [
      "id" => $category_id,
      "name" => $category_name,
      "image_url" => ""
    ];

    return $product;
  }, $products_data);

  // ================= Count Query =================
  $count_sql = "SELECT COUNT(*) FROM products p $where_sql";
  $count_stmt = $conn->prepare($count_sql);

  foreach ($params as $index => $param) {
    if (is_int($param)) {
      $count_stmt->bindValue($index + 1, $param, PDO::PARAM_INT);
    } else {
      $count_stmt->bindValue($index + 1, $param);
    }
  }

  $count_stmt->execute();
  $total = $count_stmt->fetchColumn();

  // ================= Response =================
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
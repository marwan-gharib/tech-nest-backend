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
$category_ids = [];
if (isset($_GET['category_id'])) {
    if (is_array($_GET['category_id'])) {
        foreach ($_GET['category_id'] as $val) {
            if (is_numeric($val) && $val > 0) $category_ids[] = (int)$val;
        }
    } else {
        $val = trim($_GET['category_id']);
        if ($val !== 'null' && $val !== '') {
            $parts = explode(',', $val);
            foreach ($parts as $part) {
                if (is_numeric($part) && $part > 0) $category_ids[] = (int)$part;
            }
        }
    }
}
$category_ids = array_unique($category_ids);

$search = (isset($_GET['search']) && $_GET['search'] !== 'null') ? trim($_GET['search']) : null;

$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== 'null' && is_numeric($_GET['min_price']) && $_GET['min_price'] >= 0)
  ? (float)$_GET['min_price']
  : null;

$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== 'null' && is_numeric($_GET['max_price']) && $_GET['max_price'] >= 0)
  ? (float)$_GET['max_price']
  : null;

$status = (isset($_GET['status']) && $_GET['status'] !== 'null') ? trim($_GET['status']) : null;

// ================= Sorting =================
$sort  = (isset($_GET['sort']) && $_GET['sort'] !== 'null') ? $_GET['sort'] : null;
$order = (isset($_GET['order']) && $_GET['order'] !== 'null') ? strtoupper($_GET['order']) : 'ASC';

$allowedSort  = ['name', 'price', 'created_at', 'id'];
$allowedOrder = ['ASC', 'DESC'];

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
  if (!empty($category_ids)) {
    if (count($category_ids) === 1) {
      $where[] = "p.category_id = ?";
      $params[] = $category_ids[0];
    } else {
      $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
      $where[] = "p.category_id IN ($placeholders)";
      foreach ($category_ids as $id) {
        $params[] = $id;
      }
    }
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

  // ===== status filter =====
  if ($status !== null && $status !== '') {
    $where[] = "p.status = ?";
    $params[] = $status;
  }

  $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

  // ===== sorting =====
  $sort_sql = "";
  if ($sort && in_array($sort, $allowedSort) && in_array($order, $allowedOrder)) {
    if ($sort === 'name') {
      $sort_sql = "ORDER BY LOWER(p.name) $order";
    } else {
      $sort_sql = "ORDER BY p.$sort $order";
    }
  } else {
    $sort_sql = "ORDER BY p.id DESC";
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

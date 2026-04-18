<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

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

if (!in_array($sort, $allowedSort))   $sort  = 'name';
if (!in_array($order, $allowedOrder)) $order = 'ASC';

// ================= Validation =================
if ($min_price !== null && $max_price !== null && $min_price > $max_price) {
  sendResponse(400, t('invalid_price_range'));
}

try {

  $where  = [];
  $params = [];

  // category filter
  if ($category_id !== null) {
    $where[]  = "p.category_id = ?";
    $params[] = $category_id;
  }

  // search filter — searches both English and Arabic names
  if ($search !== null && $search !== '') {
    $where[]  = "(p.name LIKE ? OR p.description LIKE ? OR pt.name LIKE ? OR pt.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
  }

  // price filter
  if ($min_price !== null && $max_price !== null) {
    $where[]  = "p.price BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
  } else {
    if ($min_price !== null) { $where[] = "p.price >= ?"; $params[] = $min_price; }
    if ($max_price !== null) { $where[] = "p.price <= ?"; $params[] = $max_price; }
  }

  $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

  // sorting on localised name
  if ($sort === 'name') {
    $sort_sql = "ORDER BY LOWER(COALESCE(pt.name, p.name)) $order";
  } else {
    $sort_sql = "ORDER BY p.$sort $order";
  }

  // ================= Main Query =================
  $sql = "SELECT
              p.*,
              COALESCE(pt.name, p.name)               AS name,
              COALESCE(pt.description, p.description)  AS description,
              c.name                                    AS category_name_en,
              COALESCE(ct.name, c.name)                AS category_name
          FROM products p
          LEFT JOIN products_translations   pt ON pt.product_id  = p.id  AND pt.lang = ?
          LEFT JOIN categories              c  ON c.id            = p.category_id
          LEFT JOIN categories_translations ct ON ct.category_id = c.id  AND ct.lang = ?
          $where_sql $sort_sql LIMIT ? OFFSET ?";

  $stmt = $conn->prepare($sql);

  // First two params are the two lang bindings, then filters, then limit/offset
  $allParams = array_merge([$lang, $lang], $params, [$limit, $offset]);

  foreach ($allParams as $index => $param) {
    if (is_int($param)) {
      $stmt->bindValue($index + 1, $param, PDO::PARAM_INT);
    } else {
      $stmt->bindValue($index + 1, $param);
    }
  }

  $stmt->execute();
  $products_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Format: nest category, strip raw columns
  $products = array_map(function($product) {
    $cat_id   = $product['category_id'] !== null ? (int)$product['category_id'] : null;
    $cat_name = $product['category_name'];

    unset($product['category_id'], $product['category_name'], $product['category_name_en']);

    $product['category'] = [
      "id"        => $cat_id,
      "name"      => $cat_name,
      "image_url" => ""
    ];

    return $product;
  }, $products_data);

  // ================= Count Query =================
  $count_sql = "SELECT COUNT(*)
                FROM products p
                LEFT JOIN products_translations pt ON pt.product_id = p.id AND pt.lang = ?
                $where_sql";
  $count_stmt = $conn->prepare($count_sql);

  $countParams = array_merge([$lang], $params);
  foreach ($countParams as $index => $param) {
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
    "products"   => $products,
    "pagination" => [
      "total" => (int)$total,
      "page"  => $page,
      "limit" => $limit,
      "pages" => ceil($total / $limit)
    ]
  ];

  sendResponse(200, t('products_retrieved'), $response);
} catch (Exception $e) {
  sendResponse(500, t('products_failed'));
}
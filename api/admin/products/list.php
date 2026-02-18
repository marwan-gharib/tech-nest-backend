<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

// Pagination
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$search      = isset($_GET['search']) ? trim($_GET['search']) : null;

$admin = validateAdminToken($conn);

try {
    $where = [];
    $params = [];

    // Filter by category
    if ($category_id) {
        $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $check->execute([$category_id]);
        if (!$check->fetch()) {
            sendResponse(404, "Category not found", null);
        }

        $where[] = "category_id = ?";
        $params[] = $category_id;
    }

    // Filter by search
    if ($search) {
        $where[] = "(name LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT * FROM products $where_sql LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    $allParams = array_merge($params, [$limit, $offset]);
    foreach ($allParams as $index => $param) {
        $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($index + 1, $param, $type);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    sendResponse(500, "Failed to retrieve products", null, ["error" => $e->getMessage()]);
}

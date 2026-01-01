<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode([
        "status" => 400,
        "message" => "Category name is required",
        "data" => null
    ]);
    exit;
}

// Prevent duplicate name (case-insensitive) excluding current id
$name = trim($data['name']);
$category_id = intval($data['id']);
$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name)=LOWER(?) AND id<>? LIMIT 1");
$dup->execute([$name, $category_id]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(409);
    echo json_encode([
        "status" => 409,
        "message" => "Category already exists",
        "data" => null
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->execute([$name, $category_id]);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Category updated successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Failed to update category"
    ]);
}

<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

if (empty($data['name'])) {
    echo json_encode([
        "status" => false,
        "message" => "Category name is required",
        "data" => null
    ]);
    exit;
}

// Prevent duplicate name (case-insensitive) excluding current id
$name = trim($data['name']);
$category_id = intval($data['category_id']);
$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name)=LOWER(?) AND id<>? LIMIT 1");
$dup->execute([$name, $category_id]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        "status" => false,
        "message" => "Category already exists",
        "data" => null
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->execute([$name, $category_id]);

    echo json_encode([
        "status" => true,
        "message" => "Category updated successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to update category",
        "error" => $e->getMessage()
    ]);
}

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

try {
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->execute([$data['name'], $data['category_id']]);

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

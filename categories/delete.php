<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

$stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
try {
    $stmt->execute([$data['category_id']]);

    echo json_encode([
        "status" => true,
        "message" => "Category deleted successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to delete category",
        "error" => $e->getMessage()
    ]);
}

<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

checkAdmin($conn, $data['user_id']);

$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
try {
    $stmt->execute([$data['product_id']]);

    echo json_encode([
        "status" => true,
        "message" => "Product deleted successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to delete product",
        "error" => $e->getMessage()
    ]);
}

<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
try {
    $stmt->execute([$data['id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

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

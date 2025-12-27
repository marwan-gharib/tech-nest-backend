<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);

$stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
try {
    $stmt->execute([$data['cart_id'], $user['id']]);

    echo json_encode([
        "status" => true,
        "message" => "Item removed from cart successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to remove item from cart",
        "error" => $e->getMessage()
    ]);
}

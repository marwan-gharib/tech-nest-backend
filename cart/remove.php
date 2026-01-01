<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);

$stmt = $conn->prepare(
    "DELETE FROM cart WHERE id = ? AND user_id = ?"
);

try {
    $stmt->execute([$data['id'], $user['id']]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            "status" => 404,
            "message" => "Item not found in cart"
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Item removed from cart successfully",
        "data" => null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Failed to remove item from cart"
    ]);
}

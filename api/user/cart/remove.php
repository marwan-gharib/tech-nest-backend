<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);

$stmt = $conn->prepare(
    "DELETE FROM cart WHERE id = ? AND user_id = ?"
);

try {
    $stmt->execute([$data['id'], $user['id']]);

    if ($stmt->rowCount() === 0) {
        sendResponse(404, "Item not found in cart");
    }

    sendResponse(200, "Item removed from cart successfully");

} catch (Exception $e) {
    sendResponse(500, "Failed to remove item from cart");
}

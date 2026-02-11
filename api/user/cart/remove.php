<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn);

$stmt = $conn->prepare(
    "DELETE FROM cart WHERE id = ? AND user_id = ?"
);

try {
    $stmt->execute([$data['id'], $user['id']]);

    if ($stmt->rowCount() === 0) {
        sendResponse(404, "Item not found in cart", null, ["cart_item" => "Not found"]);
    }

    sendResponse(200, "Item removed from cart successfully", ["id" => $data['id']]);
} catch (Exception $e) {
    sendResponse(500, "Failed to remove item from cart", null, ["exception" => $e->getMessage()]);
}

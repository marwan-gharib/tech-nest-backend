<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
    sendResponse(400, "Quantity must be a positive number");
}

$user = validateToken($conn, $data['token'] ?? null);

$cartStmt = $conn->prepare(
    "SELECT c.id, p.stock 
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.id = ? AND c.user_id = ?
     LIMIT 1"
);
$cartStmt->execute([$data['id'], $user['id']]);
$item = $cartStmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    sendResponse(404, "Cart item not found");
}

if ((int)$data['quantity'] > (int)$item['stock']) {
    sendResponse(400, "Only {$item['stock']} items available");
}

try {
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->execute([$data['quantity'], $data['id']]);

    sendResponse(200, "Cart updated successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to update cart");
}

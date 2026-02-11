<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
    sendResponse(400, "Quantity must be a positive number", null, ["quantity" => "Must be a positive number"]);
}

$user = validateToken($conn);

$productStmt = $conn->prepare(
    "SELECT stock FROM products WHERE id = ? LIMIT 1"
);
$productStmt->execute([$data['product_id']]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    sendResponse(404, "Product not found", null, ["product_id" => "Not found"]);
}

$cartStmt = $conn->prepare(
    "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1"
);
$cartStmt->execute([$user['id'], $data['product_id']]);
$existing = $cartStmt->fetch(PDO::FETCH_ASSOC);

$requestedQty = (int)$data['quantity'];
$currentQty   = $existing ? (int)$existing['quantity'] : 0;
$totalQty     = $currentQty + $requestedQty;

if ($totalQty > (int)$product['stock']) {
    sendResponse(400, "Only {$product['stock']} items available", null, ["stock" => "Insufficient stock"]);
}

if ($existing) {
    $update = $conn->prepare(
        "UPDATE cart SET quantity = ? WHERE id = ?"
    );
    $update->execute([$totalQty, $existing['id']]);

    sendResponse(200, "Cart updated successfully", [
        "id" => $existing['id'],
        "product_id" => $data['product_id'],
        "quantity" => $totalQty
    ]);
} else {
    $insert = $conn->prepare(
        "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)"
    );
    $insert->execute([$user['id'], $data['product_id'], $requestedQty]);
    $cart_id = $conn->lastInsertId();

    sendResponse(201, "Item added to cart", [
        "id" => $cart_id,
        "product_id" => $data['product_id'],
        "quantity" => $requestedQty
    ]);
}

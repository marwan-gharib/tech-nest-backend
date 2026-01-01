<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Quantity must be a positive number"
    ]);
    exit;
}

$user = validateToken($conn, $data['token'] ?? null);

$productStmt = $conn->prepare(
    "SELECT stock FROM products WHERE id = ? LIMIT 1"
);
$productStmt->execute([$data['product_id']]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode([
        "status" => false,
        "message" => "Product not found"
    ]);
    exit;
}

if ((int)$data['quantity'] > (int)$product['stock']) {
    echo json_encode([
        "status" => false,
        "message" => "Only {$product['stock']} items available"
    ]);
    exit;
}

$cartStmt = $conn->prepare(
    "SELECT id FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1"
);
$cartStmt->execute([$user['id'], $data['product_id']]);
$cart = $cartStmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    echo json_encode([
        "status" => false,
        "message" => "Item not found in cart"
    ]);
    exit;
}

$update = $conn->prepare(
    "UPDATE cart SET quantity = ? WHERE id = ?"
);
$update->execute([
    (int)$data['quantity'],
    $cart['id']
]);

echo json_encode([
    "status" => true,
    "message" => "Cart quantity updated successfully"
]);

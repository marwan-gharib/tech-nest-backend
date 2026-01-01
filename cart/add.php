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

$cartStmt = $conn->prepare(
    "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1"
);
$cartStmt->execute([$user['id'], $data['product_id']]);
$existing = $cartStmt->fetch(PDO::FETCH_ASSOC);

$requestedQty = (int)$data['quantity'];
$currentQty   = $existing ? (int)$existing['quantity'] : 0;
$totalQty     = $currentQty + $requestedQty;

if ($totalQty > (int)$product['stock']) {
    echo json_encode([
        "status" => false,
        "message" => "Only {$product['stock']} items available"
    ]);
    exit;
}

if ($existing) {
    $update = $conn->prepare(
        "UPDATE cart SET quantity = ? WHERE id = ?"
    );
    $update->execute([$totalQty, $existing['id']]);
    if ($update->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to update cart item"
        ]);
        exit;
    }
} else {
    $insert = $conn->prepare(
        "INSERT INTO cart (user_id, product_id, quantity)
         VALUES (?, ?, ?)"
    );
    $insert->execute([
        $user['id'],
        $data['product_id'],
        $requestedQty
    ]);
    if ($insert->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to insert cart item"
        ]);
        exit;
    }
}

echo json_encode([
    "status" => true,
    "message" => "Item added to cart successfully"
]);

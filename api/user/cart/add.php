<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
    sendResponse(400, "Quantity must be a positive number", null, ["quantity" => "Must be a positive number"]);
}

$productStmt = $conn->prepare(
    "SELECT p.*, c.name as category_name, c.image_url as category_image 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.id = ? 
     LIMIT 1"
);
$productStmt->execute([$data['product_id']]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    sendResponse(404, "Product not found", null, ["product_id" => "Not found"]);
}

// Format product to include nested category
$product['category'] = [
    "id" => $product['category_id'],
    "name" => $product['category_name'],
    "image_url" => $product['category_image']
];
unset($product['category_name']);
unset($product['category_image']);

$cartStmt = $conn->prepare(
    "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1"
);
$cartStmt->execute([$user['id'], $data['product_id']]);
$existing = $cartStmt->fetch(PDO::FETCH_ASSOC);

$requestedQty = (int)$data['quantity'];
$totalQty     = $requestedQty;

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
        "quantity" => $totalQty,
        "product" => $product
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
        "quantity" => $requestedQty,
        "product" => $product
    ]);
}

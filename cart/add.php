<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Quantity must be a positive number",
        "data" => null
    ]);
    exit;
}

$user = validateToken($conn, $data['token']);

$stmt = $conn->prepare(
    "INSERT INTO cart (user_id,product_id,quantity)
     VALUES (?,?,?)"
);

try {
    $stmt->execute([
        $data['user_id'],
        $data['product_id'],
        $data['quantity']
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Item added to cart successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to add item to cart",
        "error" => $e->getMessage()
    ]);
}

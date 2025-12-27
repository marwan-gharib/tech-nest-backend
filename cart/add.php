<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare(
    "INSERT INTO cart (user_id,product_id,quantity)
     VALUES (?,?,?)"
);

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

<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

checkAdmin($conn, $data['user_id']);

$stmt = $conn->prepare(
    "UPDATE products
     SET name=?, description=?, price=?
     WHERE id=?"
);

$stmt->execute([
    $data['name'],
    $data['description'],
    $data['price'],
    $data['product_id']
]);

echo json_encode([
    "status" => true,
    "message" => "Product updated successfully",
    "data" => null
]);

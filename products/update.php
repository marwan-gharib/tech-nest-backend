<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

checkAdmin($conn, $data['user_id']);

if (empty($data['name']) || empty($data['description']) || !is_numeric($data['price'])) {
    echo json_encode([
        "status" => false,
        "message" => "Name, description, and valid price are required",
        "data" => null
    ]);
    exit;
}

try {
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
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to update product",
        "error" => $e->getMessage()
    ]);
}

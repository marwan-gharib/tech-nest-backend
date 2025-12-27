<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

checkAdmin($conn, $data['user_id']);

$stmt = $conn->prepare(
    "INSERT INTO products (`name`,`description`,price,category_id,image_url)
     VALUES (?,?,?,?,?)"
);

try {
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['category_id'],
        $data['image_url']
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Product added successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to add product",
        "error" => $e->getMessage()
    ]);
}

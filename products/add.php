<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

if (!empty($data['img'])) {
    $image_name = uniqid() . ".png";
    $image_path = "uploads/" . $image_name;
    file_put_contents($image_path, base64_decode($data['img']));
    $data['image_url'] = $image_path;
} else {
    $data['image_url'] = null;
}

$stmt = $conn->prepare(
    "INSERT INTO products (`name`, `description`, price, stock, category_id, image_url)
     VALUES (?, ?, ?, ?, ?, ?)"
);

try {
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['stock'],
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

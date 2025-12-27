<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

if (empty($data['name']) || empty($data['description']) || !is_numeric($data['price'])) {
    echo json_encode([
        "status" => false,
        "message" => "Name, description, and valid price are required",
        "data" => null
    ]);
    exit;
}

if (!empty($data['img'])) {
    $image_name = uniqid() . ".png";
    $image_path = "uploads/" . $image_name;
    file_put_contents($image_path, base64_decode($data['img']));
    $data['image_url'] = $image_path;
} else {
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id=?");
    $stmt->execute([$data['id']]);
    $data['image_url'] = $stmt->fetchColumn();
}

try {
    $stmt = $conn->prepare(
        "UPDATE products
         SET `name`=?, `description`=?, price=?, stock=?, image_url=?
         WHERE id=?"
    );
    $stmt->execute([
        $data['name'],
        $data['description'],
        $data['price'],
        $data['stock'],
        $data['image_url'],
        $data['id']
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

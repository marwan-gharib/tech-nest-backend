<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);
checkAdmin($conn, $user['id']);

$name = trim($data['name']);
if ($name === '') {
    http_response_code(400);
    echo json_encode([
        "status" => 400,
        "message" => "Category name is required"
    ]);
    exit;
}

$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
$dup->execute([$name]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(409);
    echo json_encode([
        "status" => 409,
        "message" => "Category already exists"
    ]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO categories (`name`) VALUES (?)");
try {
    $stmt->execute([$name]);

    http_response_code(201);
    echo json_encode([
        "status" => 201,
        "message" => "Category added successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Failed to add category"
    ]);
}

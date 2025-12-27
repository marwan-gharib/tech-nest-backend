<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

$name = trim($data['name']);

// Prevent duplicate category names (case-insensitive)
$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
$dup->execute([$name]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        "status" => false,
        "message" => "Category already exists",
        "data" => null
    ]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO categories (`name`) VALUES (?)");
try {
    $stmt->execute([$name]);

    echo json_encode([
        "status" => true,
        "message" => "Category added successfully",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to add category",
        "error" => $e->getMessage()
    ]);
}

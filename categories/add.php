<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token']);
checkAdmin($conn, $user['id']);

$stmt = $conn->prepare("INSERT INTO categories (`name`) VALUES (?)");
try {
    $stmt->execute([$data['name']]);

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

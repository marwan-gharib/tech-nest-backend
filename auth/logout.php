<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);

try {
    $stmt = $conn->prepare("UPDATE users SET token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Logout successful",
        "data" => null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Failed to logout"
    ]);
}
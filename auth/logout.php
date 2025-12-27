<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);

try {
    $stmt = $conn->prepare("UPDATE users SET token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    echo json_encode([
        "status" => true,
        "message" => "Logout successful",
        "data" => null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to logout",
        "error" => $e->getMessage()
    ]);
}
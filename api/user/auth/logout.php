<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);

try {
    $stmt = $conn->prepare("UPDATE users SET token = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    sendResponse(200, "Logout successful");
} catch (Exception $e) {
    sendResponse(500, "Failed to logout");
}

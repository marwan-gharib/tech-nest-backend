<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn, $data['token'] ?? null);

try {
    $stmt = $conn->prepare("UPDATE admins SET token = NULL WHERE id = ?");
    $stmt->execute([$admin['id']]);

    sendResponse(200, "Logout successful");
} catch (Exception $e) {
    sendResponse(500, "Failed to logout");
}

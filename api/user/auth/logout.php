<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

try {
    $stmt = $conn->prepare("UPDATE users SET token = NULL, token_expiry = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    sendResponse(200, "Logout successful");
} catch (Exception $e) {
    sendResponse(500, "Failed to logout");
}

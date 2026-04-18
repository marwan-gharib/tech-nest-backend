<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

try {
    $stmt = $conn->prepare("UPDATE users SET token = NULL, token_expiry = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);

    sendResponse(200, t('logout_success'));
} catch (Exception $e) {
    sendResponse(500, t('logout_failed'));
}

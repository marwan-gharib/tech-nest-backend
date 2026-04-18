<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

try {
    $stmt = $conn->prepare("UPDATE admins SET token = NULL, token_expiry = NULL WHERE id = ?");
    $stmt->execute([$admin['id']]);

    sendResponse(200, t('logout_success'));
} catch (Exception $e) {
    sendResponse(500, t('logout_failed'));
}

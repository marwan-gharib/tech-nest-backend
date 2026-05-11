<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, t('method_not_allowed'));
}

$user = validateToken($conn);

$input = json_decode(file_get_contents("php://input"), true);
$fcm_token = $input['fcm_token'] ?? null;

if (!$fcm_token) {
    sendResponse(400, t('fcm_token_required'));
}

try {
    $stmt = $conn->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
    $stmt->execute([$fcm_token, $user['id']]);

    sendResponse(200, t('fcm_token_saved'));
} catch (PDOException $e) {
    sendResponse(500, t('database_error'));
}

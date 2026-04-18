<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['email'])) {
    sendResponse(400, t('email_required'));
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, t('invalid_email'));
}

$stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendResponse(404, t('email_not_found'));
}

if ($user['is_verified'] == 0) {
    sendResponse(403, t('email_not_verified'));
}

$verification_code = rand(100000, 999999);

try {
    $stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?");
    $stmt->execute([$verification_code, $user['id']]);

    sendForgotPasswordEmail($data['email'], $verification_code);

    sendResponse(200, t('code_sent'), ["email" => $data['email']]);
} catch (Exception $e) {
    sendResponse(500, t('reset_request_failed'));
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$lang = getRequestedLang();

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['email']) ||
    empty($data['verification_code']) ||
    empty($data['new_password'])
) {
    sendResponse(400, t('all_fields_required'));
}

$stmt = $conn->prepare("SELECT id, is_verified, fcm_token FROM users WHERE email = ? AND verification_code = ? AND code_expires_at >= NOW()");
$stmt->execute([$data['email'], $data['verification_code']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendResponse(400, t('invalid_or_expired_code'));
}

if ($user['is_verified'] == 0) {
    sendResponse(403, t('email_not_verified'));
}

$newHashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);

try {
    $update = $conn->prepare("UPDATE users SET `password` = ?, verification_code = NULL, code_expires_at = NULL WHERE id = ?");
    $update->execute([$newHashedPassword, $user['id']]);

    // --- FCM NOTIFICATION ---
    try {
        require_once "../../../helpers/FCMService.php";
        $fcm = new FCMService($conn);

        $fcm->sendNotification(
            [
                'i18n' => [
                    'lang' => $lang,
                    'title_key' => 'notif_security_alert_title',
                    'body_key'  => 'notif_security_alert_body',
                    'args'      => [],
                ],
                'data' => [
                    'type'   => 'RESET_PASSWORD',
                    'route'  => '/login',
                    'entity' => ['type' => 'RESET_PASSWORD', 'id' => (int)$user['id']],
                    'extra'  => [],
                ],
            ],
            ['type' => 'single', 'user_ids' => [(int)$user['id']]]
        );
    } catch (Exception $e) {
        // Silently ignore notification failure — password is already reset
    }
    // --- END FCM NOTIFICATION ---

    sendResponse(200, t('password_reset_success'), ["user_id" => $user['id']]);
} catch (Exception $e) {
    sendResponse(500, t('password_update_failed'));
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, t('method_not_allowed'));
}

$user = validateToken($conn);

$input = json_decode(file_get_contents("php://input"), true);
$notification_id = $input['notification_id'] ?? null;

try {
    if ($notification_id) {
        // Mark specific notification
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user['id']]);
    } else {
        // Mark all as read
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);
    }

    sendResponse(200, t('notifications_marked_read'));
} catch (PDOException $e) {
    sendResponse(500, t('database_error'));
}

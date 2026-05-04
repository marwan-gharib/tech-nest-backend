<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, t('method_not_allowed'));
}

$user = validateToken($conn);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;

$offset = ($page - 1) * $limit;

try {
    // Get total count for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
    $countStmt->execute([$user['id']]);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get notifications
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as &$notif) {
        if ($notif['data']) {
            $notif['data'] = json_decode($notif['data'], true);
        }
    }

    sendResponse(200, "Notifications fetched successfully", [
        'notifications' => $notifications,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
} catch (PDOException $e) {
    sendResponse(500, t('database_error' . $e->getMessage()));
}

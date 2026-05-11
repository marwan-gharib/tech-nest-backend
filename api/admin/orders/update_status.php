<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);
$lang = getRequestedLang();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    sendResponse(400, t('invalid_input'));
}

$order_id = (int)$_GET['id'];
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['status'])) {
    sendResponse(400, t('all_fields_required'));
}

$status = $data['status'];
$valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

if (!in_array($status, $valid_statuses)) {
    sendResponse(400, t('invalid_order_status'));
}

try {
    // 1. Fetch order and user details
    $orderStmt = $conn->prepare("
        SELECT o.user_id, u.fcm_token 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $orderStmt->execute([$order_id]);
    $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderData) {
        sendResponse(404, t('order_not_found'));
    }

    // 2. Update status
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->execute([$status, $order_id]);

    // --- FCM NOTIFICATION ---
    try {
        require_once "../../../helpers/FCMService.php";
        $fcm = new FCMService($conn);

        $statusLabels = [
            'pending'   => t('pending'),
            'confirmed' => t('confirmed'),
            'shipped'   => t('shipped'),
            'delivered' => t('delivered'),
            'cancelled' => t('cancelled'),
        ];
        $statusLabel = $statusLabels[$status] ?? ucfirst($status);

        $fcm->sendNotification(
            [
                'notification' => [
                    'title' => "Order #$order_id — $statusLabel",
                    'body'  => "Your order status has been updated to: $status.",
                ],
                'data' => [
                    'type'   => 'ORDER',
                    'entity' => ['type' => 'ORDER', 'id' => $order_id],
                    'extra'  => ['status' => $status],
                ],
            ],
            ['type' => 'single', 'user_ids' => [$orderData['user_id']]]
        );
    } catch (Exception $e) {
        // Silently ignore notification failure — order update is already committed
    }
    // --- END FCM NOTIFICATION ---

    sendResponse(200, t('order_status_updated'), [
        "order_id" => $order_id,
        "status" => $status
    ]);

} catch (Exception $e) {
    sendResponse(500, t('database_error'));
}

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

    // --- NEW FCM LOGIC ---
    try {
        if (!empty($orderData['fcm_token'])) {
            require_once "../../../helpers/FCMService.php";
            $fcm = new FCMService($conn);
            $fcm->sendToUser($orderData['user_id'], $orderData['fcm_token'], [
                'title' => 'Order Status Updated',
                'body' => "Your order #$order_id is now $status.",
                'type' => 'order',
                'data' => [
                    'order_id' => (string)$order_id,
                    'status' => $status
                ]
            ]);
        }
    } catch (Exception $e) {
        // Silently ignore notification failure
    }
    // --- END FCM LOGIC ---

    sendResponse(200, t('order_status_updated'), [
        "order_id" => $order_id,
        "status" => $status
    ]);

} catch (Exception $e) {
    sendResponse(500, t('database_error'));
}

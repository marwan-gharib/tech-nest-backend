<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";
include "../../../helpers/FCMService.php";

// This file serves as an example of how to trigger notifications from your backend events
// In a real application, you would call these functions within your order/cart logic.

$admin = validateAdminToken($conn);
$fcm = new FCMService($conn);

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'order_update':
        $userId = $_GET['user_id'] ?? null;
        $orderId = $_GET['order_id'] ?? '12345';
        $status = $_GET['status'] ?? 'Shipped';

        if (!$userId) sendResponse(400, "user_id is required");

        // 1. Get user's FCM token
        $stmt = $conn->prepare("SELECT fcm_token FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['fcm_token'])) {
            $payload = [
                'title' => "Order #$orderId Update",
                'body' => "Your order status has been updated to: $status",
                'type' => 'order_update',
                'data' => [
                    'order_id' => $orderId,
                    'status' => $status
                ]
            ];
            $result = $fcm->sendToUser($userId, $user['fcm_token'], $payload);
            sendResponse(200, "Order notification sent", $result);
        } else {
            sendResponse(404, "User or FCM token not found");
        }
        break;

    case 'cart_reminder':
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) sendResponse(400, "user_id is required");

        $stmt = $conn->prepare("SELECT fcm_token FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['fcm_token'])) {
            $payload = [
                'title' => "Complete Your Purchase! 🛒",
                'body' => "You still have items in your cart. Checkout now before they're gone!",
                'type' => 'cart_reminder',
                'data' => [
                    'screen' => 'cart'
                ]
            ];
            $result = $fcm->sendToUser($userId, $user['fcm_token'], $payload);
            sendResponse(200, "Cart reminder sent", $result);
        }
        break;

    case 'promo_topic':
        $topic = $_GET['topic'] ?? 'all_users';
        $payload = [
            'title' => "Flash Sale! ⚡",
            'body' => "Get 50% OFF on all electronics today only!",
            'type' => 'promo',
            'data' => [
                'promo_code' => 'TECH50',
                'category' => 'electronics'
            ]
        ];
        $result = $fcm->sendToTopic($topic, $payload);
        sendResponse(200, "Topic notification sent", $result);
        break;

    default:
        sendResponse(400, "Invalid action. Use: order_update, cart_reminder, or promo_topic");
}

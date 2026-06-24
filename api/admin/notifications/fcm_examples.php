<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";
include "../../../helpers/FCMService.php";

// This file serves as an example of how to trigger notifications from your backend events
// Using the new standardized payload and unified targeting system.

$admin = validateAdminToken($conn);
$fcm = new FCMService($conn);
$lang = getRequestedLang();

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'order_update':
        $userId = $_GET['user_id'] ?? null;
        $orderId = $_GET['order_id'] ?? '12345';
        $status = $_GET['status'] ?? 'Shipped';
        $statusKey = strtolower((string)$status);

        if (!$userId) sendResponse(400, t('user_id_required'));

        $payload = [
            "i18n" => [
                'lang' => 'auto',
                'title_key' => 'notif_order_status_updated_title',
                'body_key'  => 'notif_order_status_updated_body',
                'args'      => ['order_id' => (int)$orderId, 'status_key' => $statusKey],
            ],
            "data" => [
                "type" => "ORDER_UPDATE",
                "route" => "/order/" . $orderId,
                "entity" => [
                    "type" => "order",
                    "id" => (int)$orderId
                ],
                "extra" => [
                    "status" => $status
                ]
            ]
        ];

        $target = [
            "type" => "single",
            "user_ids" => [(int)$userId]
        ];

        $result = $fcm->sendNotification($payload, $target);
        sendResponse(200, t('order_notification_sent'), $result);
        break;

    case 'new_product':
        $productId = $_GET['product_id'] ?? 10;
        
        $payload = [
            "i18n" => [
                'lang' => $lang,
                'title_key' => 'notif_new_product_title',
                'body_key'  => 'notif_new_product_body',
                'args'      => [],
            ],
            "data" => [
                "type" => "NEW_PRODUCT",
                "route" => "/product/" . $productId,
                "entity" => [
                    "type" => "product",
                    "id" => (int)$productId
                ],
                "extra" => []
            ]
        ];

        $target = [
            "type" => "all"
        ];

        $result = $fcm->sendNotification($payload, $target);
        sendResponse(200, t('broadcast_notification_sent'), $result);
        break;

    case 'promo_multiple':
        $userIds = isset($_GET['user_ids']) ? explode(',', $_GET['user_ids']) : [1, 2];

        $payload = [
            "i18n" => [
                'lang' => $lang,
                'title_key' => 'notif_promo_title',
                'body_key'  => 'notif_promo_body',
                'args'      => [],
            ],
            "data" => [
                "type" => "PROMO",
                "route" => "/offers",
                "entity" => [
                    "type" => "promotion",
                    "id" => 0
                ],
                "extra" => [
                    "discount" => "20%"
                ]
            ]
        ];

        $target = [
            "type" => "multiple",
            "user_ids" => array_map('intval', $userIds)
        ];

        $result = $fcm->sendNotification($payload, $target);
        sendResponse(200, t('multiple_users_notification_sent'), $result);
        break;

    default:
        sendResponse(400, t('invalid_action'));
}

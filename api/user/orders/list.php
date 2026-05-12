<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

try {
    $stmt = $conn->prepare("
        SELECT id, total_price, status, created_at, updated_at
        FROM orders 
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $status = $order['status'] ?? '';
        $order['status_label'] = t((string)$status, $lang);
    }
    unset($order);

    sendResponse(200, t('orders_retrieved'), [
        "orders" => $orders
    ]);

} catch (Exception $e) {
    sendResponse(500, t('orders_failed'));
}

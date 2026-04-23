<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    sendResponse(400, t('invalid_input'));
}

$order_id = (int)$_GET['id'];

try {
    // 1. Fetch Order details
    $orderStmt = $conn->prepare("
        SELECT id, total_price, status, shipping_address, billing_address, created_at, updated_at
        FROM orders
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");
    $orderStmt->execute([$order_id, $user['id']]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        sendResponse(404, t('order_not_found'));
    }

    $name_col = ($lang === 'ar') ? "COALESCE(pt.name, p.name)" : "p.name";

    // 2. Fetch Order Items with product translations
    $itemsStmt = $conn->prepare("
        SELECT 
            oi.id as order_item_id, oi.quantity, oi.price_at_purchase as price,
            p.id as product_id, 
            $name_col as name, 
            p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN products_translations pt ON pt.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$order_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $order['items'] = $items;

    sendResponse(200, t('orders_retrieved'), [
        "order" => $order
    ]);

} catch (Exception $e) {
    sendResponse(500, t('database_error'));
}

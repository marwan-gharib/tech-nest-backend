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
    $conn->beginTransaction();

    // 1. Fetch order to verify it belongs to user and is pending
    $orderStmt = $conn->prepare("
        SELECT id, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
        FOR UPDATE
    ");
    $orderStmt->execute([$order_id, $user['id']]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $conn->rollBack();
        sendResponse(404, t('order_not_found'));
    }

    if ($order['status'] !== 'pending') {
        $conn->rollBack();
        sendResponse(400, t('order_cancel_failed'));
    }

    // 2. Fetch order items to restore stock
    $itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$order_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    foreach ($items as $item) {
        $updateStock->execute([$item['quantity'], $item['product_id']]);
    }

    // 3. Mark as cancelled
    $cancelStmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $cancelStmt->execute([$order_id]);

    $conn->commit();

    sendResponse(200, t('order_cancelled'), [
        "order_id" => $order_id,
        "status" => "cancelled"
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    sendResponse(500, t('database_error'));
}

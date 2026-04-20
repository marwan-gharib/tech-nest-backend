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
    // 1. Fetch order to verify existence
    $orderStmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $orderStmt->execute([$order_id]);
    if (!$orderStmt->fetch()) {
        sendResponse(404, t('order_not_found'));
    }

    // 2. Update status
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->execute([$status, $order_id]);

    sendResponse(200, t('order_status_updated'), [
        "order_id" => $order_id,
        "status" => $status
    ]);

} catch (Exception $e) {
    sendResponse(500, t('database_error'));
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (
    !isset($data['id']) || !is_numeric($data['id']) ||
    !isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0
) {
    sendResponse(400, t('quantity_invalid'));
}

$cartId       = (int)$data['id'];
$requestedQty = (int)$data['quantity'];

try {
    $cartStmt = $conn->prepare(
        "SELECT c.id, c.quantity AS current_quantity, p.stock
         FROM cart c
         JOIN products p ON c.product_id = p.id
         WHERE c.id = ? AND c.user_id = ?
         LIMIT 1"
    );
    $cartStmt->execute([$cartId, $user['id']]);
    $item = $cartStmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        sendResponse(404, t('cart_item_not_found'));
    }

    $stock = (int)$item['stock'];

    if ($stock <= 0) {
        $deleteStmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $deleteStmt->execute([$cartId]);

        sendResponse(200, t('cart_item_removed_oos'), [
            "id"       => $cartId,
            "quantity" => 0,
            "status"   => "removed"
        ]);
    }

    $finalQty = $requestedQty;
    $status   = "updated";

    if ($requestedQty > $stock) {
        $finalQty = $stock;
        $status   = "adjusted";
    }

    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->execute([$finalQty, $cartId]);

    sendResponse(200, t('cart_updated'), [
        "id"               => $cartId,
        "quantity"         => $finalQty,
        "requested_quantity" => $requestedQty,
        "available_stock"  => $stock,
        "status"           => $status
    ]);

} catch (Exception $e) {
    sendResponse(500, t('cart_update_failed'));
}
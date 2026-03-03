<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

$data = json_decode(file_get_contents("php://input"), true);

// ✅ Validate input
if (
    !isset($data['id']) || !is_numeric($data['id']) ||
    !isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0
) {
    sendResponse(400, "Invalid input", null, [
        "id" => "Required",
        "quantity" => "Must be a positive number"
    ]);
}

$cartId = (int)$data['id'];
$requestedQty = (int)$data['quantity'];

try {

    // 🔍 Fetch cart item + stock
    $cartStmt = $conn->prepare(
        "SELECT c.id, c.quantity as current_quantity, p.stock 
         FROM cart c
         JOIN products p ON c.product_id = p.id
         WHERE c.id = ? AND c.user_id = ?
         LIMIT 1"
    );
    $cartStmt->execute([$cartId, $user['id']]);
    $item = $cartStmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        sendResponse(404, "Cart item not found", null, ["cart_item" => "Not found"]);
    }

    $stock = (int)$item['stock'];

    // ❌ لو المنتج out of stock → احذفه
    if ($stock <= 0) {
        $deleteStmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $deleteStmt->execute([$cartId]);

        sendResponse(200, "Item removed (out of stock)", [
            "id" => $cartId,
            "quantity" => 0,
            "status" => "removed"
        ]);
    }

    $finalQty = $requestedQty;
    $status = "updated";

    if ($requestedQty > $stock) {
        $finalQty = $stock;
        $status = "adjusted";
    }

    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->execute([$finalQty, $cartId]);

    sendResponse(200, "Cart updated successfully", [
        "id" => $cartId,
        "quantity" => $finalQty,
        "requested_quantity" => $requestedQty,
        "available_stock" => $stock,
        "status" => $status
    ]);

} catch (Exception $e) {
    sendResponse(500, "Failed to update cart", null, [
        "exception" => $e->getMessage()
    ]);
}
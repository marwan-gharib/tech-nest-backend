<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['shipping_address']) || empty($data['billing_address'])) {
    sendResponse(400, t('addresses_required'));
}

$shipping_address = trim($data['shipping_address']);
$billing_address = trim($data['billing_address']);

try {
    $conn->beginTransaction();

    // 1. Fetch user's cart
    $stmt = $conn->prepare("
        SELECT c.id as cart_id, c.quantity, p.id as product_id, p.price, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        $conn->rollBack();
        sendResponse(400, t('cart_empty'));
    }

    $totalPrice = 0;
    foreach ($cartItems as $item) {
        if ((int)$item['quantity'] > (int)$item['stock']) {
            $conn->rollBack();
            sendResponse(400, t('insufficient_stock'));
        }
        $totalPrice += ((int)$item['quantity'] * (float)$item['price']);
    }

    // Include delivery charges if total is below 2000
    $deliveryCharges = ($totalPrice == 0 || $totalPrice > 2000) ? 0 : 50;
    $grandTotal = $totalPrice + $deliveryCharges;

    // 2. Create Order
    $insertOrder = $conn->prepare("
        INSERT INTO orders (user_id, total_price, status, shipping_address, billing_address) 
        VALUES (?, ?, 'pending', ?, ?)
    ");
    $insertOrder->execute([$user['id'], $grandTotal, $shipping_address, $billing_address]);
    $order_id = $conn->lastInsertId();

    // 3. Create Order Items & Deduct Stock
    $insertItem = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
        VALUES (?, ?, ?, ?)
    ");
    $updateStock = $conn->prepare("
        UPDATE products SET stock = stock - ? WHERE id = ?
    ");

    foreach ($cartItems as $item) {
        $insertItem->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);

        $updateStock->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }

    // 4. Clear Cart
    $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCart->execute([$user['id']]);

    $conn->commit();

    // --- NEW FCM LOGIC ---
    try {
        if (!empty($user['fcm_token'])) {
            require_once "../../../helpers/FCMService.php";
            $fcm = new FCMService($conn);
            $fcm->sendToUser($user['id'], $user['fcm_token'], [
                'title' => 'Order Placed Successfully',
                'body' => "Your order #$order_id has been placed.",
                'type' => 'order',
                'data' => ['order_id' => (string)$order_id] // FCM data expects strings
            ]);
        }
    } catch (Exception $e) {
        // Silently ignore notification failure so the order is still created successfully
    }
    // --- END FCM LOGIC ---

    sendResponse(201, t('order_created'), [
        "order_id" => $order_id,
        "total_price" => $grandTotal,
        "status" => "pending"
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    sendResponse(500, t('database_error'));
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

try {
    $stmt = $conn->prepare("
        SELECT 
            c.id as cart_id, 
            c.quantity, 
            p.id as product_id, 
            p.name, 
            p.price, 
            p.description, 
            p.image_url, 
            p.category_id, 
            p.stock,
            cat.name as category_name
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN categories cat ON p.category_id = cat.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $cartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cartItems = [];
    $totalQuantity = 0;
    $totalPrice = 0;

    foreach ($cartData as $item) {
        $cartId = (int)$item['cart_id'];
        $requestedQty = (int)$item['quantity'];
        $stock = (int)$item['stock'];
        $price = (float)$item['price'];

        if ($stock <= 0) {
            $deleteStmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
            $deleteStmt->execute([$cartId]);
            continue;
        }

        if ($requestedQty > $stock) {
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $updateStmt->execute([$stock, $cartId]);
            $requestedQty = $stock;
        }

        $totalQuantity += $requestedQty;
        $totalPrice += $requestedQty * $price;

        $cartItems[] = [
            "id" => $cartId,
            "quantity" => $requestedQty,
            "product" => [
                "id" => (int)$item['product_id'],
                "name" => $item['name'],
                "price" => $price,
                "description" => $item['description'],
                "image_url" => $item['image_url'],
                "stock" => $stock,
                "category" => [
                    "id" => $item['category_id'] ? (int)$item['category_id'] : null,
                    "name" => $item['category_name'] ?? null
                ]
            ]
        ];
    }

    $deliveryCharges = $totalPrice > 500 ? 0 : 50;

    sendResponse(200, "Cart items retrieved successfully", [
        "items" => $cartItems,
        "total_quantity" => $totalQuantity,
        "total_price" => $totalPrice,
        "delivery_charges" => $deliveryCharges,
        "grand_total" => $totalPrice + $deliveryCharges
    ]);

} catch (Exception $e) {
    sendResponse(500, "Failed to retrieve cart items", null, ["error" => $e->getMessage()]);
}
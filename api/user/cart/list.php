<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

try {
    $name_col = ($lang === 'ar') ? "COALESCE(pt.name, p.name)" : "p.name";
    $desc_col = ($lang === 'ar') ? "COALESCE(pt.description, p.description)" : "p.description";
    $cat_name_col = ($lang === 'ar') ? "COALESCE(ct.name, cat.name)" : "cat.name";

    $stmt = $conn->prepare("
        SELECT
            c.id         AS cart_id,
            c.quantity,
            p.id         AS product_id,
            $name_col              AS name,
            p.price,
            $desc_col AS description,
            p.image_url,
            p.category_id,
            p.stock,
            $cat_name_col            AS category_name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        LEFT JOIN products_translations   pt  ON pt.product_id  = p.id
        LEFT JOIN categories              cat ON cat.id          = p.category_id
        LEFT JOIN categories_translations ct  ON ct.category_id = cat.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $cartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cartItems    = [];
    $totalQuantity = 0;
    $totalPrice    = 0;

    foreach ($cartData as $item) {
        $cartId       = (int)$item['cart_id'];
        $requestedQty = (int)$item['quantity'];
        $stock        = (int)$item['stock'];
        $price        = (float)$item['price'];

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
        $totalPrice    += $requestedQty * $price;

        $cartItems[] = [
            "id"       => $cartId,
            "quantity" => $requestedQty,
            "product"  => [
                "id"          => (int)$item['product_id'],
                "name"        => $item['name'],
                "price"       => $price,
                "description" => $item['description'],
                "image_url"   => $item['image_url'],
                "stock"       => $stock,
                "category"    => [
                    "id"   => $item['category_id'] ? (int)$item['category_id'] : null,
                    "name" => $item['category_name'] ?? null
                ]
            ]
        ];
    }

    $deliveryCharges = ($totalPrice == 0 || $totalPrice > 2000) ? 0 : 50;

    sendResponse(200, t('cart_retrieved'), [
        "items"            => $cartItems,
        "total_quantity"   => $totalQuantity,
        "total_price"      => $totalPrice,
        "delivery_charges" => $deliveryCharges,
        "grand_total"      => $totalPrice + $deliveryCharges
    ]);

} catch (Exception $e) {
    sendResponse(500, t('cart_failed'));
}
<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
    sendResponse(400, t('quantity_invalid'));
}

$name_col      = ($lang === 'ar') ? "COALESCE(pt.name, p.name)" : "p.name";
$desc_col      = ($lang === 'ar') ? "COALESCE(pt.description, p.description)" : "p.description";
$cat_name_col  = ($lang === 'ar') ? "COALESCE(ct.name, c.name)" : "c.name";

$productStmt = $conn->prepare(
    "SELECT
        p.*,
        $name_col               AS name,
        $desc_col               AS description,
        c.name                  AS category_name_en,
        $cat_name_col           AS category_name,
        c.image_url             AS category_image
     FROM products p
     LEFT JOIN products_translations   pt ON pt.product_id  = p.id
     LEFT JOIN categories              c  ON c.id            = p.category_id
     LEFT JOIN categories_translations ct ON ct.category_id = c.id
     WHERE p.id = ?
     LIMIT 1"
);
$productStmt->execute([$data['product_id']]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    sendResponse(404, t('product_not_found'));
}

// Format: nest category
$product['category'] = [
    "id"        => $product['category_id'],
    "name"      => $product['category_name'],
    "image_url" => $product['category_image']
];
unset($product['category_name'], $product['category_name_en'], $product['category_image']);

$cartStmt = $conn->prepare(
    "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? LIMIT 1"
);
$cartStmt->execute([$user['id'], $data['product_id']]);
$existing = $cartStmt->fetch(PDO::FETCH_ASSOC);

$requestedQty = (int)$data['quantity'];
$totalQty     = $requestedQty;

if ($totalQty > (int)$product['stock']) {
    sendResponse(400, t('only_items_available', null, ['count' => (int)$product['stock']]));
}

if ($existing) {
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->execute([$totalQty, $existing['id']]);

    sendResponse(200, t('cart_updated'), [
        "id"         => $existing['id'],
        "product_id" => $data['product_id'],
        "quantity"   => $totalQty,
        "product"    => $product
    ]);
} else {
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->execute([$user['id'], $data['product_id'], $requestedQty]);
    $cart_id = $conn->lastInsertId();

    sendResponse(201, t('cart_item_added'), [
        "id"         => $cart_id,
        "product_id" => $data['product_id'],
        "quantity"   => $requestedQty,
        "product"    => $product
    ]);
}

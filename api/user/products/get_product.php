<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);
$lang = getRequestedLang();

// 1. Validate ID
$id = (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0)
  ? (int)$_GET['id']
  : null;

if (!$id) {
    sendResponse(400, t('invalid_id'));
}

try {
    $name_col      = ($lang === 'ar') ? "COALESCE(pt.name, p.name)" : "p.name";
    $desc_col      = ($lang === 'ar') ? "COALESCE(pt.description, p.description)" : "p.description";
    $cat_name_col  = ($lang === 'ar') ? "COALESCE(ct.name, c.name)" : "c.name";

    // 2. Fetch Product with translations and category
    $sql = "SELECT
                p.*,
                $name_col               AS name,
                $desc_col               AS description,
                c.name                  AS category_name_en,
                $cat_name_col           AS category_name
            FROM products p
            LEFT JOIN products_translations   pt ON pt.product_id  = p.id
            LEFT JOIN categories              c  ON c.id            = p.category_id
            LEFT JOIN categories_translations ct ON ct.category_id = c.id
            WHERE p.id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Return 404 if not found
    if (!$product) {
        sendResponse(404, t('product_not_found'));
    }

    // 4. Format and clean up the result
    $cat_id   = $product['category_id'] !== null ? (int)$product['category_id'] : null;
    $cat_name = $product['category_name'];

    unset($product['category_id'], $product['category_name'], $product['category_name_en']);

    $product['category'] = [
        "id"        => $cat_id,
        "name"      => $cat_name,
        "image_url" => "" // Placeholder or fetch if available
    ];

    // Ensure numeric types are cast
    $product['id'] = (int)$product['id'];
    $product['price'] = (float)$product['price'];
    if (isset($product['stock'])) $product['stock'] = (int)$product['stock'];

    sendResponse(200, t('product_retrieved'), ["product" => $product]);

} catch (Exception $e) {
    sendResponse(500, t('error_fetching_product'), ["error" => $e->getMessage()]);
}

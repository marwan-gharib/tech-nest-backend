<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

if (
    empty($_POST['name']) ||
    empty($_POST['description']) ||
    !isset($_POST['price']) ||
    !isset($_POST['stock']) ||
    empty($_POST['category_id'])
) {
    sendResponse(400, t('all_fields_required'));
}

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $upload_dir = "../../../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        sendResponse(400, t('invalid_image_type'));
    }
    $hash = hash_file('sha256', $_FILES['image']['tmp_name']);
    $existing = glob($upload_dir . $hash . '.webp');
    if ($existing && count($existing) > 0) {
        $image_path = 'uploads/' . basename($existing[0]);
    } else {
        $image_path = saveImageAsWebp($_FILES['image']['tmp_name'], $upload_dir, $hash);
        if (!$image_path) {
            sendResponse(500, t('image_upload_failed'));
        }
    }
}

$name        = trim($_POST['name']);
$description = trim($_POST['description']);
$price       = (float)$_POST['price'];
$stock       = (int)$_POST['stock'];
$category_id = (int)$_POST['category_id'];

// Arabic fields (fall back to English if not provided)
$name_ar        = isset($_POST['name_ar'])        ? trim($_POST['name_ar'])        : $name;
$description_ar = isset($_POST['description_ar']) ? trim($_POST['description_ar']) : $description;

try {
    $stmt = $conn->prepare(
        "INSERT INTO products (name, description, price, stock, category_id, image_url)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$name, $description, $price, $stock, $category_id, $image_path]);
    $product_id = (int)$conn->lastInsertId();

    // Sync Arabic translation
    upsertProductTranslation($conn, $product_id, $name_ar, $description_ar);

    sendResponse(201, t('product_added'), [
        "id"             => $product_id,
        "name"           => $name,
        "name_ar"        => $name_ar,
        "description"    => $description,
        "description_ar" => $description_ar,
        "price"          => $price,
        "stock"          => $stock,
        "category_id"    => $category_id,
        "image_url"      => $image_path
    ]);
} catch (Exception $e) {
    sendResponse(500, t('product_add_failed'));
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

if (empty($_POST['name'])) {
    sendResponse(400, t('category_name_required'));
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

$name    = trim($_POST['name']);
$name_ar = isset($_POST['name_ar']) ? trim($_POST['name_ar']) : $name; // fallback to EN

$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(`name`) = LOWER(?) LIMIT 1");
$dup->execute([$name]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(409, t('category_exists'));
}

$stmt = $conn->prepare("INSERT INTO categories (`name`, `image_url`) VALUES (?, ?)");
try {
    $stmt->execute([$name, $image_path]);
    $category_id = (int)$conn->lastInsertId();

    // Sync Arabic translation
    upsertCategoryTranslation($conn, $category_id, $name_ar);

    sendResponse(201, t('category_added'), [
        "id"        => $category_id,
        "name"      => $name,
        "name_ar"   => $name_ar,
        "image_url" => $image_path
    ]);
} catch (Exception $e) {
    sendResponse(500, t('category_add_failed'));
}

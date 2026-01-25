<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

if (empty($_POST['id']) || empty($_POST['name'])) {
    sendResponse(400, "All fields required");
}

$image_path = null;

$stmt = $conn->prepare("SELECT image_url FROM categories WHERE id=?");
$stmt->execute([$_POST['id']]);
$current_image_path = $stmt->fetchColumn();

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $upload_dir = "../../../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        sendResponse(400, "Invalid image type");
    }
    $hash = hash_file('sha256', $_FILES['image']['tmp_name']);
    $existing = glob($upload_dir . $hash . '.*');
    if ($existing && count($existing) > 0) {
        $image_path = 'uploads/' . basename($existing[0]);
    } else {
        $image_name = $hash . "." . $ext;
        $image_path = "uploads/" . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], "../../../" . $image_path)) {
            sendResponse(500, "Failed to upload image");
        }
    }
} else {
    $image_path = $current_image_path;
}

$category_id = intval($_POST['id']);
$name = trim($_POST['name']);

$check = $conn->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
$check->execute([$category_id]);

if (!$check->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(404, "Category not found");
}

try {
    $stmt = $conn->prepare("UPDATE categories SET `name` = ?, image_url = ? WHERE id = ?");
    $stmt->execute([$name, $image_path, $category_id]);

    if ($current_image_path && $current_image_path !== $image_path && !isImageUsed($conn, $current_image_path)) {
        $fullPath = "../../../" . $current_image_path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    sendResponse(200, "Category updated successfully", ["id" => $category_id, "name" => $name, "image_url" => $image_path]);
} catch (Exception $e) {
    sendResponse(500, "Failed to update category");
}

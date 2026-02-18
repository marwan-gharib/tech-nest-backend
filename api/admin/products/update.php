<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

if (
    empty($_POST['id']) ||
    empty($_POST['name']) ||
    empty($_POST['description']) ||
    !isset($_POST['price']) ||
    !isset($_POST['stock'])
) {
    sendResponse(400, "All fields required");
}

$image_path = null;

$stmt = $conn->prepare("SELECT image_url FROM products WHERE id=?");
$stmt->execute([$_POST['id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    sendResponse(404, "Product not found");
}

$current_image_path = $row['image_url'];

$image_path = $current_image_path;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $upload_dir = "../../../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        sendResponse(400, "Invalid image type");
    }
    $hash = hash_file('sha256', $_FILES['image']['tmp_name']);
    $existing = glob($upload_dir . $hash . '.webp');
    if ($existing && count($existing) > 0) {
        $image_path = 'uploads/' . basename($existing[0]);
    } else {
        $image_path = saveImageAsWebp($_FILES['image']['tmp_name'], $upload_dir, $hash);
        if (!$image_path) {
            sendResponse(500, "Failed to convert/upload image");
        }
    }
} else {
    $image_path = $current_image_path;
}

try {
    $stmt = $conn->prepare("UPDATE products SET name = ?, `description` = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['stock'],
        $image_path,
        $_POST['id']
    ]);

    if ($current_image_path && $current_image_path !== $image_path && !isImageUsed($conn, $current_image_path)) {
        $fullPath = "../../../" . $current_image_path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    sendResponse(200, "Product updated successfully", $updatedProduct);
} catch (Exception $e) {
    sendResponse(500, "Failed to update product");
}

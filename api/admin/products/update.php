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

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $upload_dir = "../../../uploads/";

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        sendResponse(400, "Invalid image type");
    }

    // Deduplicate by content hash
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
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $image_path = $stmt->fetchColumn();
}

try {
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['stock'],
        $image_path,
        $_POST['id']
    ]);

    sendResponse(200, "Product updated successfully", [
        "id" => $_POST['id'],
        "name" => $_POST['name'],
        "description" => $_POST['description'],
        "price" => $_POST['price'],
        "stock" => $_POST['stock'],
        "image_url" => $image_path
    ]);
} catch (Exception $e) {
    sendResponse(500, "Failed to update product");
}

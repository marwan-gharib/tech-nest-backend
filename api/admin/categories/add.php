<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

if (empty($_POST['name'])) {
    sendResponse(400, "Category name is required");
}

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $upload_dir = "../../../uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

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
}

$name = trim($_POST['name']);

$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
$dup->execute([$name]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(409, "Category already exists");
}

$stmt = $conn->prepare("INSERT INTO categories (`name`, `image_url`) VALUES (?, ?)");
try {
    $stmt->execute([$name, $image_path]);
    $category_id = $conn->lastInsertId();
    sendResponse(201, "Category added successfully", [
        "id" => (int)$category_id,
        "name" => $name,
        "image_url" => $image_path
    ]);
} catch (Exception $e) {
    sendResponse(500, "Failed to add category");
}

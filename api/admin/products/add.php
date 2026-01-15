<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

if (
    empty($_POST['name']) ||
    empty($_POST['description']) ||
    empty($_POST['price']) ||
    empty($_POST['stock']) ||
    empty($_POST['category_id'])
) {
    sendResponse(400, "All fields are required");
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
$description = trim($_POST['description']);
$price = floatval($_POST['price']);
$stock = intval($_POST['stock']);
$category_id = intval($_POST['category_id']);

$check = $conn->prepare("SELECT id, stock FROM products WHERE name = ? AND category_id = ? LIMIT 1");
$check->execute([$name, $category_id]);
$existing = $check->fetch(PDO::FETCH_ASSOC);

try {
    if ($existing) {
        if ($image_path) {
            $update = $conn->prepare("UPDATE products SET stock = stock + ?, image_url = COALESCE(?, image_url) WHERE id = ?");
            $update->execute([$stock, $image_path, $existing['id']]);
        } else {
            $update = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $update->execute([$stock, $existing['id']]);
        }

        sendResponse(200, "Product already exists. Stock increased.");
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$name, $description, $price, $stock, $category_id, $image_path]);
            $product_id = $conn->lastInsertId();
            sendResponse(201, "Product added successfully", [
                "id" => (int)$product_id,
                "name" => $name,
                "description" => $description,
                "price" => $price,
                "stock" => $stock,
                "category_id" => $category_id,
                "image_url" => $image_path
            ]);
        } catch (Exception $e) {
            sendResponse(500, "Failed to add product");
        }
    }
} catch (Exception $e) {
    sendResponse(500, "Failed to add product");
}

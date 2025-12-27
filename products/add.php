<?php
include "../config.php";

if (
    empty($_POST['name']) ||
    empty($_POST['description']) ||
    empty($_POST['price']) ||
    empty($_POST['stock']) ||
    empty($_POST['category_id'])
) {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

$image_path = null;

if (!empty($_FILES['img']['name'])) {

    $upload_dir = "../uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
    $image_name = uniqid() . "." . $ext;
    $image_path = "uploads/" . $image_name;

    move_uploaded_file(
        $_FILES['img']['tmp_name'],
        "../" . $image_path
    );
}

// Normalize inputs
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$price = floatval($_POST['price']);
$stock = intval($_POST['stock']);
$category_id = intval($_POST['category_id']);

// Check if product with same name and category exists
$check = $conn->prepare("SELECT id, stock FROM products WHERE name = ? AND category_id = ? LIMIT 1");
$check->execute([$name, $category_id]);
$existing = $check->fetch(PDO::FETCH_ASSOC);

try {
    if ($existing) {
        // If exists: increase stock and optionally update image
        if ($image_path) {
            $update = $conn->prepare("UPDATE products SET stock = stock + ?, image_url = COALESCE(?, image_url) WHERE id = ?");
            $update->execute([$stock, $image_path, $existing['id']]);
        } else {
            $update = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $update->execute([$stock, $existing['id']]);
        }

        echo json_encode([
            "status" => true,
            "message" => "Product already exists. Stock increased."
        ]);
    } else {
        // Not exists: insert new product
        $stmt = $conn->prepare(
            "INSERT INTO products (name, description, price, stock, category_id, image_url)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $description,
            $price,
            $stock,
            $category_id,
            $image_path
        ]);

        echo json_encode([
            "status" => true,
            "message" => "Product added successfully"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to add product",
        "error" => $e->getMessage()
    ]);
}

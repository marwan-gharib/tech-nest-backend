<?php
include "../config.php";

$token = $_POST['token'] ?? null;

if (!$token) {
    echo json_encode(["status"=>false,"message"=>"Token required"]);
    exit;
}

$user = validateToken($conn, $token);
checkAdmin($conn, $user['id']);

if (
    empty($_POST['id']) ||
    empty($_POST['name']) ||
    empty($_POST['description']) ||
    !isset($_POST['price']) ||
    !isset($_POST['stock'])
) {
    echo json_encode(["status"=>false,"message"=>"All fields required"]);
    exit;
}

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $upload_dir = "../uploads/";

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(["status"=>false,"message"=>"Invalid image type"]);
        exit;
    }

    // Deduplicate by content hash
    $hash = hash_file('sha256', $_FILES['image']['tmp_name']);
    $existing = glob($upload_dir . $hash . '.*');
    if ($existing && count($existing) > 0) {
        $image_path = 'uploads/' . basename($existing[0]);
    } else {
        $image_name = $hash . "." . $ext;
        $image_path = "uploads/" . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], "../" . $image_path)) {
            echo json_encode(["status"=>false,"message"=>"Failed to upload image"]);
            exit;
        }
    }
} else {
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $image_path = $stmt->fetchColumn();
}

try {
    $stmt = $conn->prepare(
        "UPDATE products
         SET name=?, description=?, price=?, stock=?, image_url=?
         WHERE id=?"
    );
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['stock'],
        $image_path,
        $_POST['id']
    ]);

    echo json_encode(["status"=>true,"message"=>"Product updated successfully"]);
} catch (Exception $e) {
    echo json_encode([
        "status"=>false,
        "message"=>"Failed to update product",
        "error"=>$e->getMessage()
    ]);
}

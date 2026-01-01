<?php
include "../config.php";

$token = $_POST['token'] ?? null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["status"=>401,"message"=>"Token required"]);
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
    http_response_code(400);
    echo json_encode(["status"=>400,"message"=>"All fields required"]);
    exit;
}

$image_path = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $upload_dir = "../uploads/";

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(["status"=>400,"message"=>"Invalid image type"]);
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
            http_response_code(500);
            echo json_encode(["status"=>500,"message"=>"Failed to upload image"]);
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

    http_response_code(200);
    echo json_encode(["status"=>200,"message"=>"Product updated successfully"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"=>500,
        "message"=>"Failed to update product"
    ]);
}

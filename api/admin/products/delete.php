<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);
$admin = validateAdminToken($conn);

if (!$data || empty($data['id'])) {
    sendResponse(400, "Product id is required");
}

$stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->execute([$data['id']]);
$imagePath = $stmt->fetchColumn();

try {
    $delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete->execute([$data['id']]);

    if ($delete->rowCount() === 0) {
        sendResponse(404, "Product not found");
    }

    if ($imagePath && !isImageUsed($conn, $imagePath)) {
        $fullPath = "../../../" . $imagePath;
        if (file_exists($fullPath)) unlink($fullPath);
    }

    sendResponse(200, "Product deleted successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to delete product");
}

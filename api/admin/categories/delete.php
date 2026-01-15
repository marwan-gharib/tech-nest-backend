<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn);

$stmt = $conn->prepare("SELECT image_url FROM categories WHERE id = ? LIMIT 1");
if (!isset($data['id']) || empty($data['id'])) {
    sendResponse(400, "Category id is required");
}
$stmt->execute([$data['id']]);
$imagePath = $stmt->fetchColumn();

$delete = $conn->prepare("DELETE FROM categories WHERE id=?");
try {
    $delete->execute([$data['id']]);

    if ($delete->rowCount() === 0) {
        sendResponse(404, "Category not found");
    }

    $fullPath = "../../../" . $imagePath;

    if ($imagePath && file_exists($fullPath)) {
        unlink($fullPath);
    }

    sendResponse(200, "Category deleted successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to delete category");
}

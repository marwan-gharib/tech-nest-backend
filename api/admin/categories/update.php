<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn, $data['token'] ?? null);

if (empty($data['name'])) {
    sendResponse(400, "Category name is required");
}

// Prevent duplicate name (case-insensitive) excluding current id
$name = trim($data['name']);
$category_id = intval($data['id']);
$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name)=LOWER(?) AND id<>? LIMIT 1");
$dup->execute([$name, $category_id]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(409, "Category already exists");
}

try {
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->execute([$name, $category_id]);

    sendResponse(200, "Category updated successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to update category");
}

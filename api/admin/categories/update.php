<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn, $data['token'] ?? null);

if (empty($data['name'])) {
    sendResponse(400, "Category name is required");
}

$category_id = intval($data['id']);
$name = trim($data['name']);

$check = $conn->prepare("SELECT id FROM categories WHERE id = ? LIMIT 1");
$check->execute([$category_id]);

if (!$check->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(404, "Category not found");
}

try {
    $stmt = $conn->prepare(
        "UPDATE categories SET name = ? WHERE id = ?"
    );
    $stmt->execute([$name, $category_id]);

    sendResponse(200, "Category updated successfully");

} catch (Exception $e) {
    sendResponse(500, "Failed to update category");
}

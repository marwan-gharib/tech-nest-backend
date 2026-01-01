<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn, $data['token'] ?? null);

$name = trim($data['name']);
if ($name === '') {
    sendResponse(400, "Category name is required");
}

$dup = $conn->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
$dup->execute([$name]);
if ($dup->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(409, "Category already exists");
}

$stmt = $conn->prepare("INSERT INTO categories (`name`) VALUES (?)");
try {
    $stmt->execute([$name]);
    sendResponse(201, "Category added successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to add category");
}

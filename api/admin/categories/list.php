<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

// Validate Admin Token
$admin = validateAdminToken($conn, $_GET['token'] ?? null);

try {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY id DESC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Categories retrieved successfully", $categories);
} catch (Exception $e) {
    sendResponse(500, "Failed to retrieve categories");
}

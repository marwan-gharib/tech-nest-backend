<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

try {
    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Categories retrieved successfully", $categories);
} catch (Exception $e) {
    sendResponse(500, "Failed to retrieve categories");
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

// Validate Admin Token
// Although listing products might be public info, 
// the admin dashboard might require a secured endpoint 
// that could strictly be accessed by admins (e.g., for future hidden fields).
$admin = validateAdminToken($conn, $_GET['token'] ?? null);

try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Products retrieved successfully", $products);
} catch (Exception $e) {
    sendResponse(500, "Failed to retrieve products");
}

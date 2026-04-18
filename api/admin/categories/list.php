<?php
// Admin always sees English data — no translation JOIN needed
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

try {
    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, t('categories_retrieved'), $categories);
} catch (Exception $e) {
    sendResponse(500, t('categories_failed'));
}

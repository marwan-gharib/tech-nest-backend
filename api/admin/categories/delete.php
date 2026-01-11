<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn);

$stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
try {
    $stmt->execute([$data['id']]);

    if ($stmt->rowCount() === 0) {
        sendResponse(404, "Category not found");
    }

    sendResponse(200, "Category deleted successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to delete category");
}

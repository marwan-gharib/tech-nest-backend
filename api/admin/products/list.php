<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn);

try {
    if (isset($data['category_id'])) {

        $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $check->execute([$data['category_id']]);

        if (!$check->fetch()) {
            sendResponse(404, "Category not found", null);
        }

        $stmt = $conn->prepare(
            "SELECT * FROM products WHERE category_id = ?"
        );
        $stmt->execute([$data['category_id']]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products");
        $stmt->execute();
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(200, "Products retrieved successfully", $products);
} catch (Exception $e) {
    sendResponse(500, "Failed to retrieve products", null);
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

try {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as unique_items,
            SUM(quantity) as total_quantity
        FROM cart 
        WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $unique_items = (int)($result['unique_items'] ?? 0);

    sendResponse(200, "Cart items count retrieved successfully", [
        "count" => $unique_items,
    ]);

} catch (Exception $e) {
    sendResponse(500, "Failed to retrieve cart items count", ["error" => $e->getMessage()]);
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

try {
    $stmt = $conn->prepare("
        SELECT
            COUNT(*)      AS unique_items,
            SUM(quantity) AS total_quantity
        FROM cart
        WHERE user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $unique_items = (int)($result['unique_items'] ?? 0);

    sendResponse(200, t('cart_count_retrieved'), [
        "count" => $unique_items,
    ]);

} catch (Exception $e) {
    sendResponse(500, t('cart_count_failed'));
}

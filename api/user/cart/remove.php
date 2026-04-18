<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn);

$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");

try {
    $stmt->execute([$data['id'], $user['id']]);

    if ($stmt->rowCount() === 0) {
        sendResponse(404, t('cart_not_found'));
    }

    sendResponse(200, t('cart_item_removed'), ["id" => $data['id']]);
} catch (Exception $e) {
    sendResponse(500, t('cart_remove_failed'));
}

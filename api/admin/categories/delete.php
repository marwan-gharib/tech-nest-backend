<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$admin = validateAdminToken($conn);

if (!$data || !isset($data['id']) || empty($data['id'])) {
    sendResponse(400, t('category_id_required'));
}

$stmt = $conn->prepare("SELECT image_url FROM categories WHERE id = ? LIMIT 1");
$stmt->execute([$data['id']]);
$imagePath = $stmt->fetchColumn();

$delete = $conn->prepare("DELETE FROM categories WHERE id=?");
try {
    $delete->execute([$data['id']]);

    if ($delete->rowCount() === 0) {
        sendResponse(404, t('category_not_found'));
    }

    if ($imagePath && !isImageUsed($conn, $imagePath)) {
        $fullPath = "../../../" . $imagePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    sendResponse(200, t('category_deleted'));
} catch (Exception $e) {
    sendResponse(500, t('category_delete_failed'));
}

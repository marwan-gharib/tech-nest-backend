<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['old_password']) ||
    empty($data['new_password'])
) {
    sendResponse(400, "Old password and new password are required");
}

$user = validateToken($conn);

$stmt = $conn->prepare("SELECT `password` FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || !password_verify($data['old_password'], $row['password'])) {
    sendResponse(400, "Old password is incorrect");
}

$newHashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);

try {
    $update = $conn->prepare("UPDATE users SET `password` = ? WHERE id = ?");
    $update->execute([$newHashedPassword, $user['id']]);

    sendResponse(200, "Password updated successfully");
} catch (Exception $e) {
    sendResponse(500, "Failed to update password");
}


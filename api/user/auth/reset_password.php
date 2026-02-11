<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['email']) ||
    empty($data['verification_code']) ||
    empty($data['new_password'])
) {
    sendResponse(400, "Email, verification code, and new password are required", null, ["fields" => "Missing required fields"]);
}

$stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ? AND verification_code = ? AND code_expires_at >= NOW()");
$stmt->execute([$data['email'], $data['verification_code']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendResponse(400, "Invalid or expired verification code", null, ["verification_code" => "Invalid or expired"]);
}

if ($user['is_verified'] == 0) {
    sendResponse(403, "Email not verified. Please verify your email first.", null, ["email" => "Not verified"]);
}

$newHashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);

try {
    $update = $conn->prepare("UPDATE users SET `password` = ?, verification_code = NULL, code_expires_at = NULL WHERE id = ?");
    $update->execute([$newHashedPassword, $user['id']]);

    sendResponse(200, "Password updated successfully", ["user_id" => $user['id']]);
} catch (Exception $e) {
    sendResponse(500, "Failed to update password", null, ["exception" => $e->getMessage()]);
}

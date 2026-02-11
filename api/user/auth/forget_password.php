<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['email'])) {
    sendResponse(400, "Email is required", null, ["email" => "Field is required"]);
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Invalid email format", null, ["email" => "Invalid format"]);
}

$stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendResponse(404, "User with this email does not exist", null, ["email" => "Not found"]);
}

if ($user['is_verified'] == 0) {
    sendResponse(403, "Email not verified. Please verify your email first.", null, ["email" => "Not verified"]);
}

$verification_code = rand(100000, 999999);

try {
    $stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?");
    $stmt->execute([$verification_code, $user['id']]);

    sendForgotPasswordEmail($data['email'], $verification_code);

    sendResponse(200, "Password reset code sent to your email", ["email" => $data['email']]);
} catch (Exception $e) {
    sendResponse(500, "Failed to process request", null, ["exception" => $e->getMessage()]);
}

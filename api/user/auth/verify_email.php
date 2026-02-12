<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare(
    "SELECT * FROM users 
    WHERE email=? 
    AND verification_code=? 
    AND is_verified=0 
    AND code_expires_at >= NOW()"
);

$stmt->execute([$data['email'], $data['verification_code']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    sendResponse(400, "Invalid verification code or email.");
}

$token = generateTokenWithExpiry($user['id'], 7, $conn);

$stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL, code_expires_at=NULL, token=? WHERE email=?");
$stmt->execute([$token, $data['email']]);

$stmt = $conn->prepare("SELECT id, name, email, is_verified, profile_image FROM users WHERE email=?");
$stmt->execute([$data['email']]);
$verifiedUser = $stmt->fetch(PDO::FETCH_ASSOC);

sendResponse(200, "Email verified successfully.", [
    "token" => $token,
    "user" => [
        "id" => $verifiedUser['id'],
        "name" => $verifiedUser['name'],
        "email" => $verifiedUser['email'],
        "image_url" => $verifiedUser['profile_image'] ?? null
    ]
]);

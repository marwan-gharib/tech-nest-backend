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

$token = bin2hex(random_bytes(25));
// Cannot set verification_code to NULL as it is NOT NULL in DB. Set to 0.
$stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_code=0, code_expires_at=NULL, token=? WHERE email=?");
$stmt->execute([$token, $data['email']]);

sendResponse(200, "Email verified successfully.", [
    "id" => $user['id'],
    "name" => $user['name'],
    "email" => $user['email'],
    "token" => $token
]);

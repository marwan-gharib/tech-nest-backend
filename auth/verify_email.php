<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare(
    "SELECT * FROM users 
    WHERE email=? 
    AND verification_code=? 
    AND is_verified=0 
    AND verification_expires_at >= NOW()"
);

$stmt->execute([$data['email'], $data['verification_code']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid verification code or email.",
        "data" => null
    ]);
    exit;
}

$token = bin2hex(random_bytes(50));
$stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL, verification_expires_at=NULL, token=? WHERE email=?");
$stmt->execute([$token, $email]);

echo json_encode([
    "status" => true,
    "message" => "Email verified successfully.",
    "data" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role'],
        "token" => $token
    ]
]);
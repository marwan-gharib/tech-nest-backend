<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'];
$verification_code = $data['verification_code'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND verification_code=? AND is_verified=0");
$stmt->execute([$email, $verification_code]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid verification code or email.",
        "data" => null
    ]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET is_verified=1 WHERE email=?");
$stmt->execute([$email]);

echo json_encode([
    "status" => true,
    "message" => "Email verified successfully.",
    "data" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role']
    ]
]);
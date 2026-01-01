<?php
include "../config.php";

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
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => 400,
        "message" => "Invalid verification code or email."
    ]);
    exit;
}

$token = bin2hex(random_bytes(25));
$stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL, code_expires_at=NULL, token=? WHERE email=?");
$stmt->execute([$token, $data['email']]);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    "status" => 200,
    "message" => "Email verified successfully.",
    "data" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role'],
        "token" => $token
    ]
]);

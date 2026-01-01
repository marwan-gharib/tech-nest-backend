<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode([
        "status" => 400,
        "message" => "All fields are required"
    ]);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "status" => 400,
        "message" => "Invalid email format"
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$data['email']]);
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(409);
    echo json_encode([
        "status" => 409,
        "message" => "Email already exists"
    ]);
    exit;
}

$verification_code = rand(100000, 999999);

$stmt = $conn->prepare(
    "INSERT INTO users 
    (`name`,email,`password`,`role`,verification_code,is_verified,code_expires_at,token)
    VALUES (?,?,?,?,?,0,DATE_ADD(NOW(),INTERVAL 5 MINUTE),NULL)"
);
$stmt->execute([
    $data['name'],
    $data['email'],
    password_hash($data['password'], PASSWORD_BCRYPT),
    'user',
    $verification_code
]);

sendVerificationEmail($data['email'], $verification_code);

http_response_code(201);
echo json_encode([
    "status" => 201,
    "message" => "Registration successful. Verification code sent to email.",
    "data" => [
        "name" => $data['name'],
        "email" => $data['email']
    ]
]);

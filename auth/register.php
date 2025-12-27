<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "All fields are required",
        "data" => null
    ]);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email format",
        "data" => null
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$data['email']]);
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        "status" => false,
        "message" => "Email already exists",
        "data" => null
    ]);
    exit;
}

$verification_code = rand(100000, 999999);
$stmt = $conn->prepare(
    "INSERT INTO users (`name`, email, `password`, `role`, `verification_code`, `is_verified`)
     VALUES (?, ?, ?, ?, ?, 0)"
);
$stmt->execute([
    $data['name'],
    $data['email'],
    password_hash($data['password'], PASSWORD_BCRYPT),
    'user',
    $verification_code
]);

mail($data['email'], "Your Verification Code", "Your verification code is: $verification_code");

echo json_encode([
    "status" => true,
    "message" => "Registration successful. Verification code sent to email.",
    "data" => [
        "name" => $data['name'],
        "email" => $data['email']
    ]
]);

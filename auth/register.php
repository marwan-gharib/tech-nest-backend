<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['name']) || empty($data['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Name and password are required",
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

$stmt = $conn->prepare(
    "INSERT INTO users (`name`,email,`password`,`role`)
     VALUES (?,?,?, 'user')"
);

$stmt->execute([
    $data['name'],
    $data['email'],
    password_hash($data['password'], PASSWORD_DEFAULT)
]);

echo json_encode([
    "status" => true,
    "message" => "Registration successful",
    "data" => null
]);

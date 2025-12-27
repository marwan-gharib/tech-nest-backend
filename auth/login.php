<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

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
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data['password'], $user['password'])) {
    $token = bin2hex(random_bytes(16));
    $stmt = $conn->prepare("UPDATE users SET token=? WHERE id=?");
    $stmt->execute([$token, $user['id']]);

    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "data" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "role" => $user['role'],
            "token" => $token
        ]
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email or password",
        "data" => null
    ]);
}

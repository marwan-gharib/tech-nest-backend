<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data['password'], $user['password'])) {
    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "data" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "role" => $user['role']
        ]
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email or password",
        "data" => null
    ]);
}

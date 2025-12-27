<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare(
    "INSERT INTO users (name,email,password,role)
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

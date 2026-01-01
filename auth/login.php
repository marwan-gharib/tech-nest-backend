<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

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
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data['password'], $user['password'])) {
    if ($user['token'] !== null) {
        http_response_code(403);
        echo json_encode([
            "status" => 403,
            "message" => "User already logged in"
        ]);
        exit;
    }

    $token = bin2hex(random_bytes(25));
    $stmt = $conn->prepare("UPDATE users SET token=? WHERE id=?");
    $stmt->execute([$token, $user['id']]);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
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
    http_response_code(401);
    echo json_encode([
        "status" => 401,
        "message" => "Invalid email or password"
    ]);
}

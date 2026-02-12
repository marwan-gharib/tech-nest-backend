<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Invalid email format");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($data['password'], $user['password'])) {
    if ($user['is_verified'] == 0) {
        sendResponse(401, "Invalid email or password");
    }

    $token = generateTokenWithExpiry($user['id'], 7, $conn);

    sendResponse(200, "Login successful", [
        "token" => $token,
        "user" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "image_url" => $user['profile_image'] ?? null
        ]
    ]);
} else {
    sendResponse(401, "Invalid email or password");
}

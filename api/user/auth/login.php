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
    $token = generateTokenWithExpiry($user['id'], 7, $conn);

    sendResponse(200, "Login successful", [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "token" => $token
    ]);
} else {
    sendResponse(401, "Invalid email or password");
}

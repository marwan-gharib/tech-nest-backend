<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['password'])) {
    sendResponse(400, "Email and password are required");
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Invalid email format");
}

// Check against admins table
$stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
$stmt->execute([$data['email']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($data['password'], $admin['password'])) {
    $token = generateTokenWithExpiry($admin['id'], 2, $conn, 'admins');

    sendResponse(200, "Admin login successful", [
        "id" => $admin['id'],
        "name" => $admin['name'],
        "email" => $admin['email'],
        "role" => "admin",
        "token" => $token
    ]);
} else {
    sendResponse(401, "Invalid email or password");
}

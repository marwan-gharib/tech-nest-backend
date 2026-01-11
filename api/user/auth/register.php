<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    sendResponse(400, "All fields are required");
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Invalid email format");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$data['email']]);
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    sendResponse(409, "Email already exists");
}

$verification_code = rand(100000, 999999);

$stmt = $conn->prepare(
    "INSERT INTO users 
    (`name`,email,`password`,verification_code,is_verified,code_expires_at,token)
    VALUES (?,?,?,?,0,DATE_ADD(NOW(),INTERVAL 5 MINUTE),NULL)"
);
$stmt->execute([
    $data['name'],
    $data['email'],
    password_hash($data['password'], PASSWORD_BCRYPT),
    $verification_code
]);

sendVerificationEmail($data['email'], $verification_code);

sendResponse(201, "Registration successful. Verification code sent to email.", [
    "name" => $data['name'],
    "email" => $data['email']
]);

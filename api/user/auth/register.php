<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    sendResponse(400, "All fields are required", null, ["fields" => "Missing required fields"]);
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Invalid email format", null, ["email" => "Invalid format"]);
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$data['email']]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existingUser) {
    if ($existingUser['is_verified'] == 0) {
        $verification_code = rand(100000, 999999);
        $update = $conn->prepare("UPDATE users SET verification_code=?, code_expires_at=DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id=?");
        $update->execute([$verification_code, $existingUser['id']]);
        sendVerificationEmail($data['email'], $verification_code);

        sendResponse(200, "Email already exists but not verified. Verification code resent.", [
            "user" => [
                "id" => $existingUser['id'],
                "name" => $existingUser['name'],
                "email" => $existingUser['email']
            ]
        ]);
    } else {
        sendResponse(409, "Email already exists", null, ["email" => "Already exists"]);
    }
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

$user_id = $conn->lastInsertId();

sendVerificationEmail($data['email'], $verification_code);

sendResponse(201, "Registration successful. Verification code sent to email.", [
    "user" => [
        "id" => $user_id,
        "name" => $data['name'],
        "email" => $data['email']
    ]
]);

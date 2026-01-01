<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'];
$name  = $data['name'];
$provider = $data['provider']; // The social login provider (Google or Facebook)
$social_id = $data['social_id'];

if (!$email || !$provider || !$social_id) {
    sendResponse(400, "Invalid data");
}

if (empty($name) || empty($provider)) {
    sendResponse(400, "Name and provider are required");
}

$token = bin2hex(random_bytes(25));

// Check if the user already exists in the database
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    if ($user['token'] !== null) {
        sendResponse(403, "User already logged in");
    }

    // Update the social ID if it is not already registered
    if ($provider == "google" && empty($user['google_id'])) {
        $conn->prepare("UPDATE users SET google_id=? WHERE id=?")
            ->execute([$social_id, $user['id']]);
    }

    if ($provider == "facebook" && empty($user['facebook_id'])) {
        $conn->prepare("UPDATE users SET facebook_id=? WHERE id=?")
            ->execute([$social_id, $user['id']]);
    }

    $stmt = $conn->prepare("UPDATE users SET token=? WHERE id=?");
    $stmt->execute([$token, $user['id']]);

    sendResponse(200, "User already exists, Login successfully", [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "token" => $token
    ]);
}

// Register a new user if they do not exist
$stmt = $conn->prepare(
    "INSERT INTO users (`name`,email,google_id,facebook_id,token,verification_code,is_verified)
     VALUES (?,?,?,?,?,?,?)"
);

$verification_code = rand(100000, 999999);

$stmt->execute([
    $name,
    $email,
    $provider == "google" ? $social_id : null,
    $provider == "facebook" ? $social_id : null,
    $token,
    $verification_code,
    1 // is_verified = 1
]);

$user_id = $conn->lastInsertId();

sendResponse(201, "User registered successfully", [
    "id" => $user_id,
    "name" => $name,
    "email" => $email,
    "token" => $token
]);

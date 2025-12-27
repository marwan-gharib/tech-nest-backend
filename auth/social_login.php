<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'];
$name  = $data['name'];
$provider = $data['provider']; // The social login provider (Google or Facebook)
$social_id = $data['social_id'];

if (!$email || !$provider || !$social_id) {
    echo json_encode(["status" => false, "message" => "Invalid data"]);
    exit;
}

if (empty($name) || empty($provider)) {
    echo json_encode([
        "status" => false,
        "message" => "Name and provider are required",
        "data" => null
    ]);
    exit;
}

$token = bin2hex(random_bytes(16));

// Check if the user already exists in the database
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Update the social ID if it is not already registered
    if ($provider == "google" && empty($user['google_id'])) {
        // Update Google ID
        $conn->prepare("UPDATE users SET google_id=? WHERE id=?")
             ->execute([$social_id,$user['id']]);
    }

    if ($provider == "facebook" && empty($user['facebook_id'])) {
        // Update Facebook ID
        $conn->prepare("UPDATE users SET facebook_id=? WHERE id=?")
             ->execute([$social_id,$user['id']]);
    }

    $stmt = $conn->prepare("UPDATE users SET token=? WHERE id=?");
    $stmt->execute([$token, $user['id']]);

    echo json_encode([
        "status" => true,
        "message" => "User already exists",
        "data" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "token" => $token
        ]
    ]);
    exit;
}

// Register a new user if they do not exist
$stmt = $conn->prepare(
    "INSERT INTO users (`name`,email,`role`,google_id,facebook_id,token)
     VALUES (?,?,?,?,?,?)"
);

$stmt->execute([
    $name,
    $email,
    "user",
    $provider == "google" ? $social_id : null,
    $provider == "facebook" ? $social_id : null,
    $token
]);

$user_id = $conn->lastInsertId();

echo json_encode([
    "status" => true,
    "message" => "User registered successfully",
    "data" => [
        "id" => $user_id,
        "name" => $name,
        "email" => $email,
        "token" => $token
    ]
]);

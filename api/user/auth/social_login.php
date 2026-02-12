<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";



try {
    // Parse and validate input
    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        sendResponse(400, "Invalid JSON input");
    }

    $email     = isset($data['email']) ? trim($data['email']) : null;
    $name      = isset($data['name']) ? trim($data['name']) : null;
    $provider  = isset($data['provider']) ? strtolower(trim($data['provider'])) : null;
    $social_id = isset($data['social_id']) ? trim($data['social_id']) : null;

    // Validate required fields
    $missingFields = [];
    if (!$email) $missingFields[] = 'email';
    if (!$name) $missingFields[] = 'name';
    if (!$provider) $missingFields[] = 'provider';
    if (!$social_id) $missingFields[] = 'social_id';

    if (!empty($missingFields)) {
        sendResponse(400, "Missing required fields", ["fields" => $missingFields]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(400, "Invalid email format");
    }

    if (!in_array($provider, ['google', 'facebook'])) {
        sendResponse(400, "Unsupported provider", ["provider" => $provider]);
    }

    require_once '../../../helpers/functions.php';

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update social id if not set
        if ($provider === "google") {
            $conn->prepare("UPDATE users SET google_id=?, facebook_id=? WHERE id=?")
                ->execute([$social_id, null, $user['id']]);
        }
        if ($provider === "facebook") {
            $conn->prepare("UPDATE users SET facebook_id=?, google_id=? WHERE id=?")
                ->execute([$social_id, null, $user['id']]);
        }

        // Generate new token and update
        $token = generateTokenWithExpiry($user['id'], 7, $conn);
        $token_expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
        $conn->prepare("UPDATE users SET token=?, token_expiry=? WHERE id=?")
            ->execute([$token, $token_expiry, $user['id']]);

        sendResponse(200, "Login successful", [
            "token" => $token,
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ]);
    }

    // Register new user (without token)
    $token = null;
    $token_expiry = null;

    $stmt = $conn->prepare(
        "INSERT INTO users (`name`, email, google_id, facebook_id, is_verified)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $name,
        $email,
        $provider === "google" ? $social_id : null,
        $provider === "facebook" ? $social_id : null,
        1 // is_verified = 1 for social login
    ]);

    $user_id = $conn->lastInsertId();

    // Now generate token and update user
    $token = generateTokenWithExpiry($user_id, 7, $conn);
    $token_expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
    $conn->prepare("UPDATE users SET token=?, token_expiry=? WHERE id=?")
        ->execute([$token, $token_expiry, $user_id]);

    sendResponse(201, "User registered successfully", [
        "token" => $token,
        "user" => [
            "id" => $user_id,
            "name" => $name,
            "email" => $email
        ]
    ]);
} catch (Exception $e) {
    sendResponse(500, "Internal Server Error", ["error" => $e->getMessage()]);
}

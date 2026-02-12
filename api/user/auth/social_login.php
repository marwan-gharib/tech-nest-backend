<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";



try {
    // Parse and validate input

    // استقبل البيانات من formData
    $email     = isset($_POST['email']) ? trim($_POST['email']) : null;
    $name      = isset($_POST['name']) ? trim($_POST['name']) : null;
    $provider  = isset($_POST['provider']) ? strtolower(trim($_POST['provider'])) : null;
    $social_id = isset($_POST['social_id']) ? trim($_POST['social_id']) : null;

    // معالجة صورة البروفايل
    // صورة البروفايل مطلوبة مع فحص كامل
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== 0) {
        sendResponse(400, "Profile image is required", null, ["profile_image" => "Image is required"]);
    }
    $upload_dir = '../../../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        sendResponse(400, "Invalid image type", null, ["profile_image" => "Allowed types: jpg, jpeg, png, webp"]);
    }
    if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) { // 2MB limit
        sendResponse(400, "Image size too large (max 2MB)", null, ["profile_image" => "Max size 2MB"]);
    }
    $image_info = getimagesize($_FILES['profile_image']['tmp_name']);
    if ($image_info === false) {
        sendResponse(400, "Uploaded file is not a valid image", null, ["profile_image" => "Invalid image file"]);
    }
    $hash = hash_file('sha256', $_FILES['profile_image']['tmp_name']);
    $existing = glob($upload_dir . $hash . '.*');
    if ($existing && count($existing) > 0) {
        $profile_image_path = 'uploads/' . basename($existing[0]);
    } else {
        $image_name = $hash . "." . $ext;
        $profile_image_path = "uploads/" . $image_name;
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], '../../../' . $profile_image_path)) {
            sendResponse(500, "Failed to upload image");
        }
    }

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

    // Check if user exists

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update social id and profile image if not set
        if ($provider === "google") {
            $conn->prepare("UPDATE users SET google_id=?, facebook_id=?, profile_image=? WHERE id=?")
                ->execute([$social_id, null, $profile_image_path ? $profile_image_path : $user['profile_image'], $user['id']]);
        }
        if ($provider === "facebook") {
            $conn->prepare("UPDATE users SET facebook_id=?, google_id=?, profile_image=? WHERE id=?")
                ->execute([$social_id, null, $profile_image_path ? $profile_image_path : $user['profile_image'], $user['id']]);
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
                "email" => $user['email'],
                "image_url" => $profile_image_path ? $profile_image_path : $user['profile_image']
            ]
        ]);
    }

    // Register new user (without token)
    $token = null;
    $token_expiry = null;

    $stmt = $conn->prepare(
        "INSERT INTO users (`name`, email, google_id, facebook_id, is_verified, profile_image)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $name,
        $email,
        $provider === "google" ? $social_id : null,
        $provider === "facebook" ? $social_id : null,
        1, // is_verified = 1 for social login
        $profile_image_path
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
            "email" => $email,
            "image_url" => $profile_image_path
        ]
    ]);
} catch (Exception $e) {
    sendResponse(500, "Internal Server Error", ["error" => $e->getMessage()]);
}

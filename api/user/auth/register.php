<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";


$name = isset($_POST['name']) ? trim($_POST['name']) : null;
$email = isset($_POST['email']) ? trim($_POST['email']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;

if (empty($name) || empty($email) || empty($password)) {
    sendResponse(400, "All fields are required", null, ["fields" => "Missing required fields"]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Invalid email format", null, ["email" => "Invalid format"]);
}

$profile_image_path = null;
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

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existingUser) {
    if ($existingUser['is_verified'] == 0) {
        $verification_code = rand(100000, 999999);
        $update = $conn->prepare("UPDATE users SET verification_code=?, code_expires_at=DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id=?");
        $update->execute([$verification_code, $existingUser['id']]);
        sendVerificationEmail($email, $verification_code);

        sendResponse(200, "Email already exists but not verified. Verification code resent.", [
            "user" => [
                "id" => $existingUser['id'],
                "name" => $existingUser['name'],
                "email" => $existingUser['email'],
                "image_url" => $existingUser['profile_image'] ?? null
            ]
        ]);
    } else {
        sendResponse(409, "Email already exists", null, ["email" => "Already exists"]);
    }
}


$verification_code = rand(100000, 999999);

$stmt = $conn->prepare(
    "INSERT INTO users 
    (`name`,email,`password`,verification_code,is_verified,code_expires_at,token,profile_image)
    VALUES (?,?,?,?,0,DATE_ADD(NOW(),INTERVAL 5 MINUTE),NULL,?)"
);
$stmt->execute([
    $name,
    $email,
    password_hash($password, PASSWORD_BCRYPT),
    $verification_code,
    $profile_image_path
]);

$user_id = $conn->lastInsertId();

sendVerificationEmail($email, $verification_code);

sendResponse(201, "Registration successful. Verification code sent to email.", [
    "user" => [
        "id" => $user_id,
        "name" => $name,
        "email" => $email,
        "image_url" => $profile_image_path
    ]
]);

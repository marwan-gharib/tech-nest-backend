<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";


$name     = isset($_POST['name'])     ? trim($_POST['name'])     : null;
$email    = isset($_POST['email'])    ? trim($_POST['email'])    : null;
$password = isset($_POST['password']) ? $_POST['password']       : null;

if (empty($name) || empty($email) || empty($password)) {
    sendResponse(400, t('all_fields_required'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, t('invalid_email'));
}

$profile_image_path = null;
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== 0) {
    sendResponse(400, t('image_required'));
}
$upload_dir = '../../../uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
$ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $allowed)) {
    sendResponse(400, t('invalid_image_type'));
}
if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
    sendResponse(400, t('image_too_large'));
}
$image_info = getimagesize($_FILES['profile_image']['tmp_name']);
if ($image_info === false) {
    sendResponse(400, t('invalid_image_file'));
}
$hash = hash_file('sha256', $_FILES['profile_image']['tmp_name']);
$existing = glob($upload_dir . $hash . '.webp');
if ($existing && count($existing) > 0) {
    $profile_image_path = 'uploads/' . basename($existing[0]);
} else {
    $profile_image_path = saveImageAsWebp($_FILES['profile_image']['tmp_name'], $upload_dir, $hash);
    if (!$profile_image_path) {
        sendResponse(500, t('image_upload_failed'));
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existingUser) {
    if ($existingUser['is_verified'] == 0) {
        $verification_code = rand(100000, 999999);
        $update = $conn->prepare("UPDATE users SET name=?, password=?, profile_image=?, verification_code=?, code_expires_at=DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id=?");
        $update->execute([
            $name,
            password_hash($password, PASSWORD_BCRYPT),
            $profile_image_path,
            $verification_code,
            $existingUser['id']
        ]);
        sendVerificationEmail($email, $verification_code);

        sendResponse(200, t('email_exists_not_verified'), [
            "user" => [
                "id"        => $existingUser['id'],
                "name"      => $name,
                "email"     => $email,
                "image_url" => $profile_image_path
            ]
        ]);
    } else {
        sendResponse(409, t('email_exists'));
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

sendResponse(201, t('register_success'), [
    "user" => [
        "id"        => $user_id,
        "name"      => $name,
        "email"     => $email,
        "image_url" => $profile_image_path
    ]
]);

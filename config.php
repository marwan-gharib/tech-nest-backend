<?php
// Set the content type to JSON
header("Content-Type: application/json");

// Create a new PDO connection to the MySQL database
$db_host = 'localhost';
$db_name = 'ecommerce_db';
$db_user = 'root';
$db_pass = '';

$conn = new PDO(
    "mysql:host=$db_host;dbname=$db_name",
    $db_user,
    $db_pass,
);

// Set PDO to throw exceptions on errors
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Function to check if the user is an admin
function checkAdmin($conn, $user_id)
{
    // Query to get the role of the user
    $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the user does not exist or is not an admin, deny access
    if (!$user || $user['role'] !== 'admin') {
        echo json_encode([
            "status" => false,
            "message" => "Access denied (Admin only)"
        ]);
        exit;
    }
}

// Function to validate tokens for protected endpoints
function validateToken($conn, $token)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE token=?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid or missing token",
            "data" => null
        ]);
        exit;
    }

    return $user;
}


require_once __DIR__ . "/PHPMailer/Exception.php";
require_once __DIR__ . "/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $code)
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'technest1485@gmail.com';
        $mail->Password   = 'ectxrpmxtvskmlmg';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('technest1485@gmail.com', 'Tech Nest');
        $mail->addAddress($email);

        $mail->Subject = 'Email Verification Code';
        $mail->Body    = "Your verification code is: $code\nValid for 5 minutes.";

        $mail->send();
    } catch (Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "Email already exists",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

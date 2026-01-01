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

        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'technest1485@gmail.com';
        $mail->Password   = 'ectxrpmxtvskmlmg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & receiver
        $mail->setFrom('technest1485@gmail.com', 'Tech Nest');
        $mail->addAddress($email);

        // Email format
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Tech Nest';

        // HTML Body
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; background:#f4f6f8; padding:20px'>
            <div style='max-width:500px; margin:auto; background:#ffffff; border-radius:10px; overflow:hidden'>
                
                <div style='background:#0d6efd; padding:20px; text-align:center; color:white'>
                    <h1 style='margin:0'>Tech Nest</h1>
                </div>

                <div style='padding:30px; text-align:center'>
                    <h2 style='color:#333'>Email Verification</h2>
                    <p style='color:#555; font-size:16px'>
                        Use the verification code below to confirm your email address.
                    </p>

                    <div style='margin:30px 0'>
                        <span style='font-size:32px; letter-spacing:6px; font-weight:bold; color:#0d6efd'>
                            $code
                        </span>
                    </div>

                    <p style='color:#888; font-size:14px'>
                        This code is valid for <b>5 minutes</b>.
                    </p>

                    <hr style='margin:30px 0'>

                    <p style='font-size:12px; color:#aaa'>
                        If you did not request this, please ignore this email.
                    </p>
                </div>

            </div>
        </div>
        ";

        // Fallback text version
        $mail->AltBody = "Your verification code is: $code (Valid for 5 minutes)";

        $mail->send();

    } catch (Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "Failed to send verification email",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}


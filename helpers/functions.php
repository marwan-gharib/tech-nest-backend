<?php

require_once __DIR__ . "/../PHPMailer/Exception.php";
require_once __DIR__ . "/../PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendResponse($status, $message, $data = null)
{
    http_response_code($status);
    $response = [
        "status" => $status,
        "message" => $message,
        "data" => $data ?? null,
    ];

    // Remove null fields for cleaner response
    foreach (["data"] as $key) {
        if ($response[$key] === null) {
            unset($response[$key]);
        }
    }

    $json = json_encode($response);
    if ($json === false) {
        http_response_code(500);
        echo json_encode([
            "status" => 500,
            "message" => "JSON Encoding Error: " . json_last_error_msg()
        ]);
        exit;
    }

    echo $json;
    exit;
}

/* Validate User Token */
function validateToken($conn)
{
    $headers = getallheaders();
    $authHeader = $headers['Token'] ?? null;

    if (!$authHeader) {
        sendResponse(401, "Token required");
    }

    $token = str_replace('Bearer ', '', $authHeader);

    $stmt = $conn->prepare("SELECT * FROM users WHERE token = ? AND token_expiry > NOW() AND is_verified = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendResponse(401, "Invalid, expired token or account not verified");
    }

    return $user;
}

/* Validate Admin Token */
function validateAdminToken($conn)
{
    $headers = getallheaders();
    $authHeader = $headers['Token'] ?? null;

    if (!$authHeader) {
        sendResponse(401, "Admin Token required");
    }

    $token = str_replace('Bearer ', '', $authHeader);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE token = ? AND token_expiry > NOW()");
    $stmt->execute([$token]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        sendResponse(401, "Invalid or expired admin token");
    }

    return $admin;
}

function sendVerificationEmail($email, $code)
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'technest1485@gmail.com';
        $mail->Password   = 'ectxrpmxtvskmlmg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('technest1485@gmail.com', 'Tech Nest');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Tech Nest';

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

        $mail->AltBody = "Your verification code is: $code (Valid for 5 minutes)";

        $mail->send();
    } catch (Exception $e) {
        sendResponse(500, "Failed to send verification email");
    }
}

function sendForgotPasswordEmail($email, $code)
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'technest1485@gmail.com';
        $mail->Password   = 'ectxrpmxtvskmlmg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('technest1485@gmail.com', 'Tech Nest');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - Tech Nest';

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; background:#f4f6f8; padding:20px'>
            <div style='max-width:500px; margin:auto; background:#ffffff; border-radius:10px; overflow:hidden'>
                
                <div style='background:#dc3545; padding:20px; text-align:center; color:white'>
                    <h1 style='margin:0'>Tech Nest</h1>
                </div>

                <div style='padding:30px; text-align:center'>
                    <h2 style='color:#333'>Password Reset Request</h2>
                    <p style='color:#555; font-size:16px'>
                        You requested to reset your password. Use the code below to proceed.
                    </p>

                    <div style='margin:30px 0'>
                        <span style='font-size:32px; letter-spacing:6px; font-weight:bold; color:#dc3545'>
                            $code
                        </span>
                    </div>

                    <p style='color:#888; font-size:14px'>
                        This code is valid for <b>5 minutes</b>.
                    </p>

                    <hr style='margin:30px 0'>

                    <p style='font-size:12px; color:#aaa'>
                        If you did not request this, please ignore this email or contact support.
                    </p>
                </div>

            </div>
        </div>
        ";

        $mail->AltBody = "Your password reset code is: $code (Valid for 5 minutes)";

        $mail->send();
    } catch (Exception $e) {
        sendResponse(500, "Failed to send password reset email");
    }
}

function generateTokenWithExpiry($id, $expiryDays, $conn, $table = 'users')
{
    $token = bin2hex(random_bytes(25));
    $expiryTime = date('Y-m-d H:i:s', strtotime("+$expiryDays days"));

    $stmt = $conn->prepare("UPDATE $table SET token = ?, token_expiry = ? WHERE id = ?");
    $stmt->execute([$token, $expiryTime, $id]);

    return $token;
}

function isImageUsed($conn, $imagePath)
{
    $stmt = $conn->prepare("SELECT id FROM products WHERE image_url = ? LIMIT 1");
    $stmt->execute([$imagePath]);
    if ($stmt->fetch()) return true;

    $stmt = $conn->prepare("SELECT id FROM categories WHERE image_url = ? LIMIT 1");
    $stmt->execute([$imagePath]);
    if ($stmt->fetch()) return true;

    return false;
}

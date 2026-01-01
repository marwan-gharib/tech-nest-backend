<?php

require_once __DIR__ . "/../PHPMailer/Exception.php";
require_once __DIR__ . "/../PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Standardized JSON Response
 */
function sendResponse($status, $message, $data = null)
{
    http_response_code($status);
    $response = [
        "status" => $status,
        "message" => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    // Ensure data is null if not provided in success 200/201 (optional, based on previous reqs)
    // Actually user asked for {status, message} only for errors.
    // For success, usually {status, message, data}.
    // If status is error, remove data.
    if ($status >= 400) {
        unset($response['data']);
    } else if ($data === null) {
         // Should we add data: null for success?
         // Previous code did: "data" => null.
         // Let's keep consistency.
         $response['data'] = null;
    }

    echo json_encode($response);
    exit;
}

/**
 * Check if the user is an admin (Deprecated - use validateAdminToken)
 */
function checkAdmin($conn, $user_id)
{
    // This function is deprecated as we now use separate tables.
    // Logic moved to validateAdminToken
}

/**
 * Validate User Token
 */
function validateToken($conn, $token)
{
    if (!$token) {
        sendResponse(401, "Token required");
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE token=?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendResponse(401, "Invalid or missing token");
    }

    return $user;
}

/**
 * Validate Admin Token
 */
function validateAdminToken($conn, $token)
{
    if (!$token) {
        sendResponse(401, "Admin Token required");
    }

    $stmt = $conn->prepare("SELECT * FROM admins WHERE token=?");
    $stmt->execute([$token]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        sendResponse(401, "Invalid or missing admin token");
    }

    return $admin;
}

/**
 * Send Verification Email
 */
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
        sendResponse(500, "Failed to send verification email");
    }
}

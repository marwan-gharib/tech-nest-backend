<?php
// Set the content type to JSON
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, token, Token, Authorization, lang, Accept-Language");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load language helper first (must be before functions.php)
require_once __DIR__ . "/../helpers/lang.php";

$db_host = 'localhost';
$db_name = 'ecommerce_db';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => 500,
        "message" => t('database_error')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

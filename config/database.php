<?php
// Set the content type to JSON
header("Content-Type: application/json");

// Create a new PDO connection to the MySQL database
$db_host = 'localhost';
$db_name = 'ecommerce_db';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO(
        "mysql:host=$db_host;dbname=$db_name",
        $db_user,
        $db_pass,
    );
    // Set PDO to throw exceptions on errors
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Database connection failed"
    ]);
    exit;
}

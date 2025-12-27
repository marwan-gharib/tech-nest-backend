<?php
// Set the content type to JSON
header("Content-Type: application/json");

// Create a new PDO connection to the MySQL database
$conn = new PDO(
    "mysql:host=localhost;dbname=ecommerce_db",
    "root",
    ""
);

// Set PDO to throw exceptions on errors
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Function to check if the user is an admin
function checkAdmin($conn, $user_id) {
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

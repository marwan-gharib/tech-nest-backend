<?php
header("Content-Type: application/json");

$conn = new PDO(
    "mysql:host=localhost;dbname=ecommerce_db",
    "root",
    ""
);

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* 🔐 function تتأكد إن Admin */
function checkAdmin($conn, $user_id) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        echo json_encode([
            "status"=>false,
            "message"=>"Access denied (Admin only)"
        ]);
        exit;
    }
}

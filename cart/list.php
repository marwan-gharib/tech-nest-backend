<?php
include "../config.php";

$user_id = $_GET['user_id'];

$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=?");
try {
    $stmt->execute([$user_id]);

    echo json_encode([
        "status" => true,
        "message" => "Cart items retrieved successfully",
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to retrieve cart items",
        "error" => $e->getMessage()
    ]);
}

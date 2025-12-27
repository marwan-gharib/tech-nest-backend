<?php
include "../config.php";

$user_id = $_GET['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=?");
$stmt->execute([$user_id]);

echo json_encode([
    "status" => true,
    "message" => "Orders retrieved successfully",
    "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);

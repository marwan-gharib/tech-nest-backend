<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$user = validateToken($conn, $data['token'] ?? null);

$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=?");
$stmt->execute([$user['id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode([
    "status" => true,
    "message" => "Cart items retrieved successfully",
    "data" => $data
]);

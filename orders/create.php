<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("INSERT INTO orders (user_id,total) VALUES (?,?)");
$stmt->execute([$data['user_id'],$data['total']]);

echo json_encode([
    "status" => true,
    "message" => "Order created successfully",
    "data" => null
]);

<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

checkAdmin($conn, $data['user_id']);

$stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
$stmt->execute([$data['name']]);

echo json_encode([
    "status" => true,
    "message" => "Category added successfully",
    "data" => null
]);

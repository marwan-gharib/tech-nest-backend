<?php
include "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

checkAdmin($conn, $data['user_id']);

$stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
$stmt->execute([$data['category_id']]);

echo json_encode([
    "status" => true,
    "message" => "Category deleted successfully",
    "data" => null
]);

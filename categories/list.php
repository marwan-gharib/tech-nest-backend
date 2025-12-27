<?php
include "../config.php";

$stmt = $conn->query("SELECT * FROM categories");
echo json_encode([
    "status" => true,
    "message" => "Categories retrieved successfully",
    "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);

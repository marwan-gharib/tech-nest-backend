<?php
include "../config.php";

$stmt = $conn->query("SELECT * FROM products");
echo json_encode([
    "status" => true,
    "message" => "Products retrieved successfully",
    "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);

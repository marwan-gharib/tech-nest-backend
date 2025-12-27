<?php
include "../config.php";

$stmt = $conn->query("SELECT * FROM products");
try {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Products retrieved successfully",
        "data" => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to retrieve products",
        "error" => $e->getMessage()
    ]);
}

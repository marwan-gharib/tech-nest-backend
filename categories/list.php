<?php
include "../config.php";

$stmt = $conn->query("SELECT * FROM categories");
try {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Categories retrieved successfully",
        "data" => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to retrieve categories",
        "error" => $e->getMessage()
    ]);
}

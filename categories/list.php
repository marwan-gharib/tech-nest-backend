<?php
include "../config.php";

$stmt = $conn->query("SELECT * FROM categories");
try {
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "status" => 200,
        "message" => "Categories retrieved successfully",
        "data" => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => 500,
        "message" => "Failed to retrieve categories"
    ]);
}

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

$q = isset($_GET['search_query']) ? trim($_GET['search_query']) : null;

if ($q === null || $q === '') {
    sendResponse(400, "Search query is required", null);
}

if (strlen($q) < 2) {
    sendResponse(400, "Search query must be at least 2 characters", null);
}

$q = htmlspecialchars($q, ENT_QUOTES, 'UTF-8');

try {

    $stmt = $conn->prepare("
        SELECT DISTINCT name
        FROM products
        WHERE name LIKE ?
        ORDER BY name ASC
        LIMIT 10
    ");

    $stmt->execute(["%$q%"]);

    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$results) {
        sendResponse(200, "No suggestions found", []);
    }

    sendResponse(200, "Suggestions retrieved successfully", $results);
} catch (Exception $e) {
    sendResponse(500, "Failed to fetch suggestions", null, [
        "error" => $e->getMessage()
    ]);
}

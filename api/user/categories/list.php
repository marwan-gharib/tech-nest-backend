<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);


$stmt = $conn->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendResponse(200, "Categories retrieved successfully", $categories);

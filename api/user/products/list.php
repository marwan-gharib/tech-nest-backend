<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendResponse(200, "Products retrieved successfully", $products);

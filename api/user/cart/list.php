<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=?");
$stmt->execute([$user['id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

sendResponse(200, "Cart items retrieved successfully", $cartItems);

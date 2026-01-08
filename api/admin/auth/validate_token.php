<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? $_POST['token'] ?? null;

$admin = validateAdminToken($conn, $token);

// If validateAdminToken doesn't exit, the token is valid
sendResponse(200, "Token is valid");

<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$user = validateToken($conn);

sendResponse(200, "Token is valid");

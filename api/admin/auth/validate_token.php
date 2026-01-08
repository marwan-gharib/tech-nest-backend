<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

// If validateAdminToken doesn't exit, the token is valid
sendResponse(200, "Token is valid");

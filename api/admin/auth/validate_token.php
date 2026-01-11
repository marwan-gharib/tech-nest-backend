<?php
include "../../../config/database.php";
include "../../../helpers/functions.php";

$admin = validateAdminToken($conn);

sendResponse(200, "Token is valid");

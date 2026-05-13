<?php
header("Content-Type: application/json");

echo json_encode([
    "status"        => 200,
    "message"       => "Welcome to Tech Nest Backend API",
    "version"       => "1.0.0",
    "documentation" => "Please refer to README.md for full documentation.",
    "structure"     => [
        "Admin" => [
            "Auth" => [
                "Login"          => "api/admin/auth/login.php",
                "Logout"         => "api/admin/auth/logout.php",
                "Validate Token" => "api/admin/auth/validate_token.php",
            ],
            "Products" => [
                "List"   => "api/admin/products/list.php",
                "Add"    => "api/admin/products/add.php",
                "Update" => "api/admin/products/update.php",
                "Delete" => "api/admin/products/delete.php",
            ],
            "Categories" => [
                "List"   => "api/admin/categories/list.php",
                "Add"    => "api/admin/categories/add.php",
                "Update" => "api/admin/categories/update.php",
                "Delete" => "api/admin/categories/delete.php",
            ],
            "Orders" => [
                "Update Status" => "api/admin/orders/update_status.php",
            ],
            "Notifications" => [
                "FCM Examples" => "api/admin/notifications/fcm_examples.php",
            ],
        ],
        "User" => [
            "Auth" => [
                "Register"       => "api/user/auth/register.php",
                "Verify Email"   => "api/user/auth/verify_email.php",
                "Login"          => "api/user/auth/login.php",
                "Logout"         => "api/user/auth/logout.php",
                "Forget Password"=> "api/user/auth/forget_password.php",
                "Reset Password" => "api/user/auth/reset_password.php",
            ],
            "Products" => [
                "List"                  => "api/user/products/list.php",
                "Get Product"           => "api/user/products/get_product.php",
                "Searching Suggestions" => "api/user/products/searching_suggestions.php",
            ],
            "Categories" => [
                "List" => "api/user/categories/list.php",
            ],
            "Cart" => [
                "List"            => "api/user/cart/list.php",
                "Add"             => "api/user/cart/add.php",
                "Update Quantity" => "api/user/cart/update_quantity.php",
                "Remove"          => "api/user/cart/remove.php",
                "Count"           => "api/user/cart/count.php",
            ],
            "Orders" => [
                "Create"  => "api/user/orders/create.php",
                "List"    => "api/user/orders/list.php",
                "Details" => "api/user/orders/details.php",
                "Cancel"  => "api/user/orders/cancel.php",
            ],
            "Notifications" => [
                "Save FCM Token"        => "api/user/notifications/save_fcm_token.php",
                "Get Notifications"     => "api/user/notifications/get_notifications.php",
                "Mark Notification Read"=> "api/user/notifications/mark_notification_read.php",
            ],
        ],
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
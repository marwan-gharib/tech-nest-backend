<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => 200,
    "message" => "Welcome to the E-commerce Backend API (New Structure)",
    "documentation" => "Please refer to README.md for full documentation.",
    "structure" => [
        "Admin" => [
            "Auth" => [
                "Login" => "api/admin/auth/login.php",
                "Logout" => "api/admin/auth/logout.php",
                "Validate Token" => "api/admin/auth/validate_token.php"
            ],
            "Products" => [
                "List" => "api/admin/products/list.php",
                "Add" => "api/admin/products/add.php",
                "Update" => "api/admin/products/update.php",
                "Delete" => "api/admin/products/delete.php"
            ],
            "Categories" => [
                "List" => "api/admin/categories/list.php",
                "Add" => "api/admin/categories/add.php",
                "Update" => "api/admin/categories/update.php",
                "Delete" => "api/admin/categories/delete.php"
            ],
            "Orders" => [
                "Update Status" => "api/admin/orders/update_status.php"
            ]
        ],
        "User" => [
            "Auth" => [
                "Register" => "api/user/auth/register.php",
                "Login" => "api/user/auth/login.php",
                "Social Login" => "api/user/auth/social_login.php",
                "Verify Email" => "api/user/auth/verify_email.php",
                "Validate Token" => "api/user/auth/validate_token.php",
                "Logout" => "api/user/auth/logout.php"
            ],
            "Products" => [
                "List" => "api/user/products/list.php"
            ],
            "Categories" => [
                "List" => "api/user/categories/list.php"
            ],
            "Cart" => [
                "Add" => "api/user/cart/add.php",
                "List" => "api/user/cart/list.php",
                "Update Quantity" => "api/user/cart/update_quantity.php",
                "Remove" => "api/user/cart/remove.php"
            ],
            "Orders" => [
                "Create" => "api/user/orders/create.php",
                "List" => "api/user/orders/list.php",
                "Details" => "api/user/orders/details.php",
                "Cancel" => "api/user/orders/cancel.php"
            ]
        ]
    ]
]);

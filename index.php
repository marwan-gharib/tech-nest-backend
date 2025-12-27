<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => true,
    "message" => "Welcome to the E-commerce Backend API",
    "endpoints" => [
        "auth/login.php" => "Login endpoint",
        "auth/register.php" => "Register endpoint",
        "cart/add.php" => "Add to cart",
        "cart/list.php" => "List cart items",
        "cart/remove.php" => "Remove from cart",
        "categories/add.php" => "Add category",
        "categories/list.php" => "List categories",
        "categories/delete.php" => "Delete category",
        "orders/create.php" => "Create order",
        "orders/list.php" => "List orders",
        "products/add.php" => "Add product",
        "products/list.php" => "List products",
        "products/delete.php" => "Delete product"
    ]
]);
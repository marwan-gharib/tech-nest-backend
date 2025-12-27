# E-Commerce Backend API

This is the backend for an e-commerce application. It provides various endpoints for managing users, products, categories, orders, and carts. The backend is built using PHP and uses a MySQL database.

## Table of Contents
- [Setup](#setup)
- [Endpoints](#endpoints)
  - [Authentication](#authentication)
  - [Cart](#cart)
  - [Categories](#categories)
  - [Orders](#orders)
  - [Products](#products)
- [Error Handling](#error-handling)

## Setup
1. Clone the repository.
2. Import the `ecommerce_db` SQL schema into your MySQL database.
3. Update the database credentials in `config.php`.
4. Start a local PHP server or deploy to a web server.

## Endpoints

### Authentication

#### `POST /auth/login.php`
- **Description**: Logs in a user.
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "password123"
  }
  ```
- **Response**:
  - **Success**:
    ```json
    {
      "status": true,
      "message": "Login successful",
      "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "user"
      }
    }
    ```
  - **Failure**:
    ```json
    {
      "status": false,
      "message": "Invalid email or password",
      "data": null
    }
    ```

#### `POST /auth/register.php`
- **Description**: Registers a new user.
- **Request Body**:
  ```json
  {
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123"
  }
  ```
- **Response**:
  - **Success**:
    ```json
    {
      "status": true,
      "message": "Registration successful",
      "data": null
    }
    ```
  - **Failure**:
    ```json
    {
      "status": false,
      "message": "Failed to register user",
      "error": "Error details"
    }
    ```

### Social Login

#### `POST /auth/social_login.php`
- **Description**: Authenticates a user using social login (Google or Facebook). If the user does not exist, they are registered automatically.
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "name": "John Doe",
    "provider": "google",
    "social_id": "1234567890"
  }
  ```
  - `email`: The email address of the user.
  - `name`: The name of the user.
  - `provider`: The social login provider (e.g., `google` or `facebook`).
  - `social_id`: The unique identifier provided by the social login provider.

- **Response**:
  - **Success** (User already exists):
    ```json
    {
      "status": true,
      "message": "User already exists",
      "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "user"
      }
    }
    ```
  - **Success** (New user registered):
    ```json
    {
      "status": true,
      "message": "User registered successfully",
      "data": {
        "id": 2,
        "name": "John Doe",
        "email": "user@example.com"
      }
    }
    ```
  - **Failure**:
    ```json
    {
      "status": false,
      "message": "Invalid data"
    }
    ```

- **Differences from Regular Login**:
  1. **No Password Required**: Social login does not require a password. Instead, it relies on the `social_id` provided by the social login provider.
  2. **Automatic Registration**: If the user does not exist, they are automatically registered with the provided `name`, `email`, and `social_id`.
  3. **Provider-Specific IDs**: The `social_id` is stored separately for each provider (e.g., `google_id` or `facebook_id`).

- **Use Case**: Social login is useful for users who prefer to log in using their Google or Facebook accounts instead of creating a new password for the application.

### Cart

#### `POST /cart/add.php`
- **Description**: Adds an item to the cart.
- **Request Body**:
  ```json
  {
    "user_id": 1,
    "product_id": 101,
    "quantity": 2
  }
  ```
- **Response**:
  - **Success**:
    ```json
    {
      "status": true,
      "message": "Item added to cart successfully",
      "data": null
    }
    ```
  - **Failure**:
    ```json
    {
      "status": false,
      "message": "Failed to add item to cart",
      "error": "Error details"
    }
    ```

#### `GET /cart/list.php?user_id=1`
- **Description**: Retrieves all items in the user's cart.
- **Response**:
  - **Success**:
    ```json
    {
      "status": true,
      "message": "Cart items retrieved successfully",
      "data": [
        {
          "id": 1,
          "user_id": 1,
          "product_id": 101,
          "quantity": 2
        }
      ]
    }
    ```
  - **Failure**:
    ```json
    {
      "status": false,
      "message": "Failed to retrieve cart items",
      "error": "Error details"
    }
    ```

#### `POST /cart/remove.php`
- **Description**: Removes an item from the cart.
- **Request Body**:
  ```json
  {
    "cart_id": 1,
    "user_id": 1
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Item removed from cart successfully",
    "data": null
  }
  ```

### Categories

#### `POST /categories/add.php`
- **Description**: Adds a new category (Admin only).
- **Request Body**:
  ```json
  {
    "user_id": 1,
    "name": "Electronics"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Category added successfully",
    "data": null
  }
  ```

#### `GET /categories/list.php`
- **Description**: Retrieves all categories.
- **Response**:
  ```json
  {
    "status": true,
    "message": "Categories retrieved successfully",
    "data": [
      {
        "id": 1,
        "name": "Electronics"
      }
    ]
  }
  ```

### Products

#### `POST /products/add.php`
- **Description**: Adds a new product (Admin only).
- **Request Body**:
  ```json
  {
    "user_id": 1,
    "name": "Laptop",
    "description": "A high-end laptop",
    "price": 1500,
    "stock": 12,
    "category_id": 1,
    "image_url": "http://example.com/laptop.jpg"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Product added successfully",
    "data": null
  }
  ```

#### `GET /products/list.php`
- **Description**: Retrieves all products.
- **Response**:
  ```json
  {
    "status": true,
    "message": "Products retrieved successfully",
    "data": [
      {
        "id": 1,
        "name": "Laptop",
        "description": "A high-end laptop",
        "price": 1500,
        "stock": 12,
        "category_id": 1,
        "image_url": "http://example.com/laptop.jpg"
      }
    ]
  }
  ```

## Error Handling
- All endpoints return a `status` field indicating success (`true`) or failure (`false`).
- In case of errors, a `message` field provides details about the error.
- Example error response:
  ```json
  {
    "status": false,
    "message": "Access denied (Admin only)",
    "data": null
  }
  ```

### Index Endpoint

#### `GET /index.php`
- **Description**: Entry point for the API.
- **Response**:
  ```json
  {
    "status": true,
    "message": "Welcome to the E-commerce Backend API",
    "endpoints": {
      "auth/login.php": "Login endpoint",
      "auth/register.php": "Register endpoint",
      "cart/add.php": "Add to cart",
      "cart/list.php": "List cart items",
      "cart/remove.php": "Remove from cart",
      "categories/add.php": "Add category",
      "categories/list.php": "List categories",
      "categories/delete.php": "Delete category",
      "orders/create.php": "Create order",
      "orders/list.php": "List orders",
      "products/add.php": "Add product",
      "products/list.php": "List products",
      "products/delete.php": "Delete product"
    }
  }
  ```

### Updates

- **Validation**: Added input validation for various endpoints to ensure data integrity.
- **Security**: Updated `config.php` to use environment variables for database credentials.
- **Endpoints Updated**:
  - `auth/register.php`: Validates `name` and `password` fields.
  - `auth/login.php`: Validates email format.
  - `auth/social_login.php`: Validates `name` and `provider` fields.
  - `categories/update.php`: Validates `name` field.
  - `products/update.php`: Validates `name`, `description`, and `price` fields.
  - `cart/add.php`: Validates `quantity` field.

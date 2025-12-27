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
  ```json
  {
    "status": true,
    "message": "Registration successful",
    "data": null
  }
  ```

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
  ```json
  {
    "status": true,
    "message": "Item added to cart successfully",
    "data": null
  }
  ```

#### `GET /cart/list.php?user_id=1`
- **Description**: Retrieves all items in the user's cart.
- **Response**:
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

### Orders

#### `POST /orders/create.php`
- **Description**: Creates a new order.
- **Request Body**:
  ```json
  {
    "user_id": 1,
    "total": 200.5
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Order created successfully",
    "data": null
  }
  ```

#### `GET /orders/list.php?user_id=1`
- **Description**: Retrieves all orders for a user.
- **Response**:
  ```json
  {
    "status": true,
    "message": "Orders retrieved successfully",
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "total": 200.5
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

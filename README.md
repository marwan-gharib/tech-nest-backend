# E-Commerce Backend API Documentation

This document provides detailed information about the endpoints available in the E-Commerce Backend API, including their usage, request formats, and responses.

## Table of Contents
1. [Authentication](#authentication)
   - [Register](#register)
   - [Login](#login)
2. [Products](#products)
   - [Add Product](#add-product)
   - [Update Product](#update-product)
   - [Delete Product](#delete-product)
   - [List Products](#list-products)
3. [Categories](#categories)
   - [Add Category](#add-category)
   - [Update Category](#update-category)
   - [Delete Category](#delete-category)
   - [List Categories](#list-categories)
4. [Cart](#cart)
   - [Add to Cart](#add-to-cart)
   - [List Cart Items](#list-cart-items)
   - [Remove from Cart](#remove-from-cart)

---

## Authentication

### Register
**Endpoint**: `POST /auth/register.php`

**Description**: Registers a new user, sends a verification code to their email, and returns a token.

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Registration successful. Verification code sent to email.",
  "data": {
    "name": "John Doe",
    "email": "john.doe@example.com",
    "token": "<generated_token>"
  }
}
```

### Login
**Endpoint**: `POST /auth/login.php`

**Description**: Logs in a user and returns a token.

**Request Body**:
```json
{
  "email": "john.doe@example.com",
  "password": "password123"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "role": "user",
    "token": "<generated_token>"
  }
}
```

---

## Products

### Add Product
**Endpoint**: `POST /products/add.php`

**Description**: Adds a new product. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "name": "Product Name",
  "description": "Product Description",
  "price": 100.0,
  "stock": 50,
  "category_id": 1,
  "img": "<base64_encoded_image>"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Product added successfully",
  "data": null
}
```

### Update Product
**Endpoint**: `POST /products/update.php`

**Description**: Updates an existing product. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1,
  "name": "Updated Product Name",
  "description": "Updated Description",
  "price": 120.0,
  "stock": 40,
  "img": "<base64_encoded_image>"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Product updated successfully",
  "data": null
}
```

### Delete Product
**Endpoint**: `POST /products/delete.php`

**Description**: Deletes a product. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1
}
```

**Response**:
```json
{
  "status": true,
  "message": "Product deleted successfully",
  "data": null
}
```

### List Products
**Endpoint**: `GET /products/list.php`

**Description**: Retrieves a list of all products.

**Response**:
```json
{
  "status": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "description": "Product Description",
      "price": 100.0,
      "stock": 50,
      "category_id": 1,
      "image_url": "uploads/product_image.png"
    }
  ]
}
```

---

## Categories

### Add Category
**Endpoint**: `POST /categories/add.php`

**Description**: Adds a new category. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "name": "Category Name"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Category added successfully",
  "data": null
}
```

### Update Category
**Endpoint**: `POST /categories/update.php`

**Description**: Updates an existing category. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1,
  "name": "Updated Category Name"
}
```

**Response**:
```json
{
  "status": true,
  "message": "Category updated successfully",
  "data": null
}
```

### Delete Category
**Endpoint**: `POST /categories/delete.php`

**Description**: Deletes a category. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1
}
```

**Response**:
```json
{
  "status": true,
  "message": "Category deleted successfully",
  "data": null
}
```

### List Categories
**Endpoint**: `GET /categories/list.php`

**Description**: Retrieves a list of all categories.

**Response**:
```json
{
  "status": true,
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Category Name"
    }
  ]
}
```

---

## Cart

### Add to Cart
**Endpoint**: `POST /cart/add.php`

**Description**: Adds an item to the user's cart. Requires a valid token.

**Request Body**:
```json
{
  "token": "<user_token>",
  "product_id": 1,
  "quantity": 2
}
```

**Response**:
```json
{
  "status": true,
  "message": "Item added to cart successfully",
  "data": null
}
```

### List Cart Items
**Endpoint**: `GET /cart/list.php`

**Description**: Retrieves the items in the user's cart. Requires a valid token.

**Request Parameters**:
- `token`: `<user_token>`

**Response**:
```json
{
  "status": true,
  "message": "Cart items retrieved successfully",
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

### Remove from Cart
**Endpoint**: `POST /cart/remove.php`

**Description**: Removes an item from the user's cart. Requires a valid token.

**Request Body**:
```json
{
  "token": "<user_token>",
  "cart_id": 1
}
```

**Response**:
```json
{
  "status": true,
  "message": "Item removed from cart successfully",
  "data": null
}
```

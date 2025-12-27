# E-Commerce Backend API Documentation

This document provides detailed information about the endpoints available in the E-Commerce Backend API, including their usage, request formats, and responses.

## Table of Contents
1. [Authentication](#authentication)
   - [Register](#register)
   - [Login](#login)
   - [Logout](#logout)
   - [Social Login](#social-login)
   - [Verify Email](#verify-email)
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
5. [Conventions & Policies](#conventions--policies)
   - [Authorization Rules](#authorization-rules)
   - [Input Formats & Content Types](#input-formats--content-types)
   - [Image Upload & Dedup Policy](#image-upload--dedup-policy)
   - [Cart Behavior](#cart-behavior)
   - [Error Handling & Response Shape](#error-handling--response-shape)
   - [Security Notes](#security-notes)

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
    "email": "john.doe@example.com"
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

### Logout
- Endpoint: `POST /auth/logout.php`
- Purpose: Invalidate and clear the current token on server.
- Auth: Requires valid `token` in request body.
- Content-Type: `application/json`
- Request Body:
```json
{
  "token": "<user_token>"
}
```
- Success Response:
```json
{
  "status": true,
  "message": "Logout successful",
  "data": null
}
```
- Errors:
  - `401` with `{"status": false, "message": "Invalid or missing token"}` when token absent/invalid.

### Social Login
- Endpoint: `POST /auth/social_login.php`
- Purpose: Login/Register via social provider and issue a token.
- Auth: Public (no token required).
- Content-Type: `application/json`
- Request Body:
```json
{
  "email": "john.doe@example.com",
  "name": "John Doe",
  "provider": "google",
  "social_id": "1234567890"
}
```
- Behavior:
  - If user exists by `email`, updates provider ID (`google_id`/`facebook_id`) if provided and returns a fresh token.
  - If user doesn't exist, creates a new user with the given `name` and `email`, stores provider ID, sets `is_verified=1`, and returns a token.
- Success Responses:
  - Existing user:
```json
{
  "status": true,
  "message": "User already exists",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "token": "<generated_token>"
  }
}
```
  - New user:
```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "id": 7,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "token": "<generated_token>"
  }
}
```
- Errors:
  - Missing fields: `{"status": false, "message": "Missing required fields"}`

### Verify Email
- Endpoint: `POST /auth/verify_email.php`
- Purpose: Verify a user's email using a verification code and issue a token.
- Auth: Public (no token required).
- Content-Type: `application/json`
- Request Body:
```json
{
  "email": "john.doe@example.com",
  "verification_code": 123456
}
```
- Behavior:
  - Validates the verification code for the given email.
  - On success, sets `is_verified=1` for the user and issues a token.
- Success Response:
```json
{
  "status": true,
  "message": "Email verified successfully.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "role": "user",
    "token": "<generated_token>"
  }
}
```
- Errors:
  - Invalid code/email: `{"status": false, "message": "Invalid verification code or email"}`

---

## Products

### Add Product
**Endpoint**: `POST /products/add.php`

**Description**: Adds a new product. If a product with the same `name` and `category_id` already exists, the API increases its `stock` by the provided `stock` instead of creating a new record. If an image is provided, the product's image may be updated. Requires admin privileges and a valid token.

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

**Response (new product)**:
```json
{
  "status": true,
  "message": "Product added successfully",
  "data": null
}
```

**Response (existing product, stock increased)**:
```json
{
  "status": true,
  "message": "Product already exists. Stock increased.",
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

**Description**: Adds a new category. The category name must be unique (case-insensitive). If the name already exists, the API returns an error and does not create a new category. Requires admin privileges and a valid token.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "name": "Category Name"
}
```

**Response (category created)**:
```json
{
  "status": true,
  "message": "Category added successfully",
  "data": null
}
```

**Response (duplicate name)**:
```json
{
  "status": false,
  "message": "Category already exists",
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

---

## Conventions & Policies

### Authorization Rules
- Public (no token): `products/list.php`, `categories/list.php`, `index.php`.
- User token required: `cart/add.php`, `cart/list.php`, `cart/remove.php`.
- Admin token required: `products/add.php`, `products/update.php`, `products/delete.php`, `categories/add.php`, `categories/update.php`, `categories/delete.php`.

### Input Formats & Content Types
- `products/add.php` and `products/update.php` accept `multipart/form-data` for image upload with a `token` field in the form.
- `categories/*` and `cart/*` accept `application/json` bodies including a `token` field.
- `products/list.php` and `categories/list.php` are simple `GET` endpoints.

### Image Upload & Dedup Policy
- When uploading a product image, the backend computes a SHA-256 hash of the image content and stores it once under `uploads/<hash>.<ext>`.
- If an identical image has already been uploaded, the existing file is reused and no duplicate file is saved.
- Supported image types: `jpg`, `jpeg`, `png`, `webp`.

### Cart Behavior
- Adding the same `product_id` for the same user increases `quantity` on the existing cart item instead of creating a duplicate row.
- Cart listing uses the user derived from the `token` (ignores any client-provided `user_id`).

### Error Handling & Response Shape
- All endpoints return JSON with a consistent shape:
  - `status`: boolean
  - `message`: human-readable status
  - `data`: payload or `null`
  - `error`: included only on failures with internal error messages

### Security Notes
- The backend derives the authenticated user exclusively from the `token`; client-supplied identifiers (like `user_id`) are ignored for protected endpoints.
- Admin-only endpoints perform a role check and deny access for non-admin users.

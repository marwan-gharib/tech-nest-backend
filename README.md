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
   - [Update Cart Quantity](#update-cart-quantity)
   - [Remove from Cart](#remove-from-cart)
5. [Conventions & Policies](#conventions--policies)
   - [Authorization Rules](#authorization-rules)
   - [Single Session Policy](#single-session-policy)
   - [Email Verification Policy](#email-verification-policy)
   - [Input Formats & Content Types](#input-formats--content-types)
   - [Image Upload & Dedup Policy](#image-upload--dedup-policy)
   - [Cart Behavior](#cart-behavior)
   - [Error Handling & Response Shape](#error-handling--response-shape)
   - [Security Notes](#security-notes)

---

## Authentication

### Register
**Endpoint**: `POST /auth/register.php`

**Description**: Registers a new user and sends a 6-digit verification code to their email via SMTP. The code expires in **5 minutes**.

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

**Description**: Logs in a user and returns a token. **Enforces Single Session Policy**: If the user is already logged in (active token exists), the login request is denied.

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

**Error (Already Logged In)**:
```json
{
  "status": false,
  "message": "User already logged in",
  "data": null
}
```

### Logout
- Endpoint: `POST /auth/logout.php`
- Purpose: Invalidate and clear the current token on server (sets token to NULL).
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

### Social Login
- Endpoint: `POST /auth/social_login.php`
- Purpose: Login/Register via social provider and issue a token.
- Auth: Public (no token required).
- **Policy**: Adheres to Single Session Policy. If the user is already logged in, the request is denied.
- Request Body:
```json
{
  "email": "john.doe@example.com",
  "name": "John Doe",
  "provider": "google",
  "social_id": "1234567890"
}
```

### Verify Email
- Endpoint: `POST /auth/verify_email.php`
- Purpose: Verify a user's email using the 6-digit code sent during registration.
- **Constraints**: Code must be valid and used within **5 minutes** of generation.
- Request Body:
```json
{
  "email": "john.doe@example.com",
  "verification_code": 123456
}
```
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

---

## Products

### Add Product
**Endpoint**: `POST /products/add.php`

**Description**: Adds a new product. If a product with the same `name` and `category_id` already exists, the API increases its `stock` by the provided `stock` instead of creating a new record. Supports image deduplication. Requires admin privileges.

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

### Update Product
**Endpoint**: `POST /products/update.php`

**Description**: Updates an existing product. Requires admin privileges.

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

### Delete Product
**Endpoint**: `POST /products/delete.php`

**Description**: Deletes a product. Requires admin privileges.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1
}
```

### List Products
**Endpoint**: `GET /products/list.php`

**Description**: Retrieves a list of all products.

---

## Categories

### Add Category
**Endpoint**: `POST /categories/add.php`

**Description**: Adds a new category. Name must be unique. Requires admin privileges.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "name": "Category Name"
}
```

### Update Category
**Endpoint**: `POST /categories/update.php`

**Description**: Updates an existing category. Requires admin privileges.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1,
  "name": "Updated Category Name"
}
```

### Delete Category
**Endpoint**: `POST /categories/delete.php`

**Description**: Deletes a category. Requires admin privileges.

**Request Body**:
```json
{
  "token": "<admin_token>",
  "id": 1
}
```

### List Categories
**Endpoint**: `GET /categories/list.php`

**Description**: Retrieves a list of all categories.

---

## Cart

### Add to Cart
**Endpoint**: `POST /cart/add.php`

**Description**: Adds an item to the user's cart. Increases quantity if item already exists. Requires user token.

**Request Body**:
```json
{
  "token": "<user_token>",
  "product_id": 1,
  "quantity": 2
}
```

### List Cart Items
**Endpoint**: `GET /cart/list.php`

**Description**: Retrieves the items in the user's cart.

**Request Parameters**:
- `token`: `<user_token>`

### Update Cart Quantity
**Endpoint**: `POST /cart/update_quantity.php`

**Description**: Updates the quantity of a specific product in the cart. Verifies stock availability before updating.

**Request Body**:
```json
{
  "token": "<user_token>",
  "product_id": 1,
  "quantity": 5
}
```

**Response**:
```json
{
  "status": true,
  "message": "Cart quantity updated successfully"
}
```

### Remove from Cart
**Endpoint**: `POST /cart/remove.php`

**Description**: Removes an item from the user's cart.

**Request Body**:
```json
{
  "token": "<user_token>",
  "cart_id": 1
}
```

---

## Conventions & Policies

### Authorization Rules
- Public (no token): `products/list.php`, `categories/list.php`, `index.php`.
- User token required: All `cart/*` endpoints.
- Admin token required: All write operations on `products` and `categories`.

### Single Session Policy
- **One Active Device**: A user (Admin or Regular) can only have one active session at a time.
- **Login Restriction**: If a user is already logged in (has a non-null token), any new login attempt from the same or different device will be rejected until the previous session is terminated via Logout.
- **Logout**: The `/auth/logout.php` endpoint sets the user's token to `NULL`, freeing up the session for a new login.

### Email Verification Policy
- **SMTP Delivery**: Verification codes are sent via a configured SMTP server (Gmail) for reliability.
- **Code Expiry**: Verification codes are valid for **5 minutes** from the time of registration.
- **Validation**: Attempting to verify with an expired code will result in an error.

### Input Formats & Content Types
- `products/add.php` and `products/update.php` accept `multipart/form-data` (or JSON with base64) for image upload.
- Most other endpoints accept `application/json`.

### Image Upload & Dedup Policy
- When uploading a product image, the backend computes a SHA-256 hash of the image content and stores it once under `uploads/<hash>.<ext>`.
- If an identical image has already been uploaded, the existing file is reused.

### Cart Behavior
- Adding the same `product_id` for the same user increases `quantity` on the existing cart item.
- Cart listing is strictly scoped to the user identified by the provided `token`.

### Error Handling & Response Shape
- All endpoints return JSON with:
  - `status`: boolean
  - `message`: human-readable status
  - `data`: payload or `null`

### Security Notes
- The backend derives the authenticated user exclusively from the `token`.
- Admin-only endpoints perform a strict role check.
- Token validation ensures the user exists and the token matches the one in the database.

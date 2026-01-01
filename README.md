# E-Commerce Backend API Documentation

This document provides detailed information about the endpoints available in the E-Commerce Backend API, which has been restructured to follow the Single Responsibility Principle and Clean Architecture. The API is divided into Admin and User namespaces to ensure separation of concerns.

## Table of Contents
1. [Project Structure](#project-structure)
2. [Authentication](#authentication)
   - [User Authentication](#user-authentication)
   - [Admin Authentication](#admin-authentication)
3. [User Endpoints](#user-endpoints)
   - [Products (List)](#user-products)
   - [Categories (List)](#user-categories)
   - [Cart Operations](#user-cart)
4. [Admin Endpoints](#admin-endpoints)
   - [Products (CRUD)](#admin-products)
   - [Categories (CRUD)](#admin-categories)
5. [Conventions & Policies](#conventions--policies)

---

## Project Structure

The project is organized into the following directories:

- `api/admin`: Contains all endpoints specific to administrators (Product management, Category management, Admin Auth).
- `api/user`: Contains all endpoints specific to users (Shopping, Cart, User Auth).
- `config`: Configuration files (Database connection).
- `helpers`: Helper functions (Email, Validation, Response formatting).
- `uploads`: Stores product images.

---

## Authentication

### User Authentication

#### Register
**Endpoint**: `POST /api/user/auth/register.php`
**Description**: Registers a new user and sends a 6-digit verification code.
**Request**:
```json
{ "name": "User", "email": "user@example.com", "password": "password" }
```

#### Login
**Endpoint**: `POST /api/user/auth/login.php`
**Description**: Logs in a regular user.
**Request**:
```json
{ "email": "user@example.com", "password": "password" }
```

#### Social Login
**Endpoint**: `POST /api/user/auth/social_login.php`

#### Verify Email
**Endpoint**: `POST /api/user/auth/verify_email.php`

#### Logout
**Endpoint**: `POST /api/user/auth/logout.php`

### Admin Authentication

#### Admin Login
**Endpoint**: `POST /api/admin/auth/login.php`
**Description**: Dedicated login for administrators. Enforces role verification.
**Request**:
```json
{ "email": "admin@example.com", "password": "admin_password" }
```

---

## User Endpoints

### Products (List)
**Endpoint**: `GET /api/user/products/list.php`
**Description**: Publicly accessible list of products.

### Categories (List)
**Endpoint**: `GET /api/user/categories/list.php`
**Description**: Publicly accessible list of categories.

### Cart Operations

#### Add to Cart
**Endpoint**: `POST /api/user/cart/add.php`
**Request**:
```json
{ "token": "user_token", "product_id": 1, "quantity": 1 }
```

#### List Cart
**Endpoint**: `GET /api/user/cart/list.php`
**Request**:
```json
{ "token": "user_token" }
```

#### Update Quantity
**Endpoint**: `POST /api/user/cart/update_quantity.php`

#### Remove Item
**Endpoint**: `POST /api/user/cart/remove.php`

---

## Admin Endpoints

### Products (CRUD)
Requires Admin Token.

- **List**: `GET /api/admin/products/list.php`
- **Add**: `POST /api/admin/products/add.php`
- **Update**: `POST /api/admin/products/update.php`
- **Delete**: `POST /api/admin/products/delete.php`

### Categories (CRUD)
Requires Admin Token.

- **List**: `GET /api/admin/categories/list.php`
- **Add**: `POST /api/admin/categories/add.php`
- **Update**: `POST /api/admin/categories/update.php`
- **Delete**: `POST /api/admin/categories/delete.php`

---

## Conventions & Policies

### Error Handling
Error responses are standardized to exclude sensitive debug data:
```json
{
  "status": 4xx/5xx,
  "message": "Error description"
}
```
Success responses may include a `data` field.

### Authorization
- **Admin Endpoints**: Strictly require a user with `role: 'admin'`.
- **User Endpoints**: Generally for `role: 'user'`, though admins can access read-only user endpoints.
- **Single Session**: Users cannot log in if they already have an active session (valid token).

### File Organization
- **Config**: `config/database.php` handles PDO connection.
- **Helpers**: `helpers/functions.php` contains reusable logic for Auth, Email, and Responses.

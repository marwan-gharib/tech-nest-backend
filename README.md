# 🚀 Tech Nest Backend API

Welcome to the **Tech Nest Backend API**! This project powers a modern tech e-commerce platform with a robust RESTful API built in PHP. It supports full user and admin flows, including product management, category systems, cart operations, protected routes, and email verification.

---

## 📖 Table of Contents
- [✨ Key Features](#-key-features)
- [🏗️ System Architecture](#-system-architecture)
- [🔐 Authentication & Authorization](#-authentication--authorization)
- [📦 API Reference](#-api-reference)
  - [User Endpoints](#user-endpoints)
  - [Admin Endpoints](#admin-endpoints)
- [🎨 Response Format](#-response-format)
- [💻 Client-Side Implementation Guide](#-client-side-implementation-guide)
- [⚙️ Setup & Installation](#️-setup--installation)

---

## ✨ Key Features
- **Dual Role System**: Separate authentication flows for Users and Admins.
- **Secure Authentication**: Token-based security with automatic expiry.
- **Full E-Commerce Stack**: Categories, Products, and Cart management.
- **Advanced Filtering**: Product listing with search, category filtering, price range, and sorting.
- **Email Verification**: Built-in verification with PHPMailer for user registration.
- **Modern Performance**: Image processing with WebP conversion to ensure fast load times.

---

## 🏗️ System Architecture
The API follows a modular structure where each endpoint is a standalone PHP file, making it easy to scale and debug.
- **`/api/user`**: Public and protected consumer-facing endpoints.
- **`/api/admin`**: Protected management endpoints.
- **`/helpers`**: Core logic for authentication, email, and responses.
- **`/config`**: Database configuration.
- **`/uploads`**: Optimized WebP storage for images.

---

## 🔐 Authentication & Authorization

The API uses custom HTTP headers for security.

### 👤 User Authentication
- **Header**: `token: <your_token>`
- **Expiry**: Tokens are valid for 7 days.
- **Status**: Account must be email-verified to use protected routes.

### 🛡️ Admin Authentication
- **Header**: `Token: Bearer <your_token>`
- **Expiry**: Tokens are valid for 2 days.

---

## 📦 API Reference

### User Endpoints

#### 🔑 Auth & Accounts
| Endpoint | Method | Params Type | Description |
| :--- | :--: | :--- | :--- |
| `api/user/auth/register.php` | `POST` | `FormData` | Register with `name`, `email`, `password`, and `profile_image`. |
| `api/user/auth/verify_email.php` | `POST` | `JSON` | Verify account using `email` and `verification_code`. |
| `api/user/auth/login.php` | `POST` | `JSON` | Authenticate with `email` and `password`. |
| `api/user/auth/forget_password.php` | `POST` | `JSON` | Request a password reset code for an `email`. |
| `api/user/auth/reset_password.php` | `POST` | `JSON` | Reset password using `email`, `code`, and `new_password`. |
| `api/user/auth/logout.php` | `POST` | `Header` | Invalidate current user token. |

#### 🛍️ Products & Categories
| Endpoint | Method | Query Params | Description |
| :--- | :--: | :--- | :--- |
| `api/user/categories/list.php` | `GET` | - | Returns all categories. |
| `api/user/products/list.php` | `GET` | `limit`, `page`, `category_id`, `search`, `min_price`, `max_price`, `sort`, `order` | Advanced product listing with pagination and filters. |
| `api/user/products/searching_suggestions.php` | `GET` | `search_query` | Dynamic search suggestions (min 2 chars). |

#### 🛒 Shopping Cart
| Endpoint | Method | Params Type | Description |
| :--- | :--: | :--- | :--- |
| `api/user/cart/list.php` | `GET` | - | Get all items in user's cart. |
| `api/user/cart/add.php` | `POST` | `JSON` | Add product to cart (`product_id`, `quantity`). |
| `api/user/cart/update_quantity.php` | `POST` | `JSON` | Update item quantity (`id`, `quantity`). |
| `api/user/cart/remove.php` | `POST` | `JSON` | Remove item from cart by its `id`. |
| `api/user/cart/count.php` | `GET` | - | Returns total number of items in cart. |

---

### Admin Endpoints

#### 🛡 Admin Auth
| Endpoint | Method | Params Type | Description |
| :--- | :--: | :--- | :--- |
| `api/admin/auth/login.php` | `POST` | `JSON` | Admin login to get bearer token. |

#### 📂 Category Management
| Endpoint | Method | Params Type | Description |
| :--- | :--: | :--- | :--- |
| `api/admin/categories/list.php` | `GET` | - | List all categories. |
| `api/admin/categories/add.php` | `POST` | `FormData` | Add category with `name` and optional `category_image`. |
| `api/admin/categories/update.php` | `POST` | `FormData` | Update category with `id`, `name`, and optional `category_image`. |
| `api/admin/categories/delete.php` | `POST` | `JSON` | Delete category by `id`. |

#### 📦 Product Management
| Endpoint | Method | Params Type | Description |
| :--- | :--: | :--- | :--- |
| `api/admin/products/list.php` | `GET` | `limit`, `page`, etc. | Admin-view product listing. |
| `api/admin/products/add.php` | `POST` | `FormData` | Add product with `name`, `price`, `stock`, `category_id`, `description`, and `product_image`. |
| `api/admin/products/update.php` | `POST` | `FormData` | Update product details and image using `id`. |
| `api/admin/products/delete.php` | `POST` | `JSON` | Delete product by `id`. |

---

## 🎨 Response Format

All responses follow a standard envelope structure:

```json
{
  "status": 200,
  "message": "Operation successful",
  "data": {
    "key": "value"
  }
}
```

- **200/201**: Success.
- **400**: Validation error or missing parameters.
- **401**: Authentication failed (Invalid or expired token).
- **404**: Resource not found.
- **409**: Conflict (e.g., email already exists).
- **500**: Internal server error.

---

## 💻 Client-Side Implementation Guide

### Handling Form Data vs JSON
- **JSON**: Used for data-only requests (Login, Cart updates, Deleting). Use `Content-Type: application/json`.
- **Form Data**: Used when uploading files (Register, Adding Products). **Do NOT** set the `Content-Type` header manually in JavaScript (let the browser handle it with `Multipart/form-data`).

### Example: Product List with Filters
```javascript
const response = await fetch('http://localhost/tech-nest-backend/api/user/products/list.php?search=phone&min_price=100&sort=price&order=DESC', {
  headers: {
    'token': 'YOUR_USER_TOKEN'
  }
});
const result = await response.json();
```

### Example: File Upload (Registration)
```javascript
const formData = new FormData();
formData.append('name', 'John Doe');
formData.append('email', 'john@example.com');
formData.append('password', 'secure123');
formData.append('profile_image', imageFile);

const response = await fetch('api/user/auth/register.php', {
  method: 'POST',
  body: formData
});
```

---

## ⚙️ Setup & Installation

1. **Environment**: Ensure you are running PHP 7.4+ and MySQL on XAMPP/WAMP.
2. **Database**: Import the provided SQL schema into your MySQL server.
3. **Config**: Update `config/database.php` with your local DB credentials.
4. **Permissions**: Ensure the `uploads/` directory is writable for image storage.
5. **Mail**: Update the SMTP credentials in `helpers/functions.php` to use your own email provider for verifications.

---

Designed with ❤️ for **Tech Nest**.

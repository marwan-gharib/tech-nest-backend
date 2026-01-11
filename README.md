# 🌟 Tech Nest Backend API

Welcome to the **Tech Nest Backend API**! This project powers the backend for an Tech Nest platform, offering endpoints for user and admin authentication, product and category management, and cart operations. Built with PHP, the API is modular, scalable, and easy to maintain.

---

## 📖 Table of Contents
- [✨ Features](#-features)
- [📂 Folder Structure](#-folder-structure)
- [🔗 Endpoints](#-endpoints)
  - [Admin Endpoints](#admin-endpoints)
  - [User Endpoints](#user-endpoints)
- [⚙️ Setup Instructions](#️-setup-instructions)
- [🛠️ Technologies Used](#️-technologies-used)
- [🤝 Contributing](#-contributing)
- [📜 License](#-license)

---

## ✨ Features
- **Authentication**: Secure login and token-based authentication for both users and admins.
- **Token Expiry**: Tokens expire after 7 days for users and 2 days for admins.
- **Product Management**: Add, update, delete, and list products.
- **Category Management**: Add, update, delete, and list categories.
- **Cart Operations**: Add, update, remove, and list items in the cart.
- **Social Login**: Support for Google and Facebook login.

---

## 🔗 Endpoints

### Admin Endpoints

#### 🔒 Authentication
- **Login**: `POST /api/admin/auth/login.php`
  - **Description**: Authenticates an admin user and generates a token with a 2-day expiration.
  - **Request Body**:
    ```json
    {
      "email": "admin@example.com",
      "password": "password123"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Login successful",
      "data": {
        "token": "<admin_token>"
      }
    }
    ```

- **Logout**: `POST /api/admin/auth/logout.php`
  - **Description**: Logs out the admin user by invalidating the token.
  - **Headers**:
    ```json
    {
      "Authorization": "Bearer <admin_token>"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Logout successful"
    }
    ```

- **Validate Token**: `GET /api/admin/auth/validate_token.php`
  - **Description**: Validates the admin token to ensure it is still active.
  - **Headers**:
    ```json
    {
      "Authorization": "Bearer <admin_token>"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Token is valid"
    }
    ```

#### 📂 Categories
- **Add Category**: `POST /api/admin/categories/add.php`
  - **Description**: Adds a new category to the database.
  - **Request Body**:
    ```json
    {
      "name": "Electronics"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 201,
      "message": "Category added successfully"
    }
    ```

- **Delete Category**: `DELETE /api/admin/categories/delete.php`
  - **Description**: Deletes a category by its ID.
  - **Request Body**:
    ```json
    {
      "id": 1
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Category deleted successfully"
    }
    ```

- **List Categories**: `GET /api/admin/categories/list.php`
  - **Description**: Retrieves a list of all categories.
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Categories retrieved successfully",
      "data": [
        {
          "id": 1,
          "name": "Electronics"
        }
      ]
    }
    ```

- **Update Category**: `PUT /api/admin/categories/update.php`
  - **Description**: Updates the name of an existing category.
  - **Request Body**:
    ```json
    {
      "id": 1,
      "name": "Updated Category Name"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Category updated successfully"
    }
    ```

#### 🛒 Products
- **Add Product**: `POST /api/admin/products/add.php`
  - **Description**: Adds a new product to the database.
  - **Request Body**:
    ```json
    {
      "name": "Smartphone",
      "price": 299.99,
      "category_id": 1,
      "description": "Latest model smartphone",
      "image": "path/to/image.jpg"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 201,
      "message": "Product added successfully"
    }
    ```

- **Delete Product**: `DELETE /api/admin/products/delete.php`
  - **Description**: Deletes a product by its ID.
  - **Request Body**:
    ```json
    {
      "id": 1
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Product deleted successfully"
    }
    ```

- **List Products**: `GET /api/admin/products/list.php`
  - **Description**: Retrieves a list of all products.
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Products retrieved successfully",
      "data": [
        {
          "id": 1,
          "name": "Smartphone",
          "price": 299.99,
          "category_id": 1,
          "description": "Latest model smartphone",
          "image": "path/to/image.jpg"
        }
      ]
    }
    ```

- **Update Product**: `PUT /api/admin/products/update.php`
  - **Description**: Updates the details of an existing product.
  - **Request Body**:
    ```json
    {
      "id": 1,
      "name": "Updated Smartphone",
      "price": 249.99,
      "category_id": 1,
      "description": "Updated description",
      "image": "path/to/new_image.jpg"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Product updated successfully"
    }
    ```

### User Endpoints

#### 🔑 Authentication
- **Register**: `POST /api/user/auth/register.php`
  - **Description**: Registers a new user and sends a verification email.
  - **Request Body**:
    ```json
    {
      "name": "John Doe",
      "email": "john@example.com",
      "password": "password123"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 201,
      "message": "Registration successful, please check your email to verify your account"
    }
    ```

- **Login**: `POST /api/user/auth/login.php`
  - **Description**: Authenticates a user and generates a token with a 7-day expiration.
  - **Request Body**:
    ```json
    {
      "email": "john@example.com",
      "password": "password123"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Login successful",
      "data": {
        "token": "<user_token>"
      }
    }
    ```

- **Logout**: `POST /api/user/auth/logout.php`
  - **Description**: Logs out the user by invalidating the token.
  - **Headers**:
    ```json
    {
      "Authorization": "Bearer <user_token>"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Logout successful"
    }
    ```

- **Social Login**: `POST /api/user/auth/social_login.php`
  - **Description**: Authenticates a user via social media (Google, Facebook).
  - **Request Body**:
    ```json
    {
      "provider": "google",
      "token": "<social_token>"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Social login successful",
      "data": {
        "token": "<user_token>"
      }
    }
    ```

- **Validate Token**: `GET /api/user/auth/validate_token.php`
  - **Description**: Validates the user token to ensure it is still active.
  - **Headers**:
    ```json
    {
      "Authorization": "Bearer <user_token>"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Token is valid"
    }
    ```

- **Verify Email**: `POST /api/user/auth/verify_email.php`
  - **Description**: Verifies the user's email address.
  - **Request Body**:
    ```json
    {
      "token": "<verification_token>"
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Email verified successfully"
    }
    ```

#### 📂 Categories
- **List Categories**: `GET /api/user/categories/list.php`
  - **Description**: Retrieves a list of all categories.
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Categories retrieved successfully",
      "data": [
        {
          "id": 1,
          "name": "Electronics"
        }
      ]
    }
    ```

#### 🛍️ Products
- **List Products**: `GET /api/user/products/list.php`
  - **Description**: Retrieves a list of all products.
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Products retrieved successfully",
      "data": [
        {
          "id": 1,
          "name": "Smartphone",
          "price": 299.99,
          "category_id": 1,
          "description": "Latest model smartphone",
          "image": "path/to/image.jpg"
        }
      ]
    }
    ```

#### 🛒 Cart
- **Add to Cart**: `POST /api/user/cart/add.php`
  - **Description**: Adds a product to the user's cart.
  - **Request Body**:
    ```json
    {
      "product_id": 1,
      "quantity": 2
    }
    ```
  - **Response**:
    ```json
    {
      "status": 201,
      "message": "Product added to cart"
    }
    ```

- **List Cart Items**: `GET /api/user/cart/list.php`
  - **Description**: Retrieves the items in the user's cart.
  - **Response**:
    ```json
    {
      "status": 200,
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

- **Remove from Cart**: `DELETE /api/user/cart/remove.php`
  - **Description**: Removes an item from the user's cart.
  - **Request Body**:
    ```json
    {
      "id": 1
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Item removed from cart"
    }
    ```

- **Update Cart Quantity**: `PUT /api/user/cart/update_quantity.php`
  - **Description**: Updates the quantity of an item in the user's cart.
  - **Request Body**:
    ```json
    {
      "id": 1,
      "quantity": 3
    }
    ```
  - **Response**:
    ```json
    {
      "status": 200,
      "message": "Cart quantity updated"
    }
    ```

---

## ⚙️ Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/your-username/tech-nest-backend-php.git
   ```
2. **Navigate to the project directory**:
   ```bash
   cd Tech Nest-backend-php
   ```
3. **Set up the database**:
   - Import the `ecommerce_db.sql` file into your MySQL database.
   - Update the database credentials in `config/database.php`.
4. **Start the server**:
   - Use XAMPP or any PHP server to host the project.
5. **Test the endpoints**:
   - Use tools like Postman or cURL.

---

## 🛠️ Technologies Used
- **Backend**: PHP
- **Database**: MySQL
- **Authentication**: Token-based (JWT-like custom implementation)
- **Libraries**: PHPMailer for email verification

---

## 🤝 Contributing
Contributions are welcome! Please fork the repository and submit a pull request.

---

## 📜 License
This project is licensed under the MIT License. See the LICENSE file for details. You can view the license [here](LICENSE).

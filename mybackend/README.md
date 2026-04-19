# MyBackend API

A raw PHP RESTful Backend API demonstrating structured architecture, authentication, post management, and location filtering. Built without frameworks for performance and simplicity.

## Features
- **User Authentication:** Signup and Login via custom JWT payload validation
- **Post Management:** Create posts and retrieve them
- **Pagination & Location Filtering:** Fetch posts by page and limit sizes, and filter by string locations (e.g., "Lekki")
- **Like/Unlike:** Toggle likes on individual posts
- **Rate Limiting:** Protects endpoints from abuse using file-based IP tracking
- **File Logging:** Simple `.log` error and access tracing

## Architecture
- **Controllers:** Handle HTTP requests and orchestrate flow to models/services.
- **Models:** PDO-based database interacting structures.
- **Middleware:** Request interception for Authentication and Rate Limiting.
- **Core:** Core application functionalities including Custom Regular Expression Routing and JSON Response generation.

## Setup Requirements
- PHP 7.4 or 8.x
- MySQL
- Apache (Recommended for `.htaccess` URL rewriting) or Nginx

## Setup Instructions
1. Clone or copy this repository to your local web server's document root (e.g., `htdocs` or `/var/www/html/mybackend`).
2. Open MySQL and run the queries found in `database.sql` to initialize `mybackend_db` and its tables (`users`, `posts`, `likes`).
3. Update database credentials in `config/database.php` if you are not using default (username: `root`, pass: ``).
4. Ensure your server is enabled for `mod_rewrite` if using Apache so the `.htaccess` takes effect.
5. Create a `logs` directory in the root folder, and inside it, a `rate_limit` folder. Grant it write permissions.

## API Documentation

### Environment
Base URL: `http://localhost/mybackend/api`

### 1. Account Creation (Signup)
- **Endpoint:** `POST /auth/signup`
- **Body Requirement (JSON):** 
```json
{
  "username": "johndoe",
  "email": "johndoe@example.com",
  "password": "securepassword"
}
```
- **Response (201):** `"message": "User properly registered."`

### 2. Login
- **Endpoint:** `POST /auth/login`
- **Body Requirement (JSON):** 
```json
{
  "email": "johndoe@example.com",
  "password": "securepassword"
}
```
- **Response (200):** JWT token will be returned in the `token` parameter. You will use this token in the `Authorization` header (`Bearer <token>`) for protected routes.

### 3. Create a Post
- **Endpoint:** `POST /posts`
- **Headers:** `Authorization: Bearer <your_token>`
- **Body Requirement (JSON):** 
```json
{
  "content": "This is a great place to be",
  "location": "Lekki"
}
```
- **Response (201):** Post creation status and new `post_id`.

### 4. Fetch Posts
- **Endpoint:** `GET /posts`
- **Query Parameters:**
  - `page`: default 1
  - `limit`: default 10
  - `location`: string (e.g., "Yaba")
- **Example Usage:** `GET /posts?page=1&limit=5&location=Yaba`

### 5. Toggle Like on a Post
- **Endpoint:** `POST /likes/toggle`
- **Headers:** `Authorization: Bearer <your_token>`
- **Body Requirement (JSON):**
```json
{
  "post_id": 1
}
```
- **Response (200):** Toggles between `"action": "liked"` and `"action": "unliked"`.

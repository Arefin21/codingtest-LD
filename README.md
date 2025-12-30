# Laravel URL Shortener API

A RESTful API built with Laravel for user registration, authentication, and URL shortening functionality. This API uses Laravel Sanctum for secure API token authentication.

## Features

- ✅ User registration with validation
- ✅ User login with token generation
- ✅ Secure password hashing
- ✅ API token authentication using Laravel Sanctum
- ✅ URL shortening with unique short codes
- ✅ URL redirection from short codes
- ✅ Comprehensive error handling
- ✅ Input validation with proper error messages
- ✅ JSON responses for all endpoints

## Requirements

- PHP >= 8.2
- Composer
- Laravel 11.x
- Laravel Sanctum 4.x
- MySQL/PostgreSQL/SQLite

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd CodingTest-LD-2025
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations:
```bash
php artisan migrate
```

7. Start the development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication

All protected endpoints require authentication using Bearer tokens. Include the token in the `Authorization` header:

```
Authorization: Bearer {your_token_here}
```

---

## Endpoints

### 1. Register User

Register a new user account and receive an API token.

**Endpoint:** `POST /api/register`

**Authentication:** Not required

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: required, valid email format, unique in users table
- `password`: required, string, minimum 8 characters, must match password_confirmation

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
        "token_type": "Bearer"
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": [
            "This email is already registered."
        ],
        "password": [
            "The password confirmation does not match."
        ]
    }
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Registration failed. Please try again.",
    "error": "Error message details"
}
```

---

### 2. Login User

Authenticate a user and receive an API token.

**Endpoint:** `POST /api/login`

**Authentication:** Not required

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Validation Rules:**
- `email`: required, valid email format
- `password`: required, string

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "token": "2|abcdefghijklmnopqrstuvwxyz1234567890",
        "token_type": "Bearer"
    }
}
```

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "Invalid credentials",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email field is required."
        ]
    }
}
```

---

### 3. Shorten URL

Create a shortened URL from an original URL. Requires authentication.

**Endpoint:** `POST /api/shorten`

**Authentication:** Required (Bearer token)

**Request Headers:**
```
Authorization: Bearer {your_token_here}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "url": "https://www.example.com/very/long/url/path"
}
```

**Validation Rules:**
- `url`: required, valid URL format, maximum 2048 characters

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "URL shortened successfully",
    "data": {
        "id": 1,
        "original_url": "https://www.example.com/very/long/url/path",
        "short_code": "abc12345",
        "short_url": "http://localhost:8000/api/redirect/abc12345",
        "created_at": "2025-02-05T10:30:00.000000Z"
    }
}
```

**Error Response (401 Unauthorized) - Invalid Token:**
```json
{
    "message": "Unauthenticated."
}
```

**Error Response (409 Conflict) - Duplicate URL:**
```json
{
    "success": false,
    "message": "This URL has already been shortened",
    "errors": {
        "url": [
            "A shortened URL for this address already exists."
        ]
    },
    "data": {
        "short_code": "abc12345",
        "short_url": "http://localhost:8000/api/redirect/abc12345",
        "original_url": "https://www.example.com/very/long/url/path"
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "url": [
            "Please provide a valid URL."
        ]
    }
}
```

---

### 4. Redirect to Original URL

Redirect to the original URL using the short code.

**Endpoint:** `GET /api/redirect/{shortCode}`

**Authentication:** Not required

**URL Parameters:**
- `shortCode`: The unique short code generated for the URL

**Example:**
```
GET /api/redirect/abc12345
```

**Success Response (302 Redirect):**
The API will redirect to the original URL.

**Error Response (404 Not Found) - Missing Short Code:**
```json
{
    "success": false,
    "message": "Short URL not found",
    "errors": {
        "short_code": [
            "The provided short code does not exist."
        ]
    }
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to redirect. Please try again.",
    "error": "Error message details"
}
```

---

## Error Handling

The API implements comprehensive error handling for various scenarios:

### Invalid Token (401)
When an invalid or expired token is provided:
```json
{
    "message": "Unauthenticated."
}
```

### Duplicate URLs (409)
When a user tries to shorten a URL that already exists:
```json
{
    "success": false,
    "message": "This URL has already been shortened",
    "errors": {
        "url": ["A shortened URL for this address already exists."]
    },
    "data": {
        "short_code": "existing_code",
        "short_url": "http://localhost:8000/api/redirect/existing_code",
        "original_url": "https://example.com"
    }
}
```

### Missing Short Codes (404)
When a short code doesn't exist:
```json
{
    "success": false,
    "message": "Short URL not found",
    "errors": {
        "short_code": ["The provided short code does not exist."]
    }
}
```

### Validation Errors (422)
When input validation fails:
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

---

## Security Features

1. **Password Hashing**: All passwords are securely hashed using Laravel's `Hash::make()` before storage
2. **API Token Authentication**: Laravel Sanctum provides secure token-based authentication
3. **Input Validation**: All user inputs are validated before processing
4. **SQL Injection Protection**: Laravel's Eloquent ORM provides protection against SQL injection
5. **XSS Protection**: Laravel automatically escapes output to prevent XSS attacks

---

## Testing the API

### Using cURL

**Register a new user:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Shorten URL (replace {token} with your actual token):**
```bash
curl -X POST http://localhost:8000/api/shorten \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "url": "https://www.example.com/very/long/url/path"
  }'
```

**Redirect (use browser or curl with -L flag):**
```bash
curl -L http://localhost:8000/api/redirect/abc12345
```

### Using Postman

1. Import the endpoints into Postman
2. For protected routes, add the token in the Authorization tab:
   - Type: Bearer Token
   - Token: `{your_token_here}`
3. Set headers:
   - `Content-Type: application/json`
   - `Accept: application/json`

---

## Database Schema

### users
- `id` (bigint, primary key)
- `name` (string)
- `email` (string, unique)
- `password` (string, hashed)
- `email_verified_at` (timestamp, nullable)
- `remember_token` (string, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### shortened_urls
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key)
- `original_url` (string)
- `short_code` (string, unique)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### personal_access_tokens
- `id` (bigint, primary key)
- `tokenable_type` (string)
- `tokenable_id` (bigint)
- `name` (string)
- `token` (string, unique)
- `abilities` (text, nullable)
- `last_used_at` (timestamp, nullable)
- `expires_at` (timestamp, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── AuthController.php      # Handles registration and login
│           └── ShortenUrlController.php # Handles URL shortening and redirection
├── Models/
│   ├── User.php                        # User model
│   └── ShortenedUrl.php                # Shortened URL model
routes/
└── api.php                             # API routes
database/
└── migrations/
    ├── create_users_table.php
    ├── create_shortened_urls_table.php
    └── create_personal_access_tokens_table.php
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Support

For issues or questions, please contact: hr@webase.com.bd

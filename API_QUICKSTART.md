# 🚀 Quick Start - CaseProject API with Authentication

## Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Create Test User

```bash
php artisan db:seed --class=ApiTestUserSeeder
```

**Test Credentials:**

-   Email: `api@test.com`
-   Password: `password123`

---

## Testing the API

### Option 1: Using Postman

1. Import the Postman collection: `CaseProject_API.postman_collection.json`
2. Update `base_url` variable to your domain (default: `http://localhost/api`)
3. Run the "Login" request first
4. Token will be automatically saved
5. Test other endpoints

### Option 2: Using cURL (Bash)

Run the test script:

```bash
bash test-api.sh
```

### Option 3: Manual cURL Commands

**1. Login:**

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "api@test.com",
    "password": "password123"
  }'
```

**2. Use the token from response:**

```bash
TOKEN="your-token-here"

curl -X GET http://localhost/api/case-projects \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📚 Full Documentation

See: `CASEPROJECT_API_DOCUMENTATION.md`

---

## 🔑 Authentication Flow

1. **Login** → Get Bearer Token
2. **Use Token** → Include in Authorization header for all requests
3. **Logout** → Revoke token when done

---

## 🛡️ Security Features

✅ Laravel Sanctum Authentication  
✅ Bearer Token Authorization  
✅ Token Revocation  
✅ Password Hashing  
✅ Validation on all inputs  
✅ Protected endpoints

---

## 📝 Available Endpoints

### Authentication

-   `POST /api/login` - Get token
-   `POST /api/logout` - Revoke current token
-   `GET /api/me` - Get user info
-   `POST /api/revoke-all-tokens` - Revoke all user tokens

### CaseProject (Protected)

-   `GET /api/case-projects` - List all (paginated)
-   `GET /api/case-projects/{id}` - Get single
-   `POST /api/case-projects` - Create new
-   `PUT /api/case-projects/{id}` - Update
-   `DELETE /api/case-projects/{id}` - Delete (soft)

---

## ⚡ Quick Test

```bash
# 1. Start Laravel server
php artisan serve

# 2. In another terminal, test the API
bash test-api.sh
```

---

## 🐛 Troubleshooting

### 401 Unauthenticated

-   Make sure you included the token: `Authorization: Bearer {token}`
-   Token might be expired or revoked, login again

### 404 Not Found

-   Check if the resource ID exists
-   Verify your base URL is correct

### 422 Validation Error

-   Check required fields in the documentation
-   Verify staff_id and client_id exist in database

---

## 📦 Files Created

-   `app/Http/Controllers/Api/AuthController.php` - Authentication controller
-   `app/Http/Controllers/Api/CaseProjectController.php` - CaseProject API controller
-   `app/Http/Resources/CaseProjectResource.php` - API Resource transformer
-   `app/Http/Resources/CaseProjectCollection.php` - Collection resource
-   `routes/api.php` - API routes
-   `database/seeders/ApiTestUserSeeder.php` - Test user seeder
-   `test-api.sh` - Bash testing script
-   `CaseProject_API.postman_collection.json` - Postman collection
-   `CASEPROJECT_API_DOCUMENTATION.md` - Full documentation

# CaseProject API Documentation

Base URL: `http://your-domain.com/api`

**⚠️ IMPORTANT: Most endpoints require authentication using Bearer Token**

## Authentication

### 1. Login (Get API Token)

```
POST /api/login
```

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
        "token_type": "Bearer"
    }
}
```

**Error Response (422):**

```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

### 2. Logout (Revoke Current Token)

```
POST /api/logout
```

**Headers:**

```
Authorization: Bearer {your-token}
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### 3. Get Current User Info

```
GET /api/me
```

**Headers:**

```
Authorization: Bearer {your-token}
```

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "staff_id": 1
    }
}
```

---

### 4. Revoke All Tokens

```
POST /api/revoke-all-tokens
```

**Headers:**

```
Authorization: Bearer {your-token}
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "All tokens revoked successfully"
}
```

---

## CaseProject Endpoints

**🔒 All CaseProject endpoints require authentication**

### Headers Required for All CaseProject Endpoints:

```
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

---

### 1. Get All Case Projects (Paginated)

```
GET /api/case-projects
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "description": "Kasus pajak perusahaan X",
      "case_date": "2025-11-10",
      "status": "open",
      "link_dokumen": "https://example.com/doc.pdf",
      "staff": {
        "id": 1,
        "name": "John Doe",
        "phone": "081234567890",
        "position": "Tax Consultant",
        "department": "Tax Department"
      },
      "client": {
        "id": 1,
        "company_name": "PT ABC",
        "contact_person": "Jane Smith",
        "phone": "081234567891"
      },
      "created_at": "2025-11-10T10:00:00.000000Z",
      "updated_at": "2025-11-10T10:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

**Query Parameters:**

-   `page` - Nomor halaman (default: 1)
-   `per_page` - Jumlah data per halaman (default: 15)

---

### 2. Get Single Case Project

```
GET /api/case-projects/{id}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "description": "Kasus pajak perusahaan X",
    "case_date": "2025-11-10",
    "status": "open",
    "link_dokumen": "https://example.com/doc.pdf",
    "staff": {...},
    "client": {...},
    "created_at": "2025-11-10T10:00:00.000000Z",
    "updated_at": "2025-11-10T10:00:00.000000Z"
  }
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "Case project not found"
}
```

---

### 3. Create New Case Project

```
POST /api/case-projects
```

**Request Body:**

```json
{
    "description": "Kasus pajak perusahaan X",
    "case_date": "2025-11-10",
    "status": "open",
    "staff_id": 1,
    "client_id": 1,
    "link_dokumen": "https://example.com/doc.pdf"
}
```

**Validation Rules:**

-   `description` - required, string
-   `case_date` - required, date format
-   `status` - required, string
-   `staff_id` - required, exists in staff table
-   `client_id` - required, exists in clients table
-   `link_dokumen` - optional, string

**Success Response (201):**

```json
{
  "success": true,
  "message": "Case project created successfully",
  "data": {...}
}
```

**Error Response (422):**

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "description": ["The description field is required."],
        "staff_id": ["The selected staff id is invalid."]
    }
}
```

---

### 4. Update Case Project

```
PUT /api/case-projects/{id}
```

or

```
PATCH /api/case-projects/{id}
```

**Request Body:** (semua field optional)

```json
{
    "description": "Updated description",
    "case_date": "2025-11-11",
    "status": "closed",
    "staff_id": 2,
    "client_id": 2,
    "link_dokumen": "https://example.com/new-doc.pdf"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "Case project updated successfully",
  "data": {...}
}
```

---

### 5. Delete Case Project

```
DELETE /api/case-projects/{id}
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "Case project deleted successfully"
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "Case project not found"
}
```

---

## Example Usage

### Using cURL

**1. Login to get token:**

```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

**2. Get all case projects (with token):**

```bash
curl -X GET http://your-domain.com/api/case-projects \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**3. Get specific case project:**

```bash
curl -X GET http://your-domain.com/api/case-projects/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**4. Create new case project:**

```bash
curl -X POST http://your-domain.com/api/case-projects \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Kasus pajak perusahaan X",
    "case_date": "2025-11-10",
    "status": "open",
    "staff_id": 1,
    "client_id": 1,
    "link_dokumen": "https://example.com/doc.pdf"
  }'
```

**5. Update case project:**

```bash
curl -X PUT http://your-domain.com/api/case-projects/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "closed"
  }'
```

**6. Delete case project:**

```bash
curl -X DELETE http://your-domain.com/api/case-projects/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**7. Logout:**

```bash
curl -X POST http://your-domain.com/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

### Using JavaScript (Fetch API)

```javascript
// 1. Login first to get token
async function login() {
    const response = await fetch("http://your-domain.com/api/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            email: "user@example.com",
            password: "password123",
        }),
    });

    const data = await response.json();
    const token = data.data.token;

    // Save token to localStorage
    localStorage.setItem("api_token", token);

    return token;
}

// 2. Get all case projects with authentication
async function getCaseProjects() {
    const token = localStorage.getItem("api_token");

    const response = await fetch("http://your-domain.com/api/case-projects", {
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
        },
    });

    const data = await response.json();
    console.log(data);
}

// 3. Create new case project with authentication
async function createCaseProject() {
    const token = localStorage.getItem("api_token");

    const response = await fetch("http://your-domain.com/api/case-projects", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({
            description: "Kasus pajak perusahaan X",
            case_date: "2025-11-10",
            status: "open",
            staff_id: 1,
            client_id: 1,
            link_dokumen: "https://example.com/doc.pdf",
        }),
    });

    const data = await response.json();
    console.log(data);
}

// 4. Logout
async function logout() {
    const token = localStorage.getItem("api_token");

    const response = await fetch("http://your-domain.com/api/logout", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
        },
    });

    const data = await response.json();
    localStorage.removeItem("api_token");
    console.log(data);
}

// Usage
login().then(() => {
    getCaseProjects();
    createCaseProject();
});
```

---

### Using Postman

1. **Create Environment Variables**:

    - `base_url` = `http://your-domain.com/api`
    - `token` = (will be set after login)

2. **Login Request**:

    - Method: POST
    - URL: `{{base_url}}/login`
    - Headers: `Content-Type: application/json`
    - Body (raw JSON):
        ```json
        {
            "email": "user@example.com",
            "password": "password123"
        }
        ```
    - Tests tab (to save token):
        ```javascript
        if (pm.response.code === 200) {
            var jsonData = pm.response.json();
            pm.environment.set("token", jsonData.data.token);
        }
        ```

3. **Setup Authorization for Protected Endpoints**:

    - Type: Bearer Token
    - Token: `{{token}}`

4. **Or add to Headers manually**:

    - Key: `Authorization`
    - Value: `Bearer {{token}}`

5. **Make requests to CaseProject endpoints** with the token automatically included

---

## Error Responses

### 401 Unauthorized (No token or invalid token)

```json
{
    "message": "Unauthenticated."
}
```

### 422 Validation Error

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "field_name": ["Error message here"]
    }
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "Case project not found"
}
```

---

## Security Best Practices

1. **Store tokens securely**:

    - In mobile apps: Use secure storage (Keychain/Keystore)
    - In web apps: Use httpOnly cookies or secure localStorage
    - Never expose tokens in URLs

2. **Token Management**:

    - Tokens don't expire automatically (unless configured)
    - Revoke tokens when user logs out
    - Use `/revoke-all-tokens` if you suspect token compromise

3. **HTTPS Required**:

    - Always use HTTPS in production
    - Never send tokens over HTTP

4. **Rate Limiting**:
    - Laravel Sanctum has built-in rate limiting
    - Default: 60 requests per minute per user

---

## Notes

-   All protected endpoints require `Authorization: Bearer {token}` header
-   Tokens are stored in `personal_access_tokens` table
-   Each user can have multiple active tokens
-   Soft delete is used for CaseProject (data not permanently deleted)
-   Pagination default: 15 items per page
-   Relations (staff & client) are automatically eager-loaded

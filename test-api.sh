#!/bin/bash

# API Testing Script for CaseProject API
# Make sure your Laravel app is running before executing this script

BASE_URL="http://localhost:8000/api"

echo "=========================================="
echo "CaseProject API Testing with Authentication"
echo "=========================================="
echo ""

# 1. Login to get token
echo "1. Testing Login..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@rafatax.com",
    "password": "password123"
  }')

echo "$LOGIN_RESPONSE" | jq '.'

# Extract token from response
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token')

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ Login failed! Cannot get token."
    exit 1
fi

echo "✅ Login successful! Token: ${TOKEN:0:20}..."
echo ""

# 2. Get current user info
echo "2. Testing Get Current User Info..."
curl -s -X GET "$BASE_URL/me" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 3. Get all case projects (paginated)
echo "3. Testing Get All Case Projects..."
curl -s -X GET "$BASE_URL/case-projects" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 4. Create new case project
echo "4. Testing Create Case Project..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/case-projects" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "description": "Test Case dari API Testing",
    "case_date": "2025-11-10",
    "status": "open",
    "staff_id": 1,
    "client_id": 1,
    "link_dokumen": "https://example.com/test.pdf"
  }')

echo "$CREATE_RESPONSE" | jq '.'

# Extract case project ID
CASE_ID=$(echo "$CREATE_RESPONSE" | jq -r '.data.id')

if [ "$CASE_ID" != "null" ] && [ ! -z "$CASE_ID" ]; then
    echo "✅ Case Project created with ID: $CASE_ID"
    echo ""
    
    # 5. Get single case project
    echo "5. Testing Get Single Case Project (ID: $CASE_ID)..."
    curl -s -X GET "$BASE_URL/case-projects/$CASE_ID" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Accept: application/json" | jq '.'
    echo ""
    
    # 6. Update case project
    echo "6. Testing Update Case Project (ID: $CASE_ID)..."
    curl -s -X PUT "$BASE_URL/case-projects/$CASE_ID" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{
        "status": "in_progress",
        "description": "Updated description from API test"
      }' | jq '.'
    echo ""
    
    # 7. Delete case project
    echo "7. Testing Delete Case Project (ID: $CASE_ID)..."
    curl -s -X DELETE "$BASE_URL/case-projects/$CASE_ID" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Accept: application/json" | jq '.'
    echo ""
fi

# 8. Test without authentication (should fail)
echo "8. Testing Access Without Token (Should Fail)..."
curl -s -X GET "$BASE_URL/case-projects" \
  -H "Accept: application/json" | jq '.'
echo ""

# 9. Logout
echo "9. Testing Logout..."
curl -s -X POST "$BASE_URL/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

echo "=========================================="
echo "✅ API Testing Completed!"
echo "=========================================="

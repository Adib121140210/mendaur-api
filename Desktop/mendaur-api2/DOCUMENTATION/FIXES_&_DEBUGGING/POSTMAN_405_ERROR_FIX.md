# 🔧 Fix: Postman "405 Method Not Allowed" Error

## 🐛 Problem

```
Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
The GET method is not supported for route api/login. Supported methods: POST.
```

**Issue:** Postman is sending a **GET** request instead of **POST** to `/api/login`

---

## ✅ Solution: Configure Postman Correctly

### Step 1: Set Request Method to POST

1. Open Postman
2. In the dropdown next to the URL bar, select **`POST`** (NOT GET)
3. Enter URL: `http://127.0.0.1:8000/api/login`

### Step 2: Set Headers

Click on the **"Headers"** tab and add:

| Key | Value |
|-----|-------|
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

### Step 3: Set Request Body

1. Click on the **"Body"** tab
2. Select **`raw`**
3. In the dropdown on the right, select **`JSON`**
4. Enter the login credentials:

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

### Step 4: Send Request

Click the blue **"Send"** button

---

## 📋 Complete Postman Configuration

### 1️⃣ LOGIN (Get Token)

**Method:** `POST`  
**URL:** `http://127.0.0.1:8000/api/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
  "email": "adib@example.com",
  "password": "password123"
}
```

**Expected Response (200):**
```json
{
  "status": "success",
  "message": "Login successful",
  "user": {
    "id": 1,
    "nama": "Adib",
    "email": "adib@example.com",
    "total_poin": 150
  },
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}
```

**Copy the token from the response!**

---

### 2️⃣ SUBMIT CASH WITHDRAWAL

**Method:** `POST`  
**URL:** `http://127.0.0.1:8000/api/penarikan-tunai`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```

**Body (raw JSON):**
```json
{
  "user_id": 1,
  "jumlah_poin": 5000,
  "nomor_rekening": "1234567890",
  "nama_bank": "BCA",
  "nama_penerima": "Adib Test"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Permintaan penarikan tunai berhasil diajukan",
  "data": {
    "id": 1,
    "user_id": 1,
    "jumlah_poin": 5000,
    "jumlah_rupiah": 50000,
    "status": "pending"
  }
}
```

---

### 3️⃣ GET WITHDRAWAL HISTORY

**Method:** `GET`  
**URL:** `http://127.0.0.1:8000/api/penarikan-tunai`

**Headers:**
```
Accept: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```

**Body:** None (leave empty)

---

### 4️⃣ ADMIN APPROVE WITHDRAWAL

**Method:** `POST`  
**URL:** `http://127.0.0.1:8000/api/admin/penarikan-tunai/1/approve`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Body (raw JSON):**
```json
{
  "catatan_admin": "Transfer berhasil dilakukan"
}
```

---

### 5️⃣ ADMIN REJECT WITHDRAWAL

**Method:** `POST`  
**URL:** `http://127.0.0.1:8000/api/admin/penarikan-tunai/1/reject`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Body (raw JSON):**
```json
{
  "catatan_admin": "Nomor rekening tidak valid"
}
```

---

## 🎯 Quick Checklist

When using Postman, always check:

- [ ] **Method is correct** (POST, GET, PUT, DELETE)
- [ ] **URL is correct** (starts with `http://127.0.0.1:8000/api/`)
- [ ] **Headers include:**
  - `Accept: application/json`
  - `Content-Type: application/json` (for POST/PUT with body)
  - `Authorization: Bearer {token}` (for protected routes)
- [ ] **Body tab:**
  - Select `raw`
  - Select `JSON` from dropdown
  - Valid JSON syntax (no trailing commas, proper quotes)
- [ ] **Server is running** (`php artisan serve`)

---

## 🔍 Common Postman Mistakes

### ❌ Mistake 1: Using GET instead of POST
```
GET http://127.0.0.1:8000/api/login  ❌
```
**Fix:** Change to POST method

### ❌ Mistake 2: Forgetting Content-Type header
```
Headers: (empty)  ❌
```
**Fix:** Add `Content-Type: application/json`

### ❌ Mistake 3: Body type not set to JSON
```
Body: raw (Text selected)  ❌
```
**Fix:** Select `JSON` from dropdown

### ❌ Mistake 4: Missing Authorization token
```
Headers: (no Authorization header)  ❌
```
**Fix:** Add `Authorization: Bearer YOUR_TOKEN`

### ❌ Mistake 5: Invalid JSON in body
```json
{
  "email": "test@example.com",
  "password": "password",  ❌ trailing comma
}
```
**Fix:** Remove trailing comma

---

## 📸 Postman Screenshot Guide

### For POST Request:

```
┌─────────────────────────────────────────────────────────┐
│ POST ▼  http://127.0.0.1:8000/api/login        Send    │
├─────────────────────────────────────────────────────────┤
│ Params  Authorization  Headers  Body  Pre-request...   │
│                                   ▲                      │
│                                   │ Click here          │
├─────────────────────────────────────────────────────────┤
│ ○ none  ○ form-data  ○ x-www-form-urlencoded          │
│ ● raw   ○ binary  ○ GraphQL                            │
│                                                          │
│ Text ▼  JSON ▼  ← Select JSON here                     │
│ ┌────────────────────────────────────────────────────┐ │
│ │ {                                                   │ │
│ │   "email": "adib@example.com",                     │ │
│ │   "password": "password123"                        │ │
│ │ }                                                   │ │
│ └────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### Headers Tab:

```
┌─────────────────────────────────────────────────────────┐
│ Headers (2)                                             │
├─────────────────────────────────────────────────────────┤
│ KEY               │ VALUE                               │
├───────────────────┼─────────────────────────────────────┤
│ Content-Type      │ application/json                    │
│ Accept            │ application/json                    │
│ Authorization     │ Bearer 1|abc123...                  │
└─────────────────────────────────────────────────────────┘
```

---

## 🧪 Testing All Endpoints

### Test Flow:

1. **Login** (POST) → Get token
2. **Submit Withdrawal** (POST) → Use token
3. **Get History** (GET) → Use token
4. **Admin Approve** (POST) → Use admin token
5. **Admin Reject** (POST) → Use admin token

---

## 🚀 PowerShell Alternative (If Postman Still Issues)

If Postman continues to have problems, you can test directly with PowerShell:

### Login:
```powershell
$body = @{
    email = "adib@example.com"
    password = "password123"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/login' `
    -Method Post `
    -Body $body `
    -ContentType 'application/json'

$token = $response.token
Write-Host "Token: $token"
```

### Submit Withdrawal:
```powershell
$body = @{
    user_id = 1
    jumlah_poin = 5000
    nomor_rekening = "1234567890"
    nama_bank = "BCA"
    nama_penerima = "Adib Test"
} | ConvertTo-Json

$headers = @{
    Authorization = "Bearer $token"
}

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/penarikan-tunai' `
    -Method Post `
    -Body $body `
    -ContentType 'application/json' `
    -Headers $headers | ConvertTo-Json -Depth 3
```

---

## ✅ Solution Summary

**Problem:** Postman using GET instead of POST  
**Solution:**
1. ✅ Change request method to **POST**
2. ✅ Set headers: `Content-Type: application/json`
3. ✅ Select Body → raw → **JSON**
4. ✅ Add valid JSON body
5. ✅ Click Send

**Status:** Ready to test! 🚀

---

**Created:** November 17, 2025  
**Issue:** 405 Method Not Allowed  
**Resolution:** Configure Postman POST method correctly

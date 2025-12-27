# Admin Leaderboard API

## Overview
Endpoint untuk manajemen leaderboard oleh admin. Semua endpoint memerlukan autentikasi (Bearer token) dan role admin.

## Base URL
```
http://127.0.0.1:8000/api/admin/leaderboard
```

## Authentication
Semua request harus menyertakan header:
```
Authorization: Bearer <token>
```

## Endpoints

### 1. Get Leaderboard
```
GET /api/admin/leaderboard
```

**Query Parameters:**
- `period` (optional): `weekly`, `monthly`, `quarterly`, `yearly` (default: `monthly`)
- `limit` (optional): max 500 (default: 100)

**Example:**
```bash
curl -X GET "http://127.0.0.1:8000/api/admin/leaderboard?period=monthly&limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 2. Get Leaderboard Settings
```
GET /api/admin/leaderboard/settings
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "reset_period": "monthly",
    "auto_reset": false,
    "next_reset_date": "2026-01-01",
    "last_reset_date": null,
    "current_season": "December 2025"
  }
}
```

**Example:**
```bash
curl -X GET "http://127.0.0.1:8000/api/admin/leaderboard/settings" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 3. Update Leaderboard Settings
```
PUT /api/admin/leaderboard/settings
```

**Request Body:**
```json
{
  "reset_period": "monthly",
  "auto_reset": true
}
```

| Field | Type | Required | Values |
|-------|------|----------|--------|
| `reset_period` | string | Yes | `weekly`, `monthly`, `quarterly`, `yearly` |
| `auto_reset` | boolean | Yes | `true`, `false` |

**Example:**
```bash
curl -X PUT "http://127.0.0.1:8000/api/admin/leaderboard/settings" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reset_period": "monthly", "auto_reset": true}'
```

---

### 4. Reset Leaderboard (Manual)
```
POST /api/admin/leaderboard/reset
```

**Request Body:**
```json
{
  "confirm": true
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `confirm` | boolean | Yes | Must be `true` to confirm reset |

**Response:**
```json
{
  "status": "success",
  "message": "Leaderboard berhasil direset",
  "data": {
    "affected_users": 150,
    "reset_date": "2025-12-26 15:30:00",
    "previous_top_users": [
      {"user_id": 1, "nama": "User A", "total_poin": 5000},
      {"user_id": 2, "nama": "User B", "total_poin": 4500}
    ]
  }
}
```

**Example:**
```bash
curl -X POST "http://127.0.0.1:8000/api/admin/leaderboard/reset" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"confirm": true}'
```

---

### 5. Get Reset History
```
GET /api/admin/leaderboard/history
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "reset_date": "2025-12-01T00:00:00.000000Z",
      "admin_id": 1,
      "affected_users": 100,
      "season_data": {
        "season": "November 2025",
        "top_users": [...]
      }
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://127.0.0.1:8000/api/admin/leaderboard/history" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated. Please login first.",
  "error": "Authentication required"
}
```

### 403 Forbidden (Not Admin)
```json
{
  "status": "error",
  "message": "Anda tidak memiliki akses ke fitur ini. Hanya admin yang diizinkan."
}
```

### 422 Validation Error
```json
{
  "message": "The confirm field must be accepted.",
  "errors": {
    "confirm": ["The confirm field must be accepted."]
  }
}
```

---

## Frontend Integration Notes

### Using Fetch (JavaScript)
```javascript
const API_BASE = 'http://127.0.0.1:8000';

async function resetLeaderboard(token) {
  const response = await fetch(`${API_BASE}/api/admin/leaderboard/reset`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({ confirm: true }),
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Reset failed');
  }

  return response.json();
}
```

### CORS Configuration
Backend sudah dikonfigurasi untuk menerima request dari:
- `http://localhost:5173` (Vite dev)
- `http://127.0.0.1:5173`
- `http://localhost:3000`

Pastikan frontend memanggil backend URL yang benar (`http://127.0.0.1:8000`), bukan URL frontend.

---

## Testing with PowerShell

```powershell
# Get settings
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/admin/leaderboard/settings" `
  -Headers @{ Authorization = "Bearer YOUR_TOKEN" }

# Reset leaderboard
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/admin/leaderboard/reset" `
  -Method POST `
  -Headers @{ Authorization = "Bearer YOUR_TOKEN"; "Content-Type" = "application/json" } `
  -Body '{"confirm": true}'
```

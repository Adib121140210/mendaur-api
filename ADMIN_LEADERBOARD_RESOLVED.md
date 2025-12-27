# Admin Leaderboard API - Integration Guide

## Overview
API endpoints untuk mengelola leaderboard admin. Semua endpoint memerlukan authentication dengan Bearer token dan role admin.

## Fixed Issues
✅ CORS configuration - Frontend dapat mengakses API dari http://localhost:5173 dan http://127.0.0.1:5173  
✅ APP_URL configuration - Mengatasi redirect ke localhost  
✅ Admin middleware - Case-sensitive role checking fixed  
✅ Database constraints - AuditLog resource_id nullable issue fixed  
✅ SQL column mapping - User table column name consistency  

## Base URL
```
http://127.0.0.1:8000/api/admin
```

## Authentication
Semua endpoint memerlukan:
```javascript
headers: {
  'Authorization': 'Bearer YOUR_ADMIN_TOKEN',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

## Endpoints

### 1. Get Leaderboard Settings
**GET** `/leaderboard/settings`

**Response:**
```json
{
  "status": "success",
  "data": {
    "reset_period": "monthly",
    "auto_reset": false,
    "next_reset_date": "2026-01-01",
    "last_reset_date": "2025-12-26 16:04:15",
    "current_season": "December 2025",
    "total_participants": 10
  }
}
```

### 2. Update Leaderboard Settings
**PUT** `/leaderboard/settings`

**Body:**
```json
{
  "reset_period": "monthly", // "monthly", "weekly", "manual"
  "auto_reset": false
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Pengaturan leaderboard berhasil diperbarui",
  "data": {
    "reset_period": "monthly",
    "auto_reset": false,
    "updated_at": "2025-12-26T16:04:15.000000Z"
  }
}
```

### 3. Reset Leaderboard (CRITICAL ENDPOINT)
**POST** `/leaderboard/reset`

**Body:**
```json
{
  "confirm": true
}
```

**Response:**
```json
{
  "status": "success", 
  "message": "Leaderboard berhasil direset",
  "data": {
    "affected_users": 6,
    "reset_date": "2025-12-26T16:04:15.000000Z",
    "previous_top_users": [
      {
        "user_id": 1,
        "nama": "Admin User",
        "total_poin": 1500,
        "level": "Admin"
      }
    ]
  }
}
```

### 4. Get Reset History
**GET** `/leaderboard/history`

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "reset_date": "2025-12-26T09:04:15.000000Z",
      "admin_id": 1,
      "affected_users": 6,
      "season_data": {},
      "previous_top_users": []
    }
  ]
}
```

## Frontend Implementation Example

### JavaScript/Fetch
```javascript
// Reset Leaderboard
async function resetLeaderboard() {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/admin/leaderboard/reset', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ confirm: true })
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    console.log('Reset successful:', result);
    return result;
  } catch (error) {
    console.error('Reset failed:', error);
    throw error;
  }
}
```

### React/Axios Example
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/admin',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('adminToken')}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Reset leaderboard
const handleResetLeaderboard = async () => {
  try {
    const response = await api.post('/leaderboard/reset', { confirm: true });
    alert(`Reset berhasil! ${response.data.data.affected_users} user terdampak.`);
  } catch (error) {
    console.error('Error:', error.response?.data || error.message);
  }
};
```

## Data Safety Confirmation ✅
**PENTING**: Reset leaderboard hanya mengatur ulang `total_poin` user menjadi 0.  
**TIDAK ADA DATA YANG DIHAPUS**:
- ❌ Tidak menghapus tabung_sampah
- ❌ Tidak menghapus transaksi  
- ❌ Tidak menghapus poin_transaksi
- ❌ Tidak menghapus user data
- ✅ Hanya reset kolom `total_poin` ke 0

## Error Handling

### Common Errors:
1. **401 Unauthorized** - Token tidak valid atau expired
2. **403 Forbidden** - User bukan admin  
3. **422 Validation Error** - Parameter `confirm: true` tidak diberikan
4. **500 Server Error** - Database atau server issue

### Error Response Format:
```json
{
  "status": "error",
  "message": "Detailed error message",
  "errors": {
    "field": ["validation error details"]
  }
}
```

## Testing Commands (PowerShell)
```powershell
# Test GET settings
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/admin/leaderboard/settings" -Method GET -Headers @{"Authorization"="Bearer YOUR_TOKEN"; "Accept"="application/json"}

# Test POST reset
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/admin/leaderboard/reset" -Method POST -Headers @{"Authorization"="Bearer YOUR_TOKEN"; "Content-Type"="application/json"} -Body '{"confirm":true}'

# Test GET history  
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/admin/leaderboard/history" -Method GET -Headers @{"Authorization"="Bearer YOUR_TOKEN"; "Accept"="application/json"}
```

## Status: RESOLVED ✅
- CORS issue: **Fixed**
- 500 Internal Server Error: **Fixed**  
- Admin middleware: **Fixed**
- Database constraints: **Fixed**
- Endpoint functionality: **Verified Working**

All admin leaderboard endpoints are now fully functional and ready for frontend integration!

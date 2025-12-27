# 🔄 API ROUTING CLARIFICATION - Frontend vs Backend

**Issue:** Frontend using `/api/admin/...` routes  
**Status:** ✅ CORRECT! Routes are properly structured  
**Date:** December 23, 2025

---

## ✅ GOOD NEWS!

Your backend **IS correctly implementing** the `/api/admin/...` route structure!

```bash
✅ GET    /api/admin/penyetoran-sampah
✅ PATCH  /api/admin/penyetoran-sampah/{id}/approve
✅ GET    /api/admin/dashboard/overview
✅ POST   /api/admin/points/award
... and 50+ more
```

The frontend is **correctly expecting** these routes.

---

## 📋 VERIFIED ROUTE STRUCTURE

### Current Backend Routes (VERIFIED ✅)

**Admin Routes (50+ endpoints):**
```
api/admin/activity-logs
api/admin/analytics/points
api/admin/analytics/waste
api/admin/analytics/waste-by-user
api/admin/badges
api/admin/dashboard/overview
api/admin/dashboard/stats
api/admin/dashboard/point-summary
api/admin/dashboard/waste-summary
api/admin/dashboard/waste-by-user
api/admin/penarikan-tunai
api/admin/penukar-produk
api/admin/penyetoran-sampah
api/admin/points/award
api/admin/points/history
api/admin/reports/generate
api/admin/users
```

**Public Routes (also exist):**
```
api/login
api/logout
api/profile
api/notifications
api/badges
... (regular user endpoints)
```

---

## 🎯 ROUTE ORGANIZATION

```
/api/                          ← Root API
├── /admin/                    ← Admin Panel Routes (Protected)
│   ├── /dashboard/
│   ├── /penyetoran-sampah/    ← Waste Deposits
│   ├── /penukar-produk/       ← Product Redemption
│   ├── /penarikan-tunai/      ← Cash Withdrawal
│   ├── /analytics/            ← Analytics
│   ├── /badges/               ← Badge Management
│   ├── /users/                ← User Management
│   ├── /points/               ← Points Management
│   ├── /activity-logs/        ← Activity Logs
│   ├── /reports/              ← Reports
│   └── ...
│
├── /login                     ← Authentication
├── /logout
├── /profile
├── /notifications/            ← User Notifications
├── /badges/                   ← User Badges
├── /transactions/             ← User Transactions
└── ...                        ← Other Public Routes
```

---

## 🔧 HOW FRONTEND SHOULD CONFIGURE

### Option 1: Environment-Based Configuration (RECOMMENDED)

**Frontend `.env.local`:**
```env
# Development
REACT_APP_API_URL=http://localhost:8000/api

# Or Staging
REACT_APP_API_URL=https://staging-api.mendaur.com/api

# Or Production
REACT_APP_API_URL=https://api.mendaur.com/api
```

**Frontend API Client (`src/api/adminApi.js`):**
```javascript
// ✅ CORRECT WAY
class AdminAPI {
  constructor() {
    // Use environment variable - NO hardcoding
    this.baseURL = process.env.REACT_APP_API_URL;
    
    // Will automatically handle /api/admin/... paths
  }

  async getWasteDeposits(page = 1, limit = 10) {
    // Will call: http://localhost:8000/api/admin/penyetoran-sampah?page=1&limit=10
    const response = await fetch(
      `${this.baseURL}/admin/penyetoran-sampah?page=${page}&limit=${limit}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );
    return response.json();
  }
}
```

### Option 2: Simple Configuration

**Frontend API Client:**
```javascript
// ❌ WRONG - Hardcoded localhost
const API_URL = 'http://localhost:8000/api';

// ✅ CORRECT - From environment variable
const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';
```

---

## 🚨 COMMON FRONTEND MISTAKES

### ❌ Mistake 1: Double `/api` prefix
```javascript
// ❌ WRONG
const response = await fetch(
  `http://localhost:8000/api/api/admin/penyetoran-sampah`
);

// ✅ CORRECT
const response = await fetch(
  `http://localhost:8000/api/admin/penyetoran-sampah`
);
```

### ❌ Mistake 2: Hardcoded localhost
```javascript
// ❌ WRONG - Cannot change URL without editing code
const API_URL = 'http://localhost:8000/api';

// ✅ CORRECT - Change via .env.local
const API_URL = process.env.REACT_APP_API_URL;
```

### ❌ Mistake 3: Missing admin prefix
```javascript
// ❌ WRONG - /api/penyetoran-sampah doesn't exist
const response = await fetch(
  `${API_URL}/penyetoran-sampah`
);

// ✅ CORRECT - Include /admin/ for admin endpoints
const response = await fetch(
  `${API_URL}/admin/penyetoran-sampah`
);
```

---

## 📝 FRONTEND CHECKLIST

Before frontend team starts integrating, ensure they:

- [ ] Set `REACT_APP_API_URL` in `.env.local`
- [ ] DO NOT hardcode `http://localhost` anywhere
- [ ] Import token from localStorage for `Authorization` header
- [ ] Use `/admin/...` routes for admin endpoints
- [ ] Use `/api/...` routes for public endpoints
- [ ] Handle 401 (Unauthorized) errors with redirect to login
- [ ] Handle 403 (Forbidden) errors for insufficient permissions
- [ ] Implement proper error handling for all responses

---

## 🔐 AUTHENTICATION HEADERS

All admin routes require Bearer token:

```javascript
const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json'
};
```

---

## 📊 RESPONSE FORMAT VERIFICATION

All backend endpoints return this format:

```json
{
  "success": true,
  "data": [...],
  "message": "optional message"
}
```

**Frontend should ALWAYS check:**
```javascript
if (response.data.success) {
  // Use response.data.data
  const items = response.data.data;
} else {
  // Handle error
  console.error(response.data.message);
}
```

---

## 🎯 FINAL VERIFICATION

### What the Frontend Will Call:
```
GET http://localhost:8000/api/admin/penyetoran-sampah?page=1&limit=10
Headers: Authorization: Bearer {token}
```

### What the Backend Receives:
```
Route: api/admin/penyetoran-sampah
Method: GET
Query: page=1, limit=10
Headers: Authorization header with token
```

### What the Backend Returns:
```json
{
  "success": true,
  "data": [
    {
      "penyetoran_id": 1,
      "user_id": 5,
      "nama_user": "John Doe",
      "berat_kg": 25.5,
      "status": "pending"
    }
  ]
}
```

### What the Frontend Gets:
```javascript
const response = await fetch('http://localhost:8000/api/admin/penyetoran-sampah');
const json = await response.json();

// json.success === true
// json.data === [{ penyetoran_id: 1, ... }]
```

---

## ✅ SUMMARY

| Aspect | Status | Details |
|:---|:---:|:---|
| **Backend Routes** | ✅ Correct | Uses `/api/admin/...` prefix |
| **Frontend Expectation** | ✅ Correct | Also expects `/api/admin/...` |
| **Route Structure** | ✅ Aligned | Both use same structure |
| **Response Format** | ✅ Defined | `{ success, data, message }` |
| **Authentication** | ✅ Bearer Token | Required for all admin routes |
| **Configuration** | ⚠️ Important | Use `REACT_APP_API_URL` env var |

---

## 📞 WHAT FRONTEND TEAM NEEDS TO DO

1. **Set environment variable** (`.env.local`):
   ```
   REACT_APP_API_URL=http://localhost:8000/api
   ```

2. **Create API client** that uses this variable
   ```javascript
   const baseURL = process.env.REACT_APP_API_URL;
   // Make requests to ${baseURL}/admin/...
   ```

3. **Add Bearer token** to all requests
   ```javascript
   headers: {
     'Authorization': `Bearer ${localStorage.getItem('token')}`
   }
   ```

4. **Test with actual backend** (not mock data)

---

## 🚀 DEPLOYMENT NOTES

**Development:**
```env
REACT_APP_API_URL=http://localhost:8000/api
```

**Staging:**
```env
REACT_APP_API_URL=https://staging-api.mendaur.com/api
```

**Production:**
```env
REACT_APP_API_URL=https://api.mendaur.com/api
```

No code changes needed - just different `.env` files!

---

**Status:** ✅ ROUTES ARE CORRECTLY ALIGNED  
**Action:** Share this with frontend team  
**Next Step:** Frontend implements with correct environment configuration


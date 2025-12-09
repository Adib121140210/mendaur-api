# 🧪 Admin Dashboard API - Testing Guide

**Status**: ✅ **IMPLEMENTATION COMPLETE**  
**Date**: November 22, 2025  
**Components**: 4 Admin Endpoints + Admin Middleware + Role-based Access Control

---

## ✨ What Was Implemented

### 1. **Authentication Enhancement**
- ✅ Added `role` field to login response
- ✅ Added `role` field to profile response  
- ✅ Role determined by `user.level` field:
  - `level = 'admin'` → `role = 'admin'`
  - Otherwise → `role = 'user'`

### 2. **Admin Middleware**
- **File**: `app/Http/Middleware/AdminMiddleware.php`
- **Protection**: Verifies user has admin role before granting access
- **Error Response**: 401 Unauthorized if not admin
- **Registration**: Aliased as 'admin' in `bootstrap/app.php`

### 3. **Four Admin Endpoints**

#### **Endpoint 1: GET /api/poin/admin/stats**
- **Purpose**: System-wide point statistics
- **Access**: Admin only (middleware protected)
- **Response Format**:
```json
{
  "status": "success",
  "data": {
    "total_points_in_system": 12500,
    "active_users": 45,
    "total_distributions": 1250,
    "recent_activity": [
      {
        "id": 1,
        "user_id": 5,
        "user_name": "Budi Santoso",
        "points": 100,
        "source": "setor_sampah",
        "description": "Penyetoran sampah plastik",
        "created_at": "2025-01-15T10:30:00Z"
      }
    ]
  }
}
```

#### **Endpoint 2: GET /api/poin/admin/history**
- **Purpose**: View all transactions with advanced filtering
- **Access**: Admin only
- **Parameters**:
  - `page` (default: 1)
  - `per_page` (default: 10)
  - `user_id` (optional)
  - `type` (optional - filter by sumber)
  - `start_date` (optional - YYYY-MM-DD)
  - `end_date` (optional - YYYY-MM-DD)

- **Example Request**:
```
GET /api/poin/admin/history?page=1&per_page=20&user_id=5&type=setor_sampah&start_date=2025-01-01&end_date=2025-01-31
```

- **Response Format**:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "user_name": "Budi Santoso",
      "points": 100,
      "source": "setor_sampah",
      "waste_type": "Plastik",
      "weight_kg": 2.5,
      "description": "Penyetoran sampah plastik",
      "created_at": "2025-01-15T10:30:00Z"
    }
  ],
  "total": 125,
  "page": 1,
  "per_page": 20,
  "total_pages": 7
}
```

#### **Endpoint 3: GET /api/poin/admin/redemptions**
- **Purpose**: View all product redemptions/exchanges
- **Access**: Admin only
- **Parameters**:
  - `page` (default: 1)
  - `per_page` (default: 8)
  - `user_id` (optional)
  - `status` (optional - pending, approved, cancelled)

- **Example Request**:
```
GET /api/poin/admin/redemptions?page=1&per_page=8&status=pending
```

- **Response Format**:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "user_name": "Budi Santoso",
      "product_id": 12,
      "product_name": "Voucher Belanja Rp 50.000",
      "product_image": "https://api.example.com/storage/products/voucher-1.jpg",
      "points_used": 500,
      "quantity": 1,
      "status": "pending",
      "pickup_method": "Email Transfer",
      "created_at": "2025-01-15T10:30:00Z",
      "pickup_date": null
    }
  ],
  "total": 45,
  "page": 1,
  "per_page": 8,
  "total_pages": 6
}
```

#### **Endpoint 4: GET /api/poin/breakdown/all**
- **Purpose**: System-wide point breakdown by source
- **Access**: Admin only
- **Response Format**:
```json
{
  "status": "success",
  "data": {
    "total_points": 12500,
    "sources": [
      {
        "source": "setor_sampah",
        "total_points": 8500,
        "transaction_count": 850,
        "percentage": 68.0
      },
      {
        "source": "bonus",
        "total_points": 2000,
        "transaction_count": 200,
        "percentage": 16.0
      },
      {
        "source": "badge",
        "total_points": 1500,
        "transaction_count": 150,
        "percentage": 12.0
      },
      {
        "source": "redemption",
        "total_points": 500,
        "transaction_count": 100,
        "percentage": 4.0
      }
    ]
  }
}
```

---

## 🧪 Testing Checklist

### **Phase 1: Authentication Tests**

- [ ] **Test 1.1**: Login with user account (non-admin)
  ```
  POST /api/login
  Email: user@example.com
  Password: password123
  ```
  Expected: Response includes `role: "user"`

- [ ] **Test 1.2**: Login with admin account
  ```
  POST /api/login
  Email: admin@example.com
  Password: adminpass123
  ```
  Expected: Response includes `role: "admin"`

- [ ] **Test 1.3**: Get profile with authentication
  ```
  GET /api/profile
  Authorization: Bearer {token}
  ```
  Expected: Response includes `role` field

### **Phase 2: Admin Middleware Tests**

- [ ] **Test 2.1**: Access admin endpoint with non-admin token
  ```
  GET /api/poin/admin/stats
  Authorization: Bearer {user_token}
  ```
  Expected: 401 Unauthorized response

- [ ] **Test 2.2**: Access admin endpoint with admin token
  ```
  GET /api/poin/admin/stats
  Authorization: Bearer {admin_token}
  ```
  Expected: 200 OK with stats data

### **Phase 3: Stats Endpoint Tests**

- [ ] **Test 3.1**: Get stats (authenticated as admin)
  ```
  GET /api/poin/admin/stats
  Authorization: Bearer {admin_token}
  ```
  Expected: Returns total_points_in_system, active_users, total_distributions, recent_activity

- [ ] **Test 3.2**: Verify recent_activity has user_name
  Expected: All items in recent_activity include `user_name` field

### **Phase 4: History Endpoint Tests**

- [ ] **Test 4.1**: Get all transactions without filters
  ```
  GET /api/poin/admin/history
  Authorization: Bearer {admin_token}
  ```
  Expected: Paginated list with page=1, per_page=10

- [ ] **Test 4.2**: Filter by user_id
  ```
  GET /api/poin/admin/history?user_id=5
  Authorization: Bearer {admin_token}
  ```
  Expected: Only transactions from user_id=5

- [ ] **Test 4.3**: Filter by type (sumber)
  ```
  GET /api/poin/admin/history?type=setor_sampah
  Authorization: Bearer {admin_token}
  ```
  Expected: Only transactions with source=setor_sampah

- [ ] **Test 4.4**: Filter by date range
  ```
  GET /api/poin/admin/history?start_date=2025-01-01&end_date=2025-01-31
  Authorization: Bearer {admin_token}
  ```
  Expected: Only transactions within date range

- [ ] **Test 4.5**: Pagination
  ```
  GET /api/poin/admin/history?page=2&per_page=20
  Authorization: Bearer {admin_token}
  ```
  Expected: Returns correct page data with total_pages

- [ ] **Test 4.6**: Verify all items have user_name
  Expected: Every transaction includes `user_name` field

### **Phase 5: Redemptions Endpoint Tests**

- [ ] **Test 5.1**: Get all redemptions
  ```
  GET /api/poin/admin/redemptions
  Authorization: Bearer {admin_token}
  ```
  Expected: Paginated list of all redemptions

- [ ] **Test 5.2**: Filter by user_id
  ```
  GET /api/poin/admin/redemptions?user_id=5
  Authorization: Bearer {admin_token}
  ```
  Expected: Only redemptions from user_id=5

- [ ] **Test 5.3**: Filter by status
  ```
  GET /api/poin/admin/redemptions?status=pending
  Authorization: Bearer {admin_token}
  ```
  Expected: Only redemptions with status=pending

- [ ] **Test 5.4**: Verify product_image is full URL
  Expected: `product_image` contains full URL (http/https), not just relative path

- [ ] **Test 5.5**: Verify user_name included
  Expected: Every redemption includes `user_name` field

### **Phase 6: Breakdown Endpoint Tests**

- [ ] **Test 6.1**: Get breakdown
  ```
  GET /api/poin/breakdown/all
  Authorization: Bearer {admin_token}
  ```
  Expected: Returns total_points and sources array

- [ ] **Test 6.2**: Verify percentage calculation
  Expected: Each source percentage = (source_total / total_points) * 100

- [ ] **Test 6.3**: Verify transaction_count
  Expected: Each source has transaction_count > 0

- [ ] **Test 6.4**: Verify sorting by amount DESC
  Expected: Sources sorted from highest points to lowest

---

## 🚀 Testing with Postman

### **Setup**
1. Create a new Postman collection named "Admin Dashboard API"
2. Add environment variable: `base_url` = `http://localhost:8000`
3. Add environment variable: `admin_token` = (token from admin login)
4. Add environment variable: `user_token` = (token from user login)

### **Request Templates**

**Template 1: Get Admin Stats**
```
GET {{base_url}}/api/poin/admin/stats
Headers:
  Authorization: Bearer {{admin_token}}
  Accept: application/json
```

**Template 2: Get History with Filters**
```
GET {{base_url}}/api/poin/admin/history?page=1&per_page=10&user_id=5&type=setor_sampah
Headers:
  Authorization: Bearer {{admin_token}}
  Accept: application/json
```

**Template 3: Get Redemptions**
```
GET {{base_url}}/api/poin/admin/redemptions?page=1&per_page=8&status=pending
Headers:
  Authorization: Bearer {{admin_token}}
  Accept: application/json
```

**Template 4: Get Breakdown**
```
GET {{base_url}}/api/poin/breakdown/all
Headers:
  Authorization: Bearer {{admin_token}}
  Accept: application/json
```

---

## 📝 Expected Outcomes

### ✅ All Tests Pass When:
1. **Authentication**: Role field appears in login and profile responses
2. **Middleware**: Non-admin users receive 401 for admin endpoints
3. **Stats**: Returns correct system-wide statistics with recent_activity
4. **History**: Filtering and pagination work correctly, user_name always included
5. **Redemptions**: Product images return as full URLs, user_name always included
6. **Breakdown**: Percentage calculations are accurate, sorted by amount DESC

### ❌ Common Issues & Fixes

**Issue**: Getting 404 for admin endpoints
- **Fix**: Ensure middleware is registered in `bootstrap/app.php`
- **Verify**: Run `php artisan route:list | grep poin/admin`

**Issue**: user_name is null in responses
- **Fix**: Ensure JOIN with users table is correct
- **Verify**: Check SQL query joins and select clauses

**Issue**: product_image is relative path
- **Fix**: Ensure using `asset()` helper to generate full URL
- **Verify**: `asset('storage/' . $item->product_image)` format

**Issue**: Getting 401 with admin token
- **Fix**: Ensure `user.level = 'admin'` in database
- **Verify**: Check user level in database: `SELECT id, level FROM users WHERE id = {id}`

---

## 🎯 Next Steps for Frontend

Once all tests pass, frontend team can:
1. Integrate `/api/poin/admin/stats` to display system statistics dashboard
2. Integrate `/api/poin/admin/history` for transaction history with filters
3. Integrate `/api/poin/admin/redemptions` for redemption management
4. Integrate `/api/poin/breakdown/all` for point source visualization

---

## 📌 Important Notes

- All timestamps are in **ISO 8601 format** (e.g., "2025-01-15T10:30:00Z")
- All monetary values are in **points** (not currency)
- **Sorting**: All lists are sorted by `created_at DESC` (newest first)
- **Pagination**: Uses `page`, `per_page`, `total`, `total_pages` format
- **All responses** include `status` field (success/error)
- **Error responses** include descriptive error messages
- **Admin middleware** checks both authentication and admin role

---

**Implementation Status**: ✅ **COMPLETE**  
**Ready for Testing**: ✅ **YES**  
**Ready for Frontend Integration**: ✅ **YES**

# 🚀 BACKEND IMPLEMENTATION QUICKSTART

**For:** Backend Development Team  
**Date:** December 23, 2025  
**Status:** Ready to start implementation  
**Frontend Status:** ✅ 100% complete (waiting for your APIs)

---

## 📌 TL;DR (Too Long; Didn't Read)

```
Frontend: DONE ✅
You need to create: 65+ API endpoints
Expected response format: { success: true, data: [...] }
Priority: 5 endpoints in Week 1 (Phase 1)
Difficulty: Medium (mostly CRUD operations)
Time estimate: 2-3 weeks for all endpoints
```

---

## 🎯 PHASE 1 - START HERE (5 endpoints, Week 1)

These are the **CRITICAL** endpoints. Everything else depends on these.

### 1. GET /api/admin/penyetoran-sampah
**What it does:** List all waste deposits  
**Why it's critical:** Main admin feature, users waiting for approval

**Request:**
```
GET /api/admin/penyetoran-sampah?page=1&limit=10&status=pending
Authorization: Bearer {token}
```

**Response (Required Format):**
```json
{
  "success": true,
  "data": [
    {
      "penyetoran_id": 1,
      "user_id": 5,
      "nama_user": "John Doe",
      "berat_kg": 25.5,
      "status": "pending",
      "tanggal": "2025-12-23T10:30:00Z"
    }
  ]
}
```

---

### 2. PATCH /api/admin/penyetoran-sampah/{id}/approve
**What it does:** Approve waste deposit and assign points

**Request:**
```
PATCH /api/admin/penyetoran-sampah/1/approve
Authorization: Bearer {token}
Content-Type: application/json

{
  "poin_diberikan": 250
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deposit approved successfully",
  "data": { "penyetoran_id": 1, "status": "approved", "poin_diberikan": 250 }
}
```

---

### 3. PATCH /api/admin/penyetoran-sampah/{id}/reject
**What it does:** Reject waste deposit with reason

**Request:**
```
PATCH /api/admin/penyetoran-sampah/1/reject
Authorization: Bearer {token}

{
  "alasan_penolakan": "Berat tidak sesuai"
}
```

---

### 4. GET /api/admin/dashboard/overview
**What it does:** Dashboard statistics

**Response:**
```json
{
  "success": true,
  "data": {
    "total_waste_kg": 5000,
    "total_points_distributed": 125000,
    "total_users": 500,
    "pending_deposits": 50,
    "pending_redemptions": 25,
    "pending_withdrawals": 10
  }
}
```

---

### 5. POST /api/admin/points/award
**What it does:** Award points to a user

**Request:**
```json
{
  "user_id": 5,
  "points": 250,
  "reason": "Bonus penyetoran"
}
```

---

## ⏱️ PHASE 2 - WEEK 2 (20 endpoints)

After Phase 1 works, implement these categories:

**Product CRUD** (4 endpoints)
- GET /api/admin/produk
- GET /api/admin/produk/{id}
- POST /api/admin/produk
- PUT /api/admin/produk/{id}
- DELETE /api/admin/produk/{id}

**Article CRUD** (4 endpoints)
- GET /api/admin/artikel
- GET /api/admin/artikel/{id}
- POST /api/admin/artikel
- PUT /api/admin/artikel/{id}
- DELETE /api/admin/artikel/{id}

**User Management** (6 endpoints)
- GET /api/admin/users
- GET /api/admin/users/{id}
- PUT /api/admin/users/{id}
- PATCH /api/admin/users/{id}/status
- PATCH /api/admin/users/{id}/role
- DELETE /api/admin/users/{id}

**Waste Items** (4 endpoints)
- GET /api/admin/jenis-sampah
- POST /api/admin/jenis-sampah
- PUT /api/admin/jenis-sampah/{id}
- DELETE /api/admin/jenis-sampah/{id}

**Badges** (5 endpoints)
- GET /api/admin/badges
- POST /api/admin/badges
- PUT /api/admin/badges/{id}
- DELETE /api/admin/badges/{id}
- POST /api/admin/badges/{id}/assign

---

## 🗂️ STANDARD RESPONSE FORMAT (REQUIRED)

### Success Response
```json
{
  "success": true,
  "data": [...],
  "message": "optional message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "data": null
}
```

### Pagination
When using `page` and `limit` parameters:
- Default: page=1, limit=10
- Response should include `data` array with items

---

## 🔐 AUTHENTICATION

**All endpoints require:**
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

**401 Error** if token missing or invalid  
**403 Error** if user doesn't have admin role

---

## ❌ COMMON MISTAKES TO AVOID

1. ❌ Response format like: `{ data: [...] }` (missing `success` field)
   ✅ Should be: `{ success: true, data: [...] }`

2. ❌ Returning only the data array instead of object
   ✅ Always wrap in `{ success: true, data: [...] }`

3. ❌ Not implementing all CRUD operations (Get, Create, Update, Delete)
   ✅ Most endpoints need all 5 operations

4. ❌ Not handling pagination for list endpoints
   ✅ Add `page` and `limit` query parameters

5. ❌ Not returning proper HTTP status codes
   ✅ 200 (success), 400 (bad request), 401 (unauthorized), 404 (not found), 500 (error)

---

## 📋 DATABASE RELATIONSHIPS

```
Users (nasabah) ←→ Penyetoran Sampah
Users ←→ Penukar Produk ←→ Produk
Users ←→ Penarikan Tunai
Users ←→ Jadwal Penyetoran (many-to-many)
Users ←→ Badges (many-to-many)
Users ←→ Notification
Jenis Sampah → Kategori Sampah
Artikel ← User (penulis)
```

---

## ✅ TESTING CHECKLIST

For each endpoint, test:
- [ ] Happy path (correct data, 200 response)
- [ ] Missing required field (400 error)
- [ ] Invalid token (401 error)
- [ ] Non-existent ID (404 error)
- [ ] Pagination (page=2, limit=5)
- [ ] Search/filter parameters
- [ ] Response format matches spec
- [ ] All fields in response are correct type

---

## 🔗 DETAILED DOCUMENTATION

For complete specifications, see:

1. **ADMIN_API_ENDPOINTS_SPEC.md**
   - Full endpoint details
   - Request/response examples
   - Parameter descriptions

2. **ADMIN_FEATURES_CHECKLIST.md**
   - Feature descriptions
   - Business logic
   - Implementation notes

3. **ADMIN_COMPONENTS_INVENTORY.md**
   - Frontend component details
   - How data is used
   - UI/UX context

---

## 🚀 QUICK START COMMAND

```bash
# Test Phase 1 endpoints with curl
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://127.0.0.1:8000/api/admin/penyetoran-sampah?page=1&limit=10"
```

---

## 📞 INTEGRATION CHECKLIST

Before frontend integration:
- [ ] All 65 endpoints created
- [ ] Response format standardized
- [ ] Authentication working (Bearer token)
- [ ] Authorization checks (admin/superadmin)
- [ ] Input validation on all endpoints
- [ ] Error handling consistent
- [ ] HTTP status codes correct
- [ ] Pagination implemented
- [ ] Search/filter working
- [ ] CORS enabled (if needed)
- [ ] Tested with Postman/curl
- [ ] Database migrations done
- [ ] Activity logging implemented (optional but good)

---

## 🎯 SUCCESS CRITERIA

✅ Phase 1 Complete when:
- All 5 endpoints working
- Response format correct
- Admin can approve/reject deposits
- Dashboard shows stats
- Points awarded correctly

✅ Phase 2 Complete when:
- All CRUD operations working
- Search/filter working
- Pagination working
- All 20 endpoints tested

✅ Phase 3-4 Complete when:
- All 65 endpoints working
- Performance acceptable
- Error handling robust
- Ready for production

---

## 📊 ENDPOINT BREAKDOWN

```
QUICK REFERENCE TABLE:

Method  | Endpoint                           | Purpose        | Priority
--------|------------------------------------|--------------:|----------:|
GET     | /api/admin/penyetoran-sampah       | List deposits  | P1 (Week1)
PATCH   | /api/admin/penyetoran-sampah/{id}/approve | Approve | P1
PATCH   | /api/admin/penyetoran-sampah/{id}/reject  | Reject  | P1
GET     | /api/admin/dashboard/overview      | Stats          | P1
POST    | /api/admin/points/award            | Award points   | P1
---     | ---                                | ---            | ---
GET     | /api/admin/produk                  | List products  | P2 (Week2)
POST    | /api/admin/produk                  | Create product | P2
PUT     | /api/admin/produk/{id}             | Update product | P2
DELETE  | /api/admin/produk/{id}             | Delete product | P2
... (60+ more endpoints in similar format)
```

---

## 💬 QUESTIONS?

Reference these docs in order:
1. This file (overview & quick start)
2. ADMIN_API_ENDPOINTS_SPEC.md (detailed specs)
3. ADMIN_FEATURES_CHECKLIST.md (business logic)

Good luck! 🚀

---

*Created for Backend Development Team*  
*Frontend Status: ✅ READY | Backend Status: 🔴 NOT STARTED*  
*Let's build this together!*

# 🔐 ANALISIS LENGKAP FITUR, API ENDPOINTS & SECURITY - MENDAUR FRONTEND

**Tanggal:** 26 Desember 2025  
**Dokumen:** Analisis Frontend untuk Blackbox Testing  
**Tujuan:** Mapping lengkap fitur dengan endpoint API dan security layer

---

## 📋 RINGKASAN EXECUTIVE

| Kategori | Jumlah |
|----------|--------|
| **Total Fitur** | 15+ Fitur Utama |
| **Total Endpoints** | 120+ Endpoints |
| **Authentication Methods** | Bearer Token (JWT) |
| **Middleware** | auth:sanctum |
| **Role-Based Access** | Superadmin, Admin, Nasabah |
| **Services** | 7 Services (adminApi, authService, badgeService, etc.) |

---

## 🔐 SECURITY LAYER

### 1. Authentication System

#### **Metode Autentikasi**
- **Type:** Bearer Token (JWT)
- **Storage:** localStorage
- **Header Format:** `Authorization: Bearer {token}`

#### **Endpoints**
| Method | Endpoint | Middleware | Role | Deskripsi |
|--------|----------|------------|------|-----------|
| POST | `/api/login` | - | Public | User login |
| POST | `/api/register` | - | Public | User registration |
| POST | `/api/logout` | auth:sanctum | All | User logout |
| POST | `/api/forgot-password` | - | Public | Request password reset |
| POST | `/api/reset-password` | - | Public | Reset password dengan token |
| POST | `/api/verify-otp` | - | Public | Verify OTP code |

#### **Security Implementation**
```javascript
// File: src/services/authService.js
const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
}
```

#### **Stored Data**
- `token` - JWT authentication token
- `user` - User object (JSON string)
- `userRole` - Role: 'nasabah', 'admin', 'superadmin'
- `userId` - User ID

---

### 2. Role-Based Access Control (RBAC)

#### **Roles Hierarchy**
```
Superadmin (Level tertinggi)
    ├── Dapat akses ke semua endpoint
    ├── Manage admin users
    └── Manage roles & permissions
        
Admin (Level menengah)
    ├── Manage nasabah
    ├── Approve/reject transactions
    └── Manage content (artikel, produk, badges)
        
Nasabah (Level dasar)
    ├── Access personal data
    ├── Submit transactions
    └── Redeem points
```

#### **Permission Check Functions**
```javascript
// File: src/services/authService.js
isNasabah()      // Check if user is Nasabah
isAdmin()        // Check if user is Admin or Superadmin
isSuperAdmin()   // Check if user is Superadmin
canAccessAdmin() // Check if can access admin panel
```

---

## 📱 FITUR & API ENDPOINTS

### **FITUR 1: AUTHENTICATION & AUTHORIZATION**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| POST | `/api/login` | - | Public | authService.js |
| POST | `/api/register` | - | Public | authService.js |
| POST | `/api/logout` | auth:sanctum | All | authService.js |
| GET | `/api/profile` | auth:sanctum | All | api.js |
| PUT | `/api/profile` | auth:sanctum | All | api.js |

#### Security Layer
- **Authentication:** Bearer Token
- **Validation:** Email format, password strength (min 8 chars)
- **Session:** Token stored in localStorage
- **Auto-Logout:** On 401 response from token expiry

#### Frontend Components
- `src/Components/Pages/login/login.jsx`
- `src/Components/Pages/register/register.jsx`
- `src/services/authService.js`

---

### **FITUR 2: DASHBOARD NASABAH (HOME)**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/dashboard/stats/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |
| GET | `/api/dashboard/leaderboard` | auth:sanctum | All | homeContent.jsx |
| GET | `/api/users/{userId}/badges` | auth:sanctum | Nasabah | homeContent.jsx |
| GET | `/api/setor-sampah/user/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |
| GET | `/api/penukaran-produk/user/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |
| GET | `/api/penarikan-tunai/user/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |
| GET | `/api/users/{userId}/aktivitas` | auth:sanctum | Nasabah | homeContent.jsx |

#### Security Layer
- **Authentication:** Required (Bearer Token)
- **Authorization:** User can only access own data (userId validation)
- **Data Privacy:** Personal statistics not shared

#### Frontend Components
- `src/Components/Pages/home/homeContent.jsx`

---

### **FITUR 3: TABUNG SAMPAH (WASTE DEPOSIT)**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/jadwal-penyetoran` | auth:sanctum | Nasabah | FormSetorSampah.jsx |
| POST | `/api/tabung-sampah` | auth:sanctum | Nasabah | FormSetorSampah.jsx |
| GET | `/api/setor-sampah/user/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |

#### Security Layer
- **Authentication:** Required
- **Validation:** 
  - Jadwal harus valid dan aktif
  - Berat sampah > 0
  - Foto bukti required
- **File Upload:** Secure image upload (max 2MB)

#### Frontend Components
- `src/Components/Form/FormSetorSampah.jsx`

---

### **FITUR 4: TUKAR POIN (PRODUCT REDEMPTION)**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/produk` | - | Public | productApi.js |
| GET | `/api/produk/{id}` | - | Public | productApi.js |
| POST | `/api/penukar-produk` | auth:sanctum | Nasabah | productApi.js |
| GET | `/api/penukaran-produk/user/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |

#### Security Layer
- **Authentication:** Required for redemption
- **Validation:**
  - Sufficient points balance
  - Product stock available
  - Valid product ID
- **Transaction:** Atomic point deduction

#### Frontend Components
- `src/Components/Pages/tukarPoin/`
- `src/services/productApi.js`

---

### **FITUR 5: PENARIKAN TUNAI (CASH WITHDRAWAL)**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| POST | `/api/penarikan-tunai` | auth:sanctum | Nasabah | - |
| GET | `/api/penarikan-tunai/user/{userId}` | auth:sanctum | Nasabah | homeContent.jsx |

#### Security Layer
- **Authentication:** Required
- **Validation:**
  - Minimum withdrawal amount
  - Sufficient point balance
  - Valid bank account info
- **Approval:** Requires admin approval

---

### **FITUR 6: BADGES & ACHIEVEMENTS**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/users/{userId}/badges` | auth:sanctum | All | api.js |
| GET | `/api/users/{userId}/unlocked-badges` | auth:sanctum | All | api.js |
| GET | `/api/users/{userId}/badge-title` | auth:sanctum | All | api.js |
| PUT | `/api/users/{userId}/badge-title` | auth:sanctum | Nasabah | api.js |
| GET | `/api/admin/badges` | auth:sanctum | Admin | badgeService.js |
| POST | `/api/admin/badges` | auth:sanctum | Admin | badgeService.js |
| PUT | `/api/admin/badges/{id}` | auth:sanctum | Admin | badgeService.js |
| DELETE | `/api/admin/badges/{id}` | auth:sanctum | Admin | badgeService.js |
| POST | `/api/admin/badges/{id}/assign` | auth:sanctum | Admin | adminApi.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** 
  - Nasabah: View own badges, set title
  - Admin: Full CRUD on badges
- **Validation:** Badge criteria check before unlocking

#### Frontend Components
- `src/services/badgeService.js`
- Badge display components

---

### **FITUR 7: LEADERBOARD**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/dashboard/leaderboard` | auth:sanctum | All | leaderboardTable.jsx |
| GET | `/api/admin/leaderboard/settings` | auth:sanctum | Admin | LeaderboardManagement.jsx |
| POST | `/api/admin/leaderboard/settings` | auth:sanctum | Admin | LeaderboardManagement.jsx |
| POST | `/api/admin/leaderboard/reset` | auth:sanctum | Admin | LeaderboardManagement.jsx |
| GET | `/api/admin/leaderboard/history` | auth:sanctum | Admin | LeaderboardManagement.jsx |

#### Security Layer
- **Authentication:** Required
- **Authorization:** 
  - All users: View leaderboard
  - Admin: Reset & configure leaderboard
- **Data Privacy:** Only show public ranking data

#### Frontend Components
- `src/Components/Pages/leaderboard/leaderboardTable.jsx`
- `src/Components/Pages/adminDashboard/components/LeaderboardManagement.jsx`

---

### **FITUR 8: NOTIFICATIONS**

#### Endpoints (User - Nasabah)
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/notifications` | auth:sanctum | All | notificationService.js |
| GET | `/api/notifications/unread` | auth:sanctum | All | notificationService.js |
| GET | `/api/notifications/unread-count` | auth:sanctum | All | notificationService.js |
| GET | `/api/notifications/{id}` | auth:sanctum | All | notificationService.js |
| PATCH | `/api/notifications/{id}/read` | auth:sanctum | All | notificationService.js |
| PATCH | `/api/notifications/mark-all-read` | auth:sanctum | All | notificationService.js |

#### Endpoints (Admin)
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/notifications` | auth:sanctum | Admin | adminApi.js |
| POST | `/api/admin/notifications` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/notifications/{id}` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/notifications/templates` | auth:sanctum | Admin | - |

#### Security Layer
- **Authentication:** Required
- **Authorization:** 
  - Nasabah: View & mark own notifications
  - Admin: Create & send notifications
- **Data Privacy:** Users only see their own notifications

#### Frontend Components
- `src/Components/NotificationBell.jsx`
- `src/services/notificationService.js`
- `src/Components/Pages/adminDashboard/components/NotificationManagement.jsx`

---

### **FITUR 9: ARTIKEL (ARTICLES/CONTENT)**

#### Endpoints (Public/Nasabah)
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/artikel` | - | Public | api.js |
| GET | `/api/artikel/{slug}` | - | Public | api.js |

#### Endpoints (Admin)
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/artikel` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/artikel/{slug}` | auth:sanctum | Admin | adminApi.js |
| POST | `/api/admin/artikel` | auth:sanctum | Admin | adminApi.js |
| PUT | `/api/admin/artikel/{slug}` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/artikel/{slug}` | auth:sanctum | Admin | adminApi.js |

#### Security Layer
- **Authentication:** Not required for reading (public)
- **Authorization:** Admin only for CRUD operations
- **File Upload:** Secure image upload for thumbnail (max 2MB)
- **XSS Prevention:** Content sanitization before rendering

#### Frontend Components
- `src/Components/Pages/artikel/`
- `src/Components/Pages/artikelDetail/`

---

### **FITUR 10: ADMIN DASHBOARD - OVERVIEW**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/dashboard/overview` | auth:sanctum | Admin | adminApi.js |

#### Response Data
```json
{
  "total_users": 150,
  "total_active_users": 120,
  "total_waste_deposits": 450,
  "total_weight": 2500.5,
  "total_points_distributed": 125000,
  "total_redemptions": 80,
  "pending_approvals": 12,
  "monthly_growth": 15.5
}
```

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Middleware:** auth:sanctum
- **Smart 401 Handling:** Graceful fallback to mock data

#### Frontend Components
- `src/Components/Pages/adminDashboard/components/OverviewCards.jsx`

---

### **FITUR 11: ADMIN - USER MANAGEMENT**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/users` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/users/{id}` | auth:sanctum | Admin | adminApi.js |
| PUT | `/api/admin/users/{id}` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/users/{id}` | auth:sanctum | Admin | adminApi.js |
| POST | `/api/admin/users` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/users/{id}/activity-logs` | auth:sanctum | Admin | adminApi.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Validation:**
  - Email uniqueness
  - Password strength
  - Valid phone number
- **Audit Log:** All user changes logged

#### Frontend Components
- Admin dashboard user management section

---

### **FITUR 12: ADMIN - WASTE DEPOSIT MANAGEMENT**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/penyetoran-sampah` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/penyetoran-sampah/{id}` | auth:sanctum | Admin | adminApi.js |
| PATCH | `/api/admin/penyetoran-sampah/{id}/approve` | auth:sanctum | Admin | adminApi.js |
| PATCH | `/api/admin/penyetoran-sampah/{id}/reject` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/penyetoran-sampah/{id}` | auth:sanctum | Superadmin | adminApi.js |
| GET | `/api/admin/penyetoran-sampah/stats/overview` | auth:sanctum | Admin | adminApi.js |

#### Approval Payload
```json
{
  "catatan_admin": "Approved - Good condition",
  "poin_diperoleh": 100,
  "berat_terverifikasi": 5.5
}
```

#### Rejection Payload
```json
{
  "catatan_admin": "Rejected - Invalid photo",
  "alasan_penolakan": "Foto tidak jelas"
}
```

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Validation:**
  - Points calculation based on weight
  - Photo verification
  - Prevent duplicate approval
- **Audit:** All approvals/rejections logged

#### Frontend Components
- Admin dashboard waste management section

---

### **FITUR 13: ADMIN - PRODUCT REDEMPTION MANAGEMENT**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/penukar-produk` | auth:sanctum | Admin | adminApi.js |
| PATCH | `/api/admin/penukar-produk/{id}/approve` | auth:sanctum | Admin | adminApi.js |
| PATCH | `/api/admin/penukar-produk/{id}/reject` | auth:sanctum | Admin | adminApi.js |
| PATCH | `/api/admin/penukar-produk/{id}/picked-up` | auth:sanctum | Admin | adminApi.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Validation:**
  - Stock verification before approval
  - Point refund on rejection
- **Workflow:** pending → approved → picked_up

#### Frontend Components
- `src/Components/Pages/adminDashboard/components/ProductRedemptionManagement.jsx`

---

### **FITUR 14: ADMIN - PRODUCT MANAGEMENT**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/produk` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/produk/{id}` | auth:sanctum | Admin | adminApi.js |
| POST | `/api/admin/produk` | auth:sanctum | Admin | adminApi.js |
| PUT | `/api/admin/produk/{id}` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/produk/{id}` | auth:sanctum | Admin | adminApi.js |

#### Product Payload
```json
{
  "nama_produk": "T-Shirt Eco",
  "deskripsi": "T-shirt from recycled materials",
  "harga_poin": 5000,
  "stok": 50,
  "kategori": "fashion",
  "gambar": "(File upload)",
  "status": "tersedia"
}
```

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Validation:**
  - Price > 0
  - Stock >= 0
  - Valid category
- **File Upload:** Secure image upload

---

### **FITUR 15: ADMIN - WASTE TYPES & SCHEDULES**

#### Waste Types (Jenis Sampah)
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/jenis-sampah` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/jenis-sampah/{id}` | auth:sanctum | Admin | adminApi.js |
| POST | `/api/admin/jenis-sampah` | auth:sanctum | Admin | adminApi.js |
| PUT | `/api/admin/jenis-sampah/{id}` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/jenis-sampah/{id}` | auth:sanctum | Admin | adminApi.js |

#### Waste Categories
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/waste-categories` | auth:sanctum | Admin | adminApi.js |

#### Collection Schedules (Jadwal Penyetoran)
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/jadwal-penyetoran` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/jadwal-penyetoran/{id}` | auth:sanctum | Admin | adminApi.js |
| POST | `/api/admin/jadwal-penyetoran` | auth:sanctum | Admin | adminApi.js |
| PUT | `/api/admin/jadwal-penyetoran/{id}` | auth:sanctum | Admin | adminApi.js |
| DELETE | `/api/admin/jadwal-penyetoran/{id}` | auth:sanctum | Admin | adminApi.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Validation:**
  - Valid waste type
  - Valid location (latitude/longitude)
  - Valid schedule time
  - Prevent schedule conflicts

---

### **FITUR 16: SUPERADMIN - ADMIN MANAGEMENT**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/superadmin/admins` | auth:sanctum | Superadmin | adminApi.js |
| GET | `/api/superadmin/admins/{id}` | auth:sanctum | Superadmin | adminApi.js |
| POST | `/api/superadmin/admins` | auth:sanctum | Superadmin | adminApi.js |
| PUT | `/api/superadmin/admins/{id}` | auth:sanctum | Superadmin | adminApi.js |
| DELETE | `/api/superadmin/admins/{id}` | auth:sanctum | Superadmin | adminApi.js |
| GET | `/api/superadmin/admins/{id}/activity-logs` | auth:sanctum | Superadmin | adminApi.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** Superadmin ONLY
- **Validation:**
  - Unique email
  - Strong password (min 8 chars)
  - Valid role assignment
- **Audit:** All admin actions logged

---

### **FITUR 17: SUPERADMIN - ROLES & PERMISSIONS**

#### Roles
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/superadmin/roles` | auth:sanctum | Superadmin | adminApi.js |
| GET | `/api/superadmin/roles/{id}` | auth:sanctum | Superadmin | adminApi.js |
| POST | `/api/superadmin/roles` | auth:sanctum | Superadmin | adminApi.js |
| PUT | `/api/superadmin/roles/{id}` | auth:sanctum | Superadmin | adminApi.js |
| DELETE | `/api/superadmin/roles/{id}` | auth:sanctum | Superadmin | adminApi.js |

#### Permissions
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/superadmin/permissions` | auth:sanctum | Superadmin | adminApi.js |
| GET | `/api/superadmin/roles/{id}/permissions` | auth:sanctum | Superadmin | adminApi.js |
| POST | `/api/superadmin/roles/{id}/permissions` | auth:sanctum | Superadmin | adminApi.js |

#### Permission Examples
```json
[
  "users.view",
  "users.create",
  "users.edit",
  "users.delete",
  "waste.approve",
  "waste.reject",
  "products.manage",
  "reports.view"
]
```

#### Security Layer
- **Authentication:** Required
- **Authorization:** Superadmin ONLY
- **Validation:**
  - Prevent role deletion if users assigned
  - Validate permission exists
- **Audit:** All role/permission changes logged

---

### **FITUR 18: ANALYTICS & REPORTS**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/analytics/waste` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/analytics/users` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/analytics/points` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/analytics/waste-by-type` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/analytics/waste-by-user` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/transactions` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/transactions/export` | auth:sanctum | Admin | adminApi.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Data Privacy:** Aggregated data only, no personal details exposed
- **Export:** Secure CSV/Excel download

---

### **FITUR 19: ACTIVITY LOGS**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/activity-logs` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/activity-logs/stats/overview` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/activity-logs/export/csv` | auth:sanctum | Admin | adminApi.js |
| GET | `/api/admin/users/{id}/activity-logs` | auth:sanctum | Admin | adminApi.js |

#### Log Entry Example
```json
{
  "log_id": 1234,
  "user_id": 5,
  "activity_type": "waste_deposit_approved",
  "description": "Admin approved waste deposit #567",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-12-26T10:30:00Z"
}
```

#### Security Layer
- **Authentication:** Required
- **Authorization:** Admin/Superadmin only
- **Immutable:** Logs cannot be edited/deleted
- **Audit:** Complete audit trail

#### Frontend Components
- `src/Components/ActivityLogsTable.jsx`
- `src/services/activityLogService.js`

---

### **FITUR 20: BACKUP & RESTORE**

#### Endpoints
| Method | Endpoint | Middleware | Role | Service File |
|--------|----------|------------|------|--------------|
| GET | `/api/admin/backups` | auth:sanctum | Superadmin | backupService.js |
| POST | `/api/admin/backups/create` | auth:sanctum | Superadmin | backupService.js |
| POST | `/api/admin/backups/{id}/restore` | auth:sanctum | Superadmin | backupService.js |
| DELETE | `/api/admin/backups/{id}` | auth:sanctum | Superadmin | backupService.js |
| GET | `/api/admin/backups/{id}/download` | auth:sanctum | Superadmin | backupService.js |

#### Security Layer
- **Authentication:** Required
- **Authorization:** Superadmin ONLY
- **Validation:**
  - Backup integrity check
  - Database connection required
- **Encryption:** Backup files encrypted

#### Frontend Components
- `src/Components/BackupManager.jsx`
- `src/services/backupService.js`

---

## 🔒 MIDDLEWARE & GUARDS

### Laravel Backend Middleware

#### 1. **auth:sanctum**
```php
// Semua endpoint admin/superadmin menggunakan middleware ini
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->group(function () {
        // Admin routes
    });
});
```

**Fungsi:**
- Validasi Bearer token
- Check token expiry
- Load user data from token
- Return 401 if invalid/expired

#### 2. **role:admin**
```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin-only routes
});
```

**Fungsi:**
- Check user role is 'admin' or 'superadmin'
- Return 403 if insufficient permission

#### 3. **role:superadmin**
```php
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    // Superadmin-only routes
});
```

**Fungsi:**
- Check user role is 'superadmin'
- Return 403 if not superadmin

---

### Frontend Guards

#### 1. **PrivateRoute Component**
```jsx
// Redirect to login if not authenticated
<PrivateRoute>
  <DashboardPage />
</PrivateRoute>
```

#### 2. **AdminRoute Component**
```jsx
// Redirect if not admin/superadmin
<AdminRoute>
  <AdminDashboard />
</AdminRoute>
```

#### 3. **useAuth Hook**
```javascript
const { user, role, isAuthenticated } = useAuth()

if (!isAuthenticated) {
  navigate('/login')
}
```

---

## 🧪 ERROR HANDLING & FALLBACKS

### HTTP Status Codes

| Code | Meaning | Frontend Action |
|------|---------|-----------------|
| 200 | Success | Display data |
| 201 | Created | Show success message |
| 400 | Bad Request | Show validation errors |
| 401 | Unauthorized | Redirect to login / Use fallback data |
| 403 | Forbidden | Show "No permission" message |
| 404 | Not Found | Show "Not found" message |
| 422 | Validation Error | Display field errors |
| 500 | Server Error | Show generic error, retry option |

### Smart 401 Handling

```javascript
// File: src/services/adminApi.js
const handle401 = (endpoint = 'unknown') => {
  console.warn(`401 on ${endpoint}`)
  
  // Don't immediately logout - could be temporary backend issue
  // Return error for component to handle gracefully
  return {
    success: false,
    message: 'Authentication required',
    data: null,
    statusCode: 401
  }
}
```

**Strategy:**
- Don't force logout on every 401
- Allow graceful degradation with mock data
- Let components handle authentication errors
- Only logout if token is truly invalid

---

## 📝 API RESPONSE FORMATS

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data here
  },
  "meta": {
    "current_page": 1,
    "total_pages": 10,
    "per_page": 20,
    "total": 200
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email sudah terdaftar"],
    "password": ["Password minimal 8 karakter"]
  }
}
```

---

## 🔐 DATA VALIDATION RULES

### Frontend Validation

#### Login
- Email: Required, valid email format
- Password: Required, min 8 characters

#### Register
- Nama: Required, max 100 characters
- Email: Required, unique, valid format
- Password: Required, min 8 chars, with confirmation
- Alamat: Required
- No Telepon: Required, numeric

#### Waste Deposit
- Jadwal: Required, valid jadwal_id
- Berat: Required, numeric, > 0
- Foto: Required, image format, max 2MB
- Jenis Sampah: Required, valid jenis_sampah_id

#### Product Redemption
- Product ID: Required, valid product exists
- Quantity: Required, > 0, <= stock
- Points: Sufficient balance check

#### Cash Withdrawal
- Amount: Required, > minimum amount
- Bank Account: Required, valid format
- Account Name: Required

---

## 📊 PAGINATION & FILTERING

### Standard Pagination Parameters
```javascript
{
  page: 1,           // Current page
  limit: 20,         // Items per page (10, 20, 50, 100)
  per_page: 20       // Alternative to limit
}
```

### Common Filters
```javascript
{
  status: 'pending',      // pending, approved, rejected
  date_from: '2025-01-01',
  date_to: '2025-12-31',
  user_id: 123,
  search: 'keyword',
  sort_by: 'created_at',
  order: 'desc'           // asc, desc
}
```

---

## 🎯 TESTING CHECKLIST UNTUK BLACKBOX

### Authentication Testing
- [ ] Login dengan credentials valid
- [ ] Login dengan credentials invalid
- [ ] Register user baru
- [ ] Logout dan clear session
- [ ] Access protected route without token
- [ ] Token expiry handling
- [ ] Password reset flow
- [ ] OTP verification

### Authorization Testing
- [ ] Nasabah tidak bisa akses admin routes
- [ ] Admin tidak bisa akses superadmin routes
- [ ] Check permission-based access
- [ ] Test role hierarchy

### CRUD Operations Testing
**Untuk setiap fitur test:**
- [ ] Create new record
- [ ] Read/View record list
- [ ] Read/View single record
- [ ] Update existing record
- [ ] Delete record
- [ ] Validation errors handling
- [ ] File upload (jika ada)

### Workflow Testing
- [ ] Waste deposit submission → approval → points added
- [ ] Product redemption → approval → stock decreased
- [ ] Cash withdrawal request → approval → points deducted
- [ ] Badge unlock upon criteria met

### Edge Cases
- [ ] Empty state displays
- [ ] Large dataset pagination
- [ ] Concurrent requests
- [ ] Network error handling
- [ ] File upload max size
- [ ] SQL injection attempts
- [ ] XSS attempts in form inputs

### Performance Testing
- [ ] Page load time < 3s
- [ ] API response time < 2s
- [ ] Large file upload handling
- [ ] Multiple simultaneous users

---

## 📦 SERVICE FILES SUMMARY

| Service File | Purpose | Endpoints Count |
|--------------|---------|-----------------|
| **adminApi.js** | Admin dashboard operations | 80+ endpoints |
| **authService.js** | Authentication & user session | 6 endpoints |
| **badgeService.js** | Badge CRUD operations | 5 endpoints |
| **notificationService.js** | Notification management | 8 endpoints |
| **activityLogService.js** | Activity logging | 4 endpoints |
| **productApi.js** | Product display & redemption | 5 endpoints |
| **backupService.js** | Database backup/restore | 5 endpoints |
| **api.js** | General API utilities | 15+ helper functions |

**Total:** 120+ unique API endpoints

---

## 🚀 DEPLOYMENT CONSIDERATIONS

### Environment Variables
```env
# Development
VITE_API_URL=http://127.0.0.1:8000/api

# Staging
VITE_API_URL=https://staging-api.mendaur.com/api

# Production
VITE_API_URL=https://api.mendaur.com/api
```

### Security Headers (Backend harus implement)
```
Access-Control-Allow-Origin: https://mendaur.com
Access-Control-Allow-Credentials: true
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Strict-Transport-Security: max-age=31536000
```

### CORS Configuration
```php
// Backend CORS settings
'allowed_origins' => [env('FRONTEND_URL')],
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
'allowed_headers' => ['Content-Type', 'Authorization', 'Accept'],
'supports_credentials' => true,
```

---

## 📞 KONTAK & SUPPORT

**Frontend Developer:** [Your Name]  
**Backend Developer:** [Backend Team]  
**Project Manager:** [PM Name]  

**Dokumentasi Tambahan:**
- `API_TESTING_GUIDE.md` - Panduan testing API
- `DATABASE_SCHEMA_AND_API_DOCUMENTATION.md` - Schema database lengkap
- `ADMIN_DASHBOARD_API_DATABASE_COMPLETE_ANALYSIS.md` - Analisis dashboard admin

---

## ✅ CHANGELOG

| Tanggal | Versi | Perubahan |
|---------|-------|-----------|
| 2025-12-26 | 1.0.0 | Dokumen awal - Analisis lengkap 20 fitur dengan 120+ endpoints |

---

**END OF DOCUMENT**

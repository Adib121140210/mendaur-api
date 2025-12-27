# 🧪 SKENARIO BLACKBOX TESTING KATALON STUDIO - MENDAUR API

**Tanggal:** 26 Desember 2025  
**Dokumen:** Test Scenarios untuk Katalon Studio Automation  
**API Base URL:** `http://127.0.0.1:8000/api`  
**Total Test Cases:** 120+ Test Cases

---

## 📋 STRUKTUR TEST SUITE

```
Test Suites/
├── 01_Authentication/
│   ├── TS01_Login_Tests
│   ├── TS02_Register_Tests
│   ├── TS03_Logout_Tests
│   ├── TS04_ForgotPassword_Tests
│   └── TS05_Profile_Tests
├── 02_Dashboard/
│   ├── TS06_NasabahDashboard_Tests
│   └── TS07_AdminDashboard_Tests
├── 03_WasteManagement/
│   ├── TS08_WasteDeposit_Nasabah_Tests
│   └── TS09_WasteApproval_Admin_Tests
├── 04_PointsManagement/
│   ├── TS10_ProductRedemption_Tests
│   └── TS11_CashWithdrawal_Tests
├── 05_BadgesAndLeaderboard/
│   ├── TS12_Badges_Tests
│   └── TS13_Leaderboard_Tests
├── 06_AdminManagement/
│   ├── TS14_UserManagement_Tests
│   ├── TS15_ProductManagement_Tests
│   ├── TS16_ArticleManagement_Tests
│   └── TS17_NotificationManagement_Tests
└── 07_SecurityTests/
    ├── TS18_Authorization_Tests
    ├── TS19_XSS_Prevention_Tests
    └── TS20_SQLInjection_Tests
```

---

## 🔐 PREREQUISITE SETUP

### Global Variables (Project > Settings > Global Variables)
```groovy
// Base Configuration
API_BASE_URL = 'http://127.0.0.1:8000/api'
WEB_BASE_URL = 'http://localhost:5173'

// Test Credentials
SUPERADMIN_EMAIL = 'superadmin@test.com'
SUPERADMIN_PASSWORD = 'password123'
ADMIN_EMAIL = 'admin@test.com'
ADMIN_PASSWORD = 'password123'
NASABAH_EMAIL = 'adib@example.com'
NASABAH_PASSWORD = 'password123'

// Test Data
TEST_IMAGE_PATH = 'Data Files/test-image.jpg'
INVALID_TOKEN = 'invalid_token_12345'
```

### Test Data Files
```
Data Files/
├── valid_users.csv
├── invalid_users.csv
├── waste_deposits.csv
├── products.csv
└── test-images/
    ├── valid-waste-photo.jpg
    ├── oversized-image.jpg
    └── invalid-format.txt
```

---

## 📝 TEST CASE TEMPLATE

Setiap test case mengikuti format:
```groovy
Test Case ID: TC_XXX_YYY
Test Case Name: [Descriptive Name]
Priority: [High/Medium/Low]
Type: [Positive/Negative/Security]
Prerequisites: [Setup requirements]
Test Steps: [Detailed steps]
Expected Result: [Expected outcome]
Actual Result: [Filled by Katalon]
Status: [Pass/Fail]
```

---

## 🧪 TEST SUITE 1: AUTHENTICATION

### TS01: Login Tests (15 Test Cases)

#### **TC_AUTH_001: Login with Valid Credentials (Superadmin)**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "${SUPERADMIN_EMAIL}",
  "password": "${SUPERADMIN_PASSWORD}"
}
```

**Test Steps:**
1. Send POST request to `/api/login`
2. Verify response status code = 200
3. Verify response contains `token` field
4. Verify response contains `user` object
5. Verify `user.level` = "Superadmin"
6. Store token in global variable `SUPERADMIN_TOKEN`

**Expected Result:**
```json
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "user": {
      "user_id": 2,
      "nama": "Superadmin Testing",
      "email": "superadmin@test.com",
      "level": "Superadmin",
      "actual_poin": 10000
    },
    "token": "52|uPlPymxuVMznw1rUiNFDuhJFJatKt9pkH6Upu6IP687a1046"
  }
}
```

**Assertions:**
- Status Code: 200
- response.status == "success"
- response.data.token != null
- response.data.user.level == "Superadmin"

---

#### **TC_AUTH_002: Login with Valid Credentials (Admin)**
**Priority:** High | **Type:** Positive

**Request:**
```json
{
  "email": "${ADMIN_EMAIL}",
  "password": "${ADMIN_PASSWORD}"
}
```

**Assertions:**
- Status Code: 200
- response.data.user.level == "Admin"
- Token stored successfully

---

#### **TC_AUTH_003: Login with Valid Credentials (Nasabah)**
**Priority:** High | **Type:** Positive

**Request:**
```json
{
  "email": "${NASABAH_EMAIL}",
  "password": "${NASABAH_PASSWORD}"
}
```

**Assertions:**
- Status Code: 200
- response.data.user.level in ["Bronze", "Silver", "Gold", "Platinum"]

---

#### **TC_AUTH_004: Login with Invalid Email**
**Priority:** High | **Type:** Negative

**Request:**
```json
{
  "email": "nonexistent@test.com",
  "password": "password123"
}
```

**Assertions:**
- Status Code: 401
- response.status == "error"
- response.message contains "Email atau password salah"

---

#### **TC_AUTH_005: Login with Invalid Password**
**Priority:** High | **Type:** Negative

**Request:**
```json
{
  "email": "${SUPERADMIN_EMAIL}",
  "password": "wrongpassword"
}
```

**Assertions:**
- Status Code: 401
- response.message contains "Email atau password salah"

---

#### **TC_AUTH_006: Login with Empty Email**
**Priority:** Medium | **Type:** Negative

**Request:**
```json
{
  "email": "",
  "password": "password123"
}
```

**Assertions:**
- Status Code: 422
- response.errors.email exists

---

#### **TC_AUTH_007: Login with Invalid Email Format**
**Priority:** Medium | **Type:** Negative

**Request:**
```json
{
  "email": "invalidemail",
  "password": "password123"
}
```

**Assertions:**
- Status Code: 422
- response.errors.email contains "valid email"

---

#### **TC_AUTH_008: Login with Empty Password**
**Priority:** Medium | **Type:** Negative

**Request:**
```json
{
  "email": "${SUPERADMIN_EMAIL}",
  "password": ""
}
```

**Assertions:**
- Status Code: 422
- response.errors.password exists

---

#### **TC_AUTH_009: Login with SQL Injection Attempt**
**Priority:** High | **Type:** Security

**Request:**
```json
{
  "email": "admin@test.com' OR '1'='1",
  "password": "' OR '1'='1"
}
```

**Assertions:**
- Status Code: 401
- No SQL error exposed
- Login fails gracefully

---

#### **TC_AUTH_010: Login with XSS Attempt**
**Priority:** High | **Type:** Security

**Request:**
```json
{
  "email": "<script>alert('XSS')</script>@test.com",
  "password": "password"
}
```

**Assertions:**
- Status Code: 422 or 401
- Script not executed
- Response sanitized

---

### TS02: Register Tests (10 Test Cases)

#### **TC_AUTH_011: Register New User with Valid Data**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/register`

**Request:**
```json
{
  "nama": "Test User {{timestamp}}",
  "email": "testuser{{timestamp}}@test.com",
  "password": "password123",
  "password_confirmation": "password123",
  "no_hp": "081234567890",
  "alamat": "Jl. Test No. 123"
}
```

**Assertions:**
- Status Code: 201
- response.status == "success"
- response.data.user.nama == "Test User {{timestamp}}"

---

#### **TC_AUTH_012: Register with Existing Email**
**Priority:** High | **Type:** Negative

**Request:**
```json
{
  "nama": "Duplicate User",
  "email": "${SUPERADMIN_EMAIL}",
  "password": "password123",
  "password_confirmation": "password123",
  "no_hp": "081234567890",
  "alamat": "Jl. Test No. 123"
}
```

**Assertions:**
- Status Code: 422
- response.errors.email contains "sudah terdaftar"

---

#### **TC_AUTH_013: Register with Weak Password**
**Priority:** Medium | **Type:** Negative

**Request:**
```json
{
  "nama": "Test User",
  "email": "newuser@test.com",
  "password": "123",
  "password_confirmation": "123",
  "no_hp": "081234567890",
  "alamat": "Jl. Test No. 123"
}
```

**Assertions:**
- Status Code: 422
- response.errors.password contains "minimal 8 karakter"

---

#### **TC_AUTH_014: Register with Password Mismatch**
**Priority:** Medium | **Type:** Negative

**Request:**
```json
{
  "password": "password123",
  "password_confirmation": "password456"
}
```

**Assertions:**
- Status Code: 422
- response.errors.password contains "konfirmasi password"

---

### TS03: Logout Tests (3 Test Cases)

#### **TC_AUTH_015: Logout with Valid Token**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer ${SUPERADMIN_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.status == "success"
- Token invalidated (subsequent requests with same token fail)

---

#### **TC_AUTH_016: Logout without Token**
**Priority:** High | **Type:** Negative

**Headers:** None

**Assertions:**
- Status Code: 401
- response.message contains "Unauthenticated"

---

#### **TC_AUTH_017: Logout with Invalid Token**
**Priority:** Medium | **Type:** Negative

**Headers:**
```
Authorization: Bearer ${INVALID_TOKEN}
```

**Assertions:**
- Status Code: 401

---

### TS04: Forgot Password Tests (8 Test Cases)

#### **TC_AUTH_018: Send OTP to Valid Email**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/forgot-password`

**Request:**
```json
{
  "email": "${NASABAH_EMAIL}"
}
```

**Assertions:**
- Status Code: 200
- response.message contains "OTP"
- OTP sent to email/database

---

#### **TC_AUTH_019: Verify Valid OTP**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/verify-otp`

**Request:**
```json
{
  "email": "${NASABAH_EMAIL}",
  "otp": "123456"
}
```

**Assertions:**
- Status Code: 200
- response.data.reset_token exists

---

#### **TC_AUTH_020: Reset Password with Valid Token**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/reset-password`

**Request:**
```json
{
  "email": "${NASABAH_EMAIL}",
  "reset_token": "${RESET_TOKEN}",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Assertions:**
- Status Code: 200
- Can login with new password

---

### TS05: Profile Tests (5 Test Cases)

#### **TC_AUTH_021: Get Profile with Valid Token**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/profile`

**Headers:**
```
Authorization: Bearer ${SUPERADMIN_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data.user_id == 2
- response.data.email == "${SUPERADMIN_EMAIL}"

---

#### **TC_AUTH_022: Update Profile with Valid Data**
**Priority:** High | **Type:** Positive

**API Endpoint:** `PUT /api/profile`

**Request:**
```json
{
  "nama": "Updated Name",
  "no_hp": "081234567899",
  "alamat": "New Address"
}
```

**Assertions:**
- Status Code: 200
- response.data.nama == "Updated Name"

---

## 🧪 TEST SUITE 2: DASHBOARD

### TS06: Nasabah Dashboard Tests (5 Test Cases)

#### **TC_DASH_001: Get Dashboard Overview**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/dashboard/overview`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data.totalUsers exists
- response.data.totalWasteCollected exists

---

#### **TC_DASH_002: Get Leaderboard**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/dashboard/leaderboard`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data is array
- Top user has highest actual_poin

---

### TS07: Admin Dashboard Tests (8 Test Cases)

#### **TC_DASH_003: Admin Get Overview with Valid Token**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/admin/dashboard/overview`

**Headers:**
```
Authorization: Bearer ${ADMIN_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data.totalUsers > 0
- response.data.totalPointsDistributed >= 0

---

#### **TC_DASH_004: Nasabah Cannot Access Admin Dashboard**
**Priority:** High | **Type:** Security

**API Endpoint:** `GET /api/admin/dashboard/overview`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 403
- response.message contains "tidak memiliki akses"

---

## 🧪 TEST SUITE 3: WASTE MANAGEMENT

### TS08: Waste Deposit - Nasabah (10 Test Cases)

#### **TC_WASTE_001: Submit Waste Deposit with Valid Data**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/tabung-sampah`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
Content-Type: multipart/form-data
```

**Request:**
```
jadwal_penyetoran_id: 1
jenis_sampah_id: 1
berat_sampah: 5.5
foto_bukti: @test-image.jpg
catatan: "Test waste deposit"
```

**Assertions:**
- Status Code: 201
- response.data.status == "pending"
- response.data.berat_sampah == 5.5

---

#### **TC_WASTE_002: Submit Waste Deposit without Photo**
**Priority:** High | **Type:** Negative

**Request:**
```
jadwal_penyetoran_id: 1
jenis_sampah_id: 1
berat_sampah: 5.5
catatan: "No photo"
```

**Assertions:**
- Status Code: 422
- response.errors.foto_bukti exists

---

#### **TC_WASTE_003: Submit with Negative Weight**
**Priority:** Medium | **Type:** Negative

**Request:**
```
berat_sampah: -5
```

**Assertions:**
- Status Code: 422
- response.errors.berat_sampah contains "harus lebih dari 0"

---

#### **TC_WASTE_004: Submit with Oversized Image**
**Priority:** Medium | **Type:** Negative

**Request:**
```
foto_bukti: @oversized-image.jpg (> 2MB)
```

**Assertions:**
- Status Code: 422
- response.errors.foto_bukti contains "maksimal 2MB"

---

#### **TC_WASTE_005: Get User's Waste Deposits**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/setor-sampah/user/{userId}`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data is array
- All items belong to userId

---

### TS09: Waste Approval - Admin (12 Test Cases)

#### **TC_WASTE_006: Get All Waste Deposits (Admin)**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/admin/penyetoran-sampah?page=1&limit=10`

**Headers:**
```
Authorization: Bearer ${ADMIN_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data.users is array
- response.data.pagination exists

---

#### **TC_WASTE_007: Approve Waste Deposit**
**Priority:** High | **Type:** Positive

**API Endpoint:** `PATCH /api/admin/penyetoran-sampah/{id}/approve`

**Request:**
```json
{
  "catatan_admin": "Approved - Good condition",
  "poin_diperoleh": 550,
  "berat_terverifikasi": 5.5
}
```

**Assertions:**
- Status Code: 200
- response.data.status == "approved"
- User's actual_poin increased by 550

---

#### **TC_WASTE_008: Reject Waste Deposit**
**Priority:** High | **Type:** Positive

**API Endpoint:** `PATCH /api/admin/penyetoran-sampah/{id}/reject`

**Request:**
```json
{
  "catatan_admin": "Rejected - Invalid photo",
  "alasan_penolakan": "Foto tidak jelas"
}
```

**Assertions:**
- Status Code: 200
- response.data.status == "rejected"
- User's points not changed

---

#### **TC_WASTE_009: Approve Already Approved Deposit**
**Priority:** Medium | **Type:** Negative

**Assertions:**
- Status Code: 400
- response.message contains "sudah disetujui"

---

#### **TC_WASTE_010: Nasabah Cannot Approve Waste**
**Priority:** High | **Type:** Security

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 403

---

## 🧪 TEST SUITE 4: POINTS MANAGEMENT

### TS10: Product Redemption (10 Test Cases)

#### **TC_PRODUCT_001: Get All Products (Public)**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/produk`

**Assertions:**
- Status Code: 200
- response.data is array
- Products have required fields

---

#### **TC_PRODUCT_002: Redeem Product with Sufficient Points**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/penukaran-produk`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Request:**
```json
{
  "product_id": 1,
  "quantity": 1,
  "alamat_pengiriman": "Jl. Test No. 123",
  "catatan": "Test redemption"
}
```

**Assertions:**
- Status Code: 201
- response.data.status == "pending"
- User's actual_poin decreased

---

#### **TC_PRODUCT_003: Redeem with Insufficient Points**
**Priority:** High | **Type:** Negative

**Request:**
```json
{
  "product_id": 99,
  "quantity": 1000
}
```

**Assertions:**
- Status Code: 400
- response.message contains "poin tidak cukup"

---

#### **TC_PRODUCT_004: Redeem Out of Stock Product**
**Priority:** Medium | **Type:** Negative

**Assertions:**
- Status Code: 400
- response.message contains "stok tidak tersedia"

---

### TS11: Cash Withdrawal (8 Test Cases)

#### **TC_CASH_001: Request Cash Withdrawal**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/penarikan-tunai`

**Request:**
```json
{
  "jumlah_poin": 1000,
  "nama_bank": "BCA",
  "nomor_rekening": "1234567890",
  "atas_nama_rekening": "Test User"
}
```

**Assertions:**
- Status Code: 201
- response.data.status == "pending"
- Points deducted

---

#### **TC_CASH_002: Request with Insufficient Points**
**Priority:** High | **Type:** Negative

**Request:**
```json
{
  "jumlah_poin": 999999
}
```

**Assertions:**
- Status Code: 400
- response.message contains "poin tidak cukup"

---

## 🧪 TEST SUITE 5: BADGES & LEADERBOARD

### TS12: Badges (8 Test Cases)

#### **TC_BADGE_001: Get User Badge Progress**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/user/badges/progress`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data is array
- Contains progress percentage

---

#### **TC_BADGE_002: Get Completed Badges**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/user/badges/completed`

**Assertions:**
- Status Code: 200
- All badges have unlocked_at date

---

### TS13: Leaderboard (5 Test Cases)

#### **TC_LEADER_001: Get Leaderboard**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/dashboard/leaderboard`

**Assertions:**
- Status Code: 200
- Sorted by actual_poin DESC
- Top 10 users displayed

---

## 🧪 TEST SUITE 6: ADMIN MANAGEMENT

### TS14: User Management (15 Test Cases)

#### **TC_USER_001: Admin Get All Users**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/admin/users?page=1&limit=10`

**Headers:**
```
Authorization: Bearer ${ADMIN_TOKEN}
```

**Assertions:**
- Status Code: 200
- response.data.users is array
- Pagination metadata exists

---

#### **TC_USER_002: Admin Create New User**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/admin/users`

**Request:**
```json
{
  "nama": "New User",
  "email": "newuser{{timestamp}}@test.com",
  "password": "password123",
  "no_hp": "081234567890",
  "level": "Bronze"
}
```

**Assertions:**
- Status Code: 201
- User created successfully

---

#### **TC_USER_003: Admin Update User**
**Priority:** High | **Type:** Positive

**API Endpoint:** `PUT /api/admin/users/{userId}`

**Request:**
```json
{
  "nama": "Updated Name"
}
```

**Assertions:**
- Status Code: 200
- Name updated

---

#### **TC_USER_004: Admin Delete User**
**Priority:** High | **Type:** Positive

**API Endpoint:** `DELETE /api/admin/users/{userId}`

**Assertions:**
- Status Code: 200
- User soft deleted

---

#### **TC_USER_005: Admin Search Users**
**Priority:** Medium | **Type:** Positive

**API Endpoint:** `GET /api/admin/users?search=Adib`

**Assertions:**
- Status Code: 200
- Results contain "Adib"

---

### TS15: Product Management - Admin (10 Test Cases)

#### **TC_PROD_ADMIN_001: Admin Create Product**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/admin/produk`

**Request:**
```
nama_produk: "Test Product"
harga_poin: 5000
stok: 100
kategori: "fashion"
gambar: @product-image.jpg
```

**Assertions:**
- Status Code: 201
- Product created

---

#### **TC_PROD_ADMIN_002: Admin Update Product**
**Priority:** High | **Type:** Positive

**API Endpoint:** `PUT /api/admin/produk/{id}`

**Assertions:**
- Status Code: 200
- Product updated

---

#### **TC_PROD_ADMIN_003: Admin Delete Product**
**Priority:** High | **Type:** Positive

**API Endpoint:** `DELETE /api/admin/produk/{id}`

**Assertions:**
- Status Code: 200
- Product deleted

---

### TS16: Article Management - Admin (10 Test Cases)

#### **TC_ARTICLE_001: Admin Create Article**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/admin/artikel`

**Request:**
```json
{
  "judul": "Test Article",
  "konten": "Article content...",
  "gambar": @thumbnail.jpg,
  "status": "published"
}
```

**Assertions:**
- Status Code: 201
- Article created

---

#### **TC_ARTICLE_002: Public Get Articles**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/artikel`

**Assertions:**
- Status Code: 200
- Only published articles shown

---

### TS17: Notification Management (8 Test Cases)

#### **TC_NOTIF_001: Get User Notifications**
**Priority:** High | **Type:** Positive

**API Endpoint:** `GET /api/notifications`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 200
- Notifications array returned

---

#### **TC_NOTIF_002: Mark Notification as Read**
**Priority:** High | **Type:** Positive

**API Endpoint:** `PATCH /api/notifications/{id}/read`

**Assertions:**
- Status Code: 200
- is_read = true

---

#### **TC_NOTIF_003: Admin Send Notification**
**Priority:** High | **Type:** Positive

**API Endpoint:** `POST /api/admin/notifications`

**Request:**
```json
{
  "title": "Test Notification",
  "message": "Test message",
  "type": "info",
  "user_id": null
}
```

**Assertions:**
- Status Code: 201
- Notification sent

---

## 🧪 TEST SUITE 7: SECURITY TESTS

### TS18: Authorization Tests (10 Test Cases)

#### **TC_SEC_001: Access Admin Route without Token**
**Priority:** High | **Type:** Security

**API Endpoint:** `GET /api/admin/users`

**Headers:** None

**Assertions:**
- Status Code: 401

---

#### **TC_SEC_002: Access Admin Route with Nasabah Token**
**Priority:** High | **Type:** Security

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN}
```

**Assertions:**
- Status Code: 403

---

#### **TC_SEC_003: Access Nasabah Data with Different User Token**
**Priority:** High | **Type:** Security

**API Endpoint:** `GET /api/setor-sampah/user/999`

**Headers:**
```
Authorization: Bearer ${NASABAH_TOKEN} (user_id=3)
```

**Assertions:**
- Status Code: 403 or Empty data

---

### TS19: XSS Prevention (5 Test Cases)

#### **TC_SEC_004: XSS in Registration**
**Priority:** High | **Type:** Security

**Request:**
```json
{
  "nama": "<script>alert('XSS')</script>",
  "email": "test@test.com",
  "password": "password123"
}
```

**Assertions:**
- Script not executed
- Data sanitized

---

### TS20: SQL Injection Tests (5 Test Cases)

#### **TC_SEC_005: SQL Injection in Login**
**Priority:** High | **Type:** Security

**Request:**
```json
{
  "email": "admin@test.com' OR '1'='1",
  "password": "' OR '1'='1"
}
```

**Assertions:**
- Status Code: 401
- No SQL error exposed

---

## 📊 KATALON EXECUTION

### Test Suite Execution Order
```
1. Setup_Global_Variables
2. TS01_Login_Tests (Get tokens)
3. TS02-TS07 (Feature tests)
4. TS18-TS20 (Security tests)
5. Teardown_Cleanup
```

### Katalon Profile Configuration

**Profile: dev**
```groovy
API_BASE_URL = 'http://127.0.0.1:8000/api'
```

**Profile: staging**
```groovy
API_BASE_URL = 'https://staging-api.mendaur.com/api'
```

**Profile: production**
```groovy
API_BASE_URL = 'https://api.mendaur.com/api'
```

---

## 📈 REPORTING

### Expected Metrics
- **Total Test Cases:** 120+
- **Expected Pass Rate:** > 95%
- **Execution Time:** < 30 minutes
- **Critical Failures:** 0

### Report Includes:
- Test execution summary
- Pass/Fail breakdown by suite
- Response time metrics
- Failed test details with screenshots
- Coverage report

---

## 🔧 MAINTENANCE

### Weekly Tasks:
- Update test data CSV files
- Refresh test user credentials
- Clear test database records
- Update test images

### Monthly Tasks:
- Review and update assertions
- Add new test cases for new features
- Performance benchmark updates

---

**Status:** ✅ Ready for Katalon Studio Implementation

**END OF TESTING SCENARIOS**

# 💰 Frontend Integration Guide - Cash Withdrawal API

## 📋 Overview
Complete API documentation for integrating the Cash Withdrawal (Penukaran Poin) system into the frontend.

**Base URL:** `http://127.0.0.1:8000/api`

**Authentication:** All endpoints (except login/register) require Bearer token authentication.

---

## 🔐 Authentication Flow

### 1. Login
**Endpoint:** `POST /api/login`

**Request Headers:**
```javascript
{
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
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
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "nama": "Adib Surya",
      "email": "adib@example.com",
      "no_hp": "081234567890",
      "alamat": "Jakarta",
      "foto_profil": null,
      "total_poin": 5000,
      "total_setor_sampah": 10,
      "level": "Bronze"
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ123456789"
  }
}
```

**Error Response (401):**
```json
{
  "status": "error",
  "message": "Email atau password salah"
}
```

**Frontend Implementation:**
```javascript
// Login function
async function login(email, password) {
  const response = await fetch('http://127.0.0.1:8000/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.status === 'success') {
    // Store token in localStorage
    localStorage.setItem('auth_token', data.data.token);
    localStorage.setItem('user', JSON.stringify(data.data.user));
    return data.data;
  } else {
    throw new Error(data.message);
  }
}
```

---

## 💸 Cash Withdrawal Endpoints

### Authentication Header for All Requests
```javascript
{
  'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

---

## 📤 1. Submit Cash Withdrawal Request

**Endpoint:** `POST /api/penarikan-tunai`

**Purpose:** User submits a request to convert points to cash. Points are **deducted immediately**.

**Request Body:**
```json
{
  "jumlah_poin": 2000,
  "nomor_rekening": "1234567890",
  "nama_bank": "BCA",
  "nama_penerima": "Adib Surya"
}
```

**Validation Rules:**
- `jumlah_poin`: Required, minimum 2000, must be multiples of 100
- `nomor_rekening`: Required, string, max 50 characters
- `nama_bank`: Required, string, max 100 characters
- `nama_penerima`: Required, string, max 255 characters

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Permintaan penarikan tunai berhasil diajukan",
  "data": {
    "id": 1,
    "user_id": 1,
    "jumlah_poin": 2000,
    "jumlah_rupiah": "20000.00",
    "nomor_rekening": "1234567890",
    "nama_bank": "BCA",
    "nama_penerima": "Adib Surya",
    "status": "pending",
    "catatan_admin": null,
    "processed_by": null,
    "processed_at": null,
    "created_at": "2025-11-17T10:30:00.000000Z",
    "updated_at": "2025-11-17T10:30:00.000000Z"
  }
}
```

**Error Responses:**

**422 - Validation Error:**
```json
{
  "message": "The jumlah poin field must be at least 2000.",
  "errors": {
    "jumlah_poin": [
      "The jumlah poin field must be at least 2000."
    ]
  }
}
```

**400 - Insufficient Points:**
```json
{
  "status": "error",
  "message": "Poin tidak mencukupi. Poin Anda saat ini: 1500"
}
```

**Frontend Implementation:**
```javascript
async function submitWithdrawal(jumlahPoin, nomorRekening, namaBank, namaPenerima) {
  const token = localStorage.getItem('auth_token');
  
  const response = await fetch('http://127.0.0.1:8000/api/penarikan-tunai', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      jumlah_poin: jumlahPoin,
      nomor_rekening: nomorRekening,
      nama_bank: namaBank,
      nama_penerima: namaPenerima
    })
  });
  
  const data = await response.json();
  
  if (response.ok) {
    alert('Permintaan penarikan berhasil diajukan!');
    return data.data;
  } else {
    throw new Error(data.message);
  }
}
```

---

## 📋 2. Get Withdrawal History

**Endpoint:** `GET /api/penarikan-tunai`

**Purpose:** Get paginated list of user's withdrawal history.

**Query Parameters (Optional):**
- `status`: Filter by status (`pending`, `approved`, `rejected`)
- `per_page`: Items per page (default: 15)
- `page`: Page number (default: 1)

**Example URLs:**
```
GET /api/penarikan-tunai
GET /api/penarikan-tunai?status=pending
GET /api/penarikan-tunai?status=approved&per_page=10
GET /api/penarikan-tunai?page=2
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 3,
        "user_id": 1,
        "jumlah_poin": 2000,
        "jumlah_rupiah": "20000.00",
        "nomor_rekening": "1234567890",
        "nama_bank": "BCA",
        "nama_penerima": "Adib Surya",
        "status": "approved",
        "catatan_admin": null,
        "processed_by": 5,
        "processed_at": "2025-11-17T11:00:00.000000Z",
        "created_at": "2025-11-17T10:30:00.000000Z",
        "updated_at": "2025-11-17T11:00:00.000000Z"
      },
      {
        "id": 2,
        "jumlah_poin": 3000,
        "jumlah_rupiah": "30000.00",
        "status": "pending",
        "created_at": "2025-11-16T14:20:00.000000Z"
      }
    ],
    "first_page_url": "http://127.0.0.1:8000/api/penarikan-tunai?page=1",
    "from": 1,
    "last_page": 2,
    "last_page_url": "http://127.0.0.1:8000/api/penarikan-tunai?page=2",
    "next_page_url": "http://127.0.0.1:8000/api/penarikan-tunai?page=2",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 25
  }
}
```

**Frontend Implementation:**
```javascript
async function getWithdrawalHistory(status = null, page = 1) {
  const token = localStorage.getItem('auth_token');
  let url = `http://127.0.0.1:8000/api/penarikan-tunai?page=${page}`;
  
  if (status) {
    url += `&status=${status}`;
  }
  
  const response = await fetch(url, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data.data; // Returns pagination object
}
```

---

## 📊 3. Get Withdrawal Summary

**Endpoint:** `GET /api/penarikan-tunai/summary`

**Purpose:** Get statistics of user's withdrawals.

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "total_penarikan": 10000,
    "total_rupiah": "100000.00",
    "jumlah_pending": 2,
    "jumlah_approved": 3,
    "jumlah_rejected": 1
  }
}
```

**Frontend Implementation:**
```javascript
async function getWithdrawalSummary() {
  const token = localStorage.getItem('auth_token');
  
  const response = await fetch('http://127.0.0.1:8000/api/penarikan-tunai/summary', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data.data;
}
```

---

## 🔍 4. Get Specific Withdrawal Details

**Endpoint:** `GET /api/penarikan-tunai/{id}`

**Purpose:** Get details of a specific withdrawal request.

**Example:** `GET /api/penarikan-tunai/5`

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 5,
    "user_id": 1,
    "jumlah_poin": 2000,
    "jumlah_rupiah": "20000.00",
    "nomor_rekening": "1234567890",
    "nama_bank": "BCA",
    "nama_penerima": "Adib Surya",
    "status": "pending",
    "catatan_admin": null,
    "processed_by": null,
    "processed_at": null,
    "created_at": "2025-11-17T10:30:00.000000Z",
    "updated_at": "2025-11-17T10:30:00.000000Z"
  }
}
```

**Error Response (403):**
```json
{
  "status": "error",
  "message": "Anda tidak memiliki akses ke data ini"
}
```

**Error Response (404):**
```json
{
  "status": "error",
  "message": "Data penarikan tunai tidak ditemukan"
}
```

---

## 🛡️ Admin Endpoints (Only for users with level='admin')

### 5. Admin - View All Withdrawals

**Endpoint:** `GET /api/admin/penarikan-tunai`

**Query Parameters (Optional):**
- `status`: Filter by status (`pending`, `approved`, `rejected`)
- `user_id`: Filter by user ID
- `date_from`: Filter from date (YYYY-MM-DD)
- `date_to`: Filter to date (YYYY-MM-DD)
- `per_page`: Items per page (default: 15)

**Example URLs:**
```
GET /api/admin/penarikan-tunai
GET /api/admin/penarikan-tunai?status=pending
GET /api/admin/penarikan-tunai?user_id=5
GET /api/admin/penarikan-tunai?date_from=2025-11-01&date_to=2025-11-30
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 5,
        "user_id": 3,
        "jumlah_poin": 2000,
        "jumlah_rupiah": "20000.00",
        "nomor_rekening": "9876543210",
        "nama_bank": "Mandiri",
        "nama_penerima": "John Doe",
        "status": "pending",
        "catatan_admin": null,
        "processed_by": null,
        "processed_at": null,
        "created_at": "2025-11-17T10:30:00.000000Z",
        "updated_at": "2025-11-17T10:30:00.000000Z",
        "user": {
          "id": 3,
          "nama": "John Doe",
          "email": "john@example.com",
          "total_poin": 3000
        }
      }
    ],
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 6. Admin - Approve Withdrawal

**Endpoint:** `POST /api/admin/penarikan-tunai/approve/{id}`

**Purpose:** Approve a pending withdrawal. User gets the money transferred (outside system).

**Example:** `POST /api/admin/penarikan-tunai/approve/5`

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Penarikan tunai berhasil disetujui",
  "data": {
    "id": 5,
    "status": "approved",
    "processed_by": 1,
    "processed_at": "2025-11-17T11:00:00.000000Z"
  }
}
```

**Error Response (400):**
```json
{
  "status": "error",
  "message": "Penarikan tunai sudah diproses sebelumnya"
}
```

---

### 7. Admin - Reject Withdrawal

**Endpoint:** `POST /api/admin/penarikan-tunai/reject/{id}`

**Purpose:** Reject a pending withdrawal. **Points are automatically refunded to user.**

**Request Body:**
```json
{
  "catatan_admin": "Nomor rekening tidak valid"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Penarikan tunai ditolak dan poin dikembalikan",
  "data": {
    "id": 5,
    "status": "rejected",
    "catatan_admin": "Nomor rekening tidak valid",
    "processed_by": 1,
    "processed_at": "2025-11-17T11:05:00.000000Z"
  }
}
```

**Error Response (400):**
```json
{
  "status": "error",
  "message": "Penarikan tunai sudah diproses sebelumnya"
}
```

---

### 8. Admin - Get Statistics

**Endpoint:** `GET /api/admin/penarikan-tunai/statistics`

**Purpose:** Get dashboard statistics for admin panel.

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "total_pending": 5,
    "total_approved_today": 12,
    "total_approved_this_month": 150,
    "total_poin_pending": 25000,
    "total_rupiah_pending": "250000.00",
    "total_poin_approved_today": 50000,
    "total_rupiah_approved_today": "500000.00",
    "total_poin_approved_month": 750000,
    "total_rupiah_approved_month": "7500000.00"
  }
}
```

---

## 🚨 Error Handling

### 401 - Unauthenticated
```json
{
  "success": false,
  "message": "Unauthenticated. Please login first."
}
```

**Action:** Redirect to login page, clear localStorage.

```javascript
if (response.status === 401) {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  window.location.href = '/login';
}
```

---

### 403 - Forbidden (Not Admin)
```json
{
  "status": "error",
  "message": "Anda tidak memiliki akses ke data ini"
}
```

---

### 422 - Validation Error
```json
{
  "message": "The jumlah poin field must be at least 2000.",
  "errors": {
    "jumlah_poin": [
      "The jumlah poin field must be at least 2000."
    ],
    "nomor_rekening": [
      "The nomor rekening field is required."
    ]
  }
}
```

**Frontend Handling:**
```javascript
if (response.status === 422) {
  const data = await response.json();
  // Display errors to user
  Object.keys(data.errors).forEach(field => {
    data.errors[field].forEach(error => {
      console.error(`${field}: ${error}`);
    });
  });
}
```

---

## 💡 Business Logic Summary

### Point Conversion
- **100 Points = Rp 1,000**
- **Minimum Withdrawal:** 2,000 points (Rp 20,000)
- **Increment:** Must be multiples of 100

### Point Deduction Flow
1. **On Submit:** Points are **immediately deducted** from user's `total_poin`
2. **On Approve:** No change (money transferred externally)
3. **On Reject:** Points are **automatically refunded** to user's `total_poin`

### Status Workflow
```
pending → approved (final)
        → rejected (final, points refunded)
```

---

## 🎨 Frontend UI Recommendations

### 1. TukarPoin Page (User)
**Components needed:**
- Point balance display (from `user.total_poin`)
- Point to Rupiah calculator (100 points = Rp 1,000)
- Withdrawal form (jumlah_poin, nomor_rekening, nama_bank, nama_penerima)
- Validation messages
- Withdrawal history table with status badges
- Summary cards (total withdrawn, pending count, approved count)

**Status Badge Colors:**
- `pending`: Yellow/Orange
- `approved`: Green
- `rejected`: Red

---

### 2. Admin Panel (Admin Only)
**Components needed:**
- Statistics dashboard (pending count, today's approvals, monthly total)
- Withdrawal request table with filters
- Approve/Reject buttons with confirmation modals
- Rejection reason textarea
- User information display

---

## 📝 Complete Frontend Example (React/Vue/Vanilla JS)

```javascript
// API Service Module
const API_BASE_URL = 'http://127.0.0.1:8000/api';

class CashWithdrawalAPI {
  // Get auth token from localStorage
  getToken() {
    return localStorage.getItem('auth_token');
  }
  
  // Get headers with auth
  getHeaders() {
    return {
      'Authorization': `Bearer ${this.getToken()}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };
  }
  
  // Handle API errors
  async handleResponse(response) {
    const data = await response.json();
    
    if (!response.ok) {
      if (response.status === 401) {
        // Redirect to login
        localStorage.clear();
        window.location.href = '/login';
      }
      throw new Error(data.message || 'An error occurred');
    }
    
    return data;
  }
  
  // Submit withdrawal
  async submitWithdrawal(jumlahPoin, nomorRekening, namaBank, namaPenerima) {
    const response = await fetch(`${API_BASE_URL}/penarikan-tunai`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify({
        jumlah_poin: jumlahPoin,
        nomor_rekening: nomorRekening,
        nama_bank: namaBank,
        nama_penerima: namaPenerima
      })
    });
    
    return this.handleResponse(response);
  }
  
  // Get history
  async getHistory(status = null, page = 1) {
    let url = `${API_BASE_URL}/penarikan-tunai?page=${page}`;
    if (status) url += `&status=${status}`;
    
    const response = await fetch(url, {
      method: 'GET',
      headers: this.getHeaders()
    });
    
    return this.handleResponse(response);
  }
  
  // Get summary
  async getSummary() {
    const response = await fetch(`${API_BASE_URL}/penarikan-tunai/summary`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    
    return this.handleResponse(response);
  }
  
  // Admin: Get all withdrawals
  async adminGetAll(filters = {}) {
    let url = `${API_BASE_URL}/admin/penarikan-tunai?`;
    const params = new URLSearchParams(filters);
    url += params.toString();
    
    const response = await fetch(url, {
      method: 'GET',
      headers: this.getHeaders()
    });
    
    return this.handleResponse(response);
  }
  
  // Admin: Approve
  async adminApprove(id) {
    const response = await fetch(`${API_BASE_URL}/admin/penarikan-tunai/approve/${id}`, {
      method: 'POST',
      headers: this.getHeaders()
    });
    
    return this.handleResponse(response);
  }
  
  // Admin: Reject
  async adminReject(id, catatanAdmin) {
    const response = await fetch(`${API_BASE_URL}/admin/penarikan-tunai/reject/${id}`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify({ catatan_admin: catatanAdmin })
    });
    
    return this.handleResponse(response);
  }
  
  // Admin: Get statistics
  async adminGetStatistics() {
    const response = await fetch(`${API_BASE_URL}/admin/penarikan-tunai/statistics`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    
    return this.handleResponse(response);
  }
}

// Usage Example
const api = new CashWithdrawalAPI();

// Submit withdrawal
async function handleSubmit() {
  try {
    const result = await api.submitWithdrawal(2000, '1234567890', 'BCA', 'Adib Surya');
    console.log('Success:', result);
    alert('Permintaan penarikan berhasil diajukan!');
  } catch (error) {
    console.error('Error:', error);
    alert(error.message);
  }
}

// Get history with status filter
async function loadHistory(status = null) {
  try {
    const result = await api.getHistory(status);
    console.log('History:', result.data);
    // Render table with result.data.data (array of withdrawals)
  } catch (error) {
    console.error('Error:', error);
  }
}

// Get summary
async function loadSummary() {
  try {
    const result = await api.getSummary();
    console.log('Summary:', result.data);
    // Display: total_penarikan, jumlah_pending, etc.
  } catch (error) {
    console.error('Error:', error);
  }
}
```

---

## ✅ Testing Checklist for Frontend Team

- [ ] Login and store token in localStorage
- [ ] Display user's current point balance
- [ ] Submit withdrawal with valid data (2000+ points, multiples of 100)
- [ ] Verify points are deducted immediately after submission
- [ ] View withdrawal history with pagination
- [ ] Filter history by status (pending/approved/rejected)
- [ ] View withdrawal summary statistics
- [ ] Handle validation errors (< 2000 points, not multiples of 100)
- [ ] Handle insufficient points error
- [ ] Test 401 redirect to login when token expires
- [ ] (Admin) View all withdrawals with filters
- [ ] (Admin) Approve withdrawal
- [ ] (Admin) Reject withdrawal with reason
- [ ] (Admin) Verify points are refunded on rejection
- [ ] (Admin) View dashboard statistics

---

## 🔗 Related Documentation
- `CASH_WITHDRAWAL_IMPLEMENTATION_COMPLETE.md` - Full backend implementation details
- `BACKEND_CASH_WITHDRAWAL_SPEC.md` - Original specification
- `ROUTE_LOGIN_ERROR_FIXED.md` - Authentication setup
- `POSTMAN_405_ERROR_FIX.md` - API testing guide

---

## 📞 Support
If you encounter any issues during integration, check:
1. Token is properly stored and sent in Authorization header
2. All request bodies are valid JSON
3. Content-Type and Accept headers are set correctly
4. User has sufficient points before withdrawal
5. Admin endpoints are only accessible to users with `level='admin'`

---

**Last Updated:** November 17, 2025
**API Version:** 1.0
**Status:** ✅ Ready for Frontend Integration

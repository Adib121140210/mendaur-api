# 🔐 Frontend Register Integration Guide

**Date**: November 20, 2025  
**Status**: ✅ Backend Ready for Integration  
**API Base URL**: `http://127.0.0.1:8000`

---

## 📋 Overview

The frontend register page is **fully compatible** with the backend API endpoint. This guide details the complete integration including:
- ✅ API endpoint specification
- ✅ Request/Response format
- ✅ Error handling
- ✅ Validation rules
- ✅ Testing checklist

---

## 🎯 API Endpoint

```
POST /api/register
```

**Base URL**: `http://127.0.0.1:8000/api/register`

**Authentication**: ❌ NOT Required (Public endpoint)

**Content-Type**: `application/json`

---

## 📤 Request Format

### Frontend sends this payload:

```javascript
{
  "nama": "Adib Surya",
  "email": "adib@example.com",
  "no_hp": "08123456789",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}
```

### Field Requirements:

| Field | Type | Rules | Example |
|-------|------|-------|---------|
| `nama` | String | Required, 3-255 chars | "Adib Surya" |
| `email` | String | Required, valid email, unique | "adib@example.com" |
| `no_hp` | String | Required | "08123456789" or "+628123456789" |
| `password` | String | Required, min 8 chars | "Password123!" |
| `password_confirmation` | String | Required, must match `password` | "Password123!" |

**Note**: Backend validates that:
- `email` must be unique in the system
- `password` and `password_confirmation` must be identical (Laravel's `confirmed` rule)
- `no_hp` can be any format (no strict validation on backend, frontend validates)

---

## ✅ Success Response (201 Created)

```json
{
  "status": "success",
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 5,
      "nama": "Adib Surya",
      "email": "adib@example.com",
      "level": "Pemula"
    }
  }
}
```

**HTTP Status**: `201`

**User Initialization**:
- `total_poin`: 0
- `total_setor_sampah`: 0
- `level`: "Pemula" (Beginner)
- `alamat`: null (optional field)

---

## ❌ Error Responses

### 1. Validation Errors (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "nama": ["Nama lengkap wajib diisi"],
    "email": ["Email sudah terdaftar"],
    "password": ["Password minimal 8 karakter"],
    "password_confirmation": ["Password tidak cocok"]
  }
}
```

**HTTP Status**: `422`

**Frontend handling** (already implemented ✅):
```javascript
if (result.errors) {
  const backendErrors = Object.entries(result.errors).reduce((acc, [key, messages]) => {
    acc[key] = Array.isArray(messages) ? messages[0] : messages;
    return acc;
  }, {});
  setErrors(backendErrors);
  throw new Error("Validasi gagal. Silakan periksa data Anda.");
}
```

### 2. Email Already Exists

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email sudah terdaftar"]
  }
}
```

### 3. Invalid Password Confirmation

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "password_confirmation": ["Password tidak cocok"]
  }
}
```

### 4. Server Error (500)

```json
{
  "message": "Internal Server Error",
  "status": "error"
}
```

---

## 🔄 Complete Frontend Workflow

### Step 1: User fills form and clicks "Daftar Akun"

Frontend validates locally:
- ✅ Nama: min 3 chars
- ✅ Email: valid format
- ✅ No HP: valid Indonesian format (08xx or +62xx)
- ✅ Password: min 8 chars, strength indicator
- ✅ Password Confirm: matches password

### Step 2: Submit to API

```javascript
const response = await fetch("http://127.0.0.1:8000/api/register", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "Accept": "application/json",
  },
  body: JSON.stringify({
    nama: "Adib Surya",
    email: "adib@example.com",
    no_hp: "08123456789",
    password: "Password123!",
    password_confirmation: "Password123!"
  }),
});
```

### Step 3: Handle Response

**On Success (201)**:
1. Show success message: "✅ Pendaftaran berhasil! Silakan login dengan akun Anda."
2. Clear form
3. Redirect to login after 2 seconds ✅ (already implemented)

**On Validation Error (422)**:
1. Extract error messages from `result.errors`
2. Display field-specific errors
3. User corrects and resubmits

**On Server Error (500)**:
1. Show generic error: "Terjadi kesalahan. Silakan coba lagi."
2. Log to console for debugging

---

## 🧪 Testing Checklist

### Test Case 1: Happy Path (Success)
```
Input: Valid data
Expected: 201 success, redirect to login, clear form
Status: ✅ Ready to test
```

### Test Case 2: Email Already Exists
```
Input: Existing email
Expected: 422 with "Email sudah terdaftar" error
Status: ✅ Ready to test
```

### Test Case 3: Password Mismatch
```
Input: password ≠ password_confirmation
Expected: 422 with "Password tidak cocok"
Status: ✅ Ready to test
```

### Test Case 4: Short Password
```
Input: password length < 8
Expected: 422 with "Password minimal 8 karakter"
Status: ✅ Ready to test
```

### Test Case 5: Invalid Email
```
Input: invalid@format
Expected: 422 with "Format email tidak valid"
Status: ✅ Ready to test
```

### Test Case 6: Missing Fields
```
Input: Any required field empty
Expected: 422 with specific field error
Status: ✅ Ready to test
```

---

## 🔑 Backend Validation Rules

**Enforced by Laravel Backend**:

```php
$request->validate([
    'nama' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|string|min:8|confirmed',  // confirms password_confirmation
    'no_hp' => 'required|string',
    'alamat' => 'nullable|string',  // optional
]);
```

**What gets created in database**:

```php
$user = User::create([
    'nama' => $request->nama,
    'email' => $request->email,
    'password' => Hash::make($request->password),  // automatically hashed
    'no_hp' => $request->no_hp,
    'alamat' => $request->alamat,
    'total_poin' => 0,           // initialized to 0
    'total_setor_sampah' => 0,   // initialized to 0
    'level' => 'Pemula',         // Beginner level
]);
```

---

## 🎨 Frontend Code Status

**Current Implementation** ✅:
- Form validation: ✅ Complete
- Phone formatting: ✅ Complete (handles 08xx and +62xx)
- Password strength indicator: ✅ Complete
- Error handling: ✅ Complete
- Success handling: ✅ Complete
- Loading state: ✅ Complete
- Redirect on success: ✅ Complete (2 second delay)

**Example implemented error handling**:
```javascript
if (!response.ok) {
  // Handle validation errors from backend
  if (result.errors) {
    const backendErrors = Object.entries(result.errors).reduce((acc, [key, messages]) => {
      acc[key] = Array.isArray(messages) ? messages[0] : messages;
      return acc;
    }, {});
    setErrors(backendErrors);
    throw new Error("Validasi gagal. Silakan periksa data Anda.");
  }
  throw new Error(result.message || "Pendaftaran gagal");
}
```

---

## 🚀 Quick Start Testing

### 1. Ensure backend is running:
```bash
php artisan serve
```

### 2. Test with Postman:
```
POST http://127.0.0.1:8000/api/register
Content-Type: application/json

{
  "nama": "Test User",
  "email": "test@example.com",
  "no_hp": "08123456789",
  "password": "TestPassword123!",
  "password_confirmation": "TestPassword123!"
}
```

### 3. Expected response (201):
```json
{
  "status": "success",
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 5,
      "nama": "Test User",
      "email": "test@example.com",
      "level": "Pemula"
    }
  }
}
```

### 4. Verify in database:
```bash
php artisan tinker
>>> User::where('email', 'test@example.com')->first();
```

---

## 🔍 Debugging Tips

### Issue: "Email sudah terdaftar"
- Check database: `SELECT * FROM users WHERE email = 'xxx@xxx.com';`
- Clear old test data: `php artisan migrate:fresh --seed`

### Issue: "Password tidak cocok"
- Ensure frontend sends exact same string in both `password` and `password_confirmation`
- Check no extra spaces or hidden characters

### Issue: CORS error
- Backend is configured with proper CORS headers
- Ensure frontend calls correct URL: `http://127.0.0.1:8000/api/register`

### Issue: 500 Server Error
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Ensure database connection is working: `php artisan db:show`
- Run migrations: `php artisan migrate`

---

## 📱 After Registration - Next Steps

### User can now:
1. ✅ Login with registered email + password
2. ✅ View profile
3. ✅ Start depositing waste (tabung sampah)
4. ✅ Earn points
5. ✅ View products and redeem points
6. ✅ Track transactions

### Initial user data:
```json
{
  "id": 5,
  "nama": "Adib Surya",
  "email": "adib@example.com",
  "no_hp": "08123456789",
  "total_poin": 0,
  "total_setor_sampah": 0,
  "level": "Pemula",
  "foto_profil": null,
  "alamat": null,
  "email_verified_at": null
}
```

---

## 📞 Support

**Common Issues**:

| Issue | Solution |
|-------|----------|
| 422 Validation Error | Check backend validation rules above |
| CORS Error | Ensure base URL is correct |
| 500 Server Error | Check `storage/logs/laravel.log` |
| Email Already Exists | Use different email for testing |
| Database not found | Run `php artisan migrate` |

---

## ✨ Summary

✅ **Backend ready**: All validation and storage implemented  
✅ **Frontend ready**: All form validation and error handling implemented  
✅ **API endpoint**: `/api/register` fully functional  
✅ **Testing**: Ready for integration testing  

**Next Action**: Run tests with Postman/frontend and verify flow end-to-end.

---

**Last Updated**: November 20, 2025, 2024  
**API Version**: Laravel 11 with Sanctum Authentication

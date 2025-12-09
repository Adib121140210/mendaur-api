# 🚀 QUICK REFERENCE - Frontend Register Integration

## 📍 API Endpoint
```
POST http://127.0.0.1:8000/api/register
Content-Type: application/json
Accept: application/json
```

## 📤 Send This
```javascript
{
  "nama": "User Full Name",
  "email": "user@email.com",
  "no_hp": "08123456789",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

## 📥 Receive This (Success - 201)
```javascript
{
  "status": "success",
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 6,
      "nama": "User Full Name",
      "email": "user@email.com",
      "level": "Pemula"
    }
  }
}
```

## ❌ Handle Errors (422)
```javascript
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password field confirmation does not match."]
  }
}
```

## ✅ Frontend is Already Doing:
- ✅ Client-side validation
- ✅ Error message display
- ✅ Success redirect (2s delay)
- ✅ Form clear on success
- ✅ Loading state handling
- ✅ Phone number formatting

## 🎯 Backend Validation Rules
| Field | Rule |
|-------|------|
| nama | Required, 3-255 chars |
| email | Required, valid email, unique |
| no_hp | Required, string |
| password | Required, min 8 chars |
| password_confirmation | Required, must match password |

## 🧪 Quick Test (Postman)
```bash
POST http://127.0.0.1:8000/api/register
Headers: Content-Type: application/json
Body:
{
  "nama": "Test User",
  "email": "test@example.com",
  "no_hp": "08999888777",
  "password": "TestPass123!",
  "password_confirmation": "TestPass123!"
}
```

Expected: **201** with user data

## 🔧 Troubleshoot
- CORS Error? → Check API URL is exactly `http://127.0.0.1:8000`
- 500 Error? → Check `php artisan serve` is running
- Email taken? → Use different email for testing
- Password error? → Ensure passwords match exactly

## 📚 Full Docs
- `FRONTEND_REGISTER_INTEGRATION_GUIDE.md` - Complete API docs
- `POSTMAN_REGISTER_TESTS.md` - Test cases with examples
- `FRONTEND_REGISTER_READY.md` - Integration status report

## ✨ Status: **READY TO USE** ✨
No backend changes needed. Frontend can integrate immediately.

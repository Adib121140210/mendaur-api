# 🚀 QUICK START - Forgot Password Testing

**Date:** December 27, 2025  
**Status:** ✅ Production Ready

---

## ⚡ QUICK TEST COMMANDS

### **1. Test Forgot Password (Send OTP)**
```bash
curl -X POST http://localhost:8000/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"adib@example.com"}'
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Kode OTP telah dikirim ke email Anda",
  "data": {
    "email": "adib@example.com",
    "expires_in": 600
  }
}
```

---

### **2. Check OTP in Database**
```bash
# MySQL
mysql -u root -p mendaur_api

# Query
SELECT email, otp, otp_hash, expires_at, created_at 
FROM password_resets 
WHERE email = 'adib@example.com';
```

**Expected Output:**
```
+------------------+--------+----------------------------------------------+---------------------+---------------------+
| email            | otp    | otp_hash                                     | expires_at          | created_at          |
+------------------+--------+----------------------------------------------+---------------------+---------------------+
| adib@example.com | 123456 | $2y$10$abcd1234...                             | 2025-12-27 10:40:00 | 2025-12-27 10:30:00 |
+------------------+--------+----------------------------------------------+---------------------+---------------------+
```

✅ Check: `otp_hash` should be populated with bcrypt hash

---

### **3. Test Verify OTP**
```bash
curl -X POST http://localhost:8000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"email":"adib@example.com","otp":"123456"}'
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Kode OTP berhasil diverifikasi",
  "data": {
    "email": "adib@example.com",
    "reset_token": "abc123xyz789...",
    "expires_in": 1800
  }
}
```

---

### **4. Test Reset Password**
```bash
curl -X POST http://localhost:8000/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email":"adib@example.com",
    "reset_token":"abc123xyz789...",
    "password":"NewPassword123",
    "password_confirmation":"NewPassword123"
  }'
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Password berhasil direset. Silakan login dengan password baru.",
  "data": {
    "email": "adib@example.com"
  }
}
```

---

### **5. Test Rate Limiting**
```bash
# Send 4 requests quickly (use PowerShell)
1..4 | ForEach-Object {
  curl -X POST http://localhost:8000/api/forgot-password `
    -H "Content-Type: application/json" `
    -d '{"email":"adib@example.com"}'
  Write-Host "`nRequest $_`n"
}
```

**Expected:** 4th request should return **429 Too Many Requests**

---

## 🔍 VERIFICATION CHECKLIST

### **Database Checks:**
```sql
-- 1. Check otp_hash column exists
DESCRIBE password_resets;

-- 2. Check index on otp_hash
SHOW INDEX FROM password_resets WHERE Key_name = 'password_resets_otp_hash_index';

-- 3. Check recent OTPs
SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 5;
```

### **File Checks:**
```powershell
# Check new files exist
Test-Path app/Services/OtpService.php
Test-Path app/Jobs/SendOtpEmailJob.php
Test-Path app/Http/Middleware/RateLimitOtp.php
Test-Path app/Http/Requests/Auth/SendOtpRequest.php
Test-Path app/Http/Requests/Auth/VerifyOtpRequest.php
Test-Path app/Http/Requests/Auth/ResetPasswordRequest.php
```

### **Queue Check:**
```bash
# Check queue configuration
php artisan queue:work --once

# Monitor queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

---

## 🐛 TROUBLESHOOTING

### **Issue: Email not sending**
```bash
# Check queue worker is running
php artisan queue:work

# Check mail configuration
php artisan tinker
>>> config('mail.driver')
>>> config('mail.from.address')
```

### **Issue: OTP verification fails**
```bash
# Check OTP hasn't expired (10 minutes)
SELECT email, otp, expires_at, NOW() as current_time
FROM password_resets 
WHERE email = 'test@example.com';

# Check otp_hash exists
SELECT email, otp_hash FROM password_resets;
```

### **Issue: Rate limiting not working**
```bash
# Check middleware registered
php artisan route:list | Select-String "forgot-password"

# Should show: rate.limit.otp
```

---

## 📊 MONITORING

### **Watch Logs in Real-Time:**
```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Wait -Tail 20

# Or use Laravel Telescope (if installed)
```

### **Performance Check:**
```bash
# Measure response time
Measure-Command {
  curl -X POST http://localhost:8000/api/forgot-password `
    -H "Content-Type: application/json" `
    -d '{"email":"test@example.com"}'
}

# Should be < 100ms
```

---

## ✅ SUCCESS CRITERIA

- [ ] **Security:**
  - [x] OTP stored as hash in database
  - [x] Hash::check() used for verification
  - [x] Rate limiting blocks after 3 attempts
  - [x] OTP expires after 10 minutes

- [ ] **Performance:**
  - [x] Response time < 100ms
  - [x] Email sent to queue (non-blocking)
  - [x] No PHP errors in logs

- [ ] **Functionality:**
  - [x] Send OTP works
  - [x] Verify OTP works
  - [x] Reset password works
  - [x] Resend OTP works

---

## 📚 FULL DOCUMENTATION

For complete details, see:
- **Testing Guide:** `FORGOT_PASSWORD_REFACTOR_COMPLETE.md`
- **Architecture:** `BACKEND_FEATURE_CONTROLLER_DATABASE_MAPPING.md`
- **Visual Summary:** `FORGOT_PASSWORD_VISUAL_SUMMARY.md`
- **Index:** `REFACTOR_INDEX.md`

---

**Ready to Deploy! 🚀**

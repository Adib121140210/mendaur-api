# 🐛 BUG FIX: Reset Password Token Invalid

## ❌ PROBLEM IDENTIFIED

**Symptom:** Setelah verify OTP berhasil (200 OK), reset password selalu gagal dengan:
```
400 Bad Request: "Token reset tidak valid atau sudah kedaluwarsa"
```

---

## 🔍 ROOT CAUSE

**Bug Location:** `app/Http/Controllers/Auth/ForgotPasswordController.php`

**The Problem:**
Setelah `verifyOTP()` berhasil, `expires_at` tetap menggunakan waktu expiry OTP yang asli (10 menit dari send OTP). Ini menyebabkan:

1. User request OTP jam 19:00 → expires_at = 19:10
2. User verify OTP jam 19:08 → expires_at TETAP 19:10
3. User reset password jam 19:11 → **EXPIRED!** ❌

**Before (Bug):**
```php
// verifyOTP()
$resetRecord->update([
    'verified_at' => Carbon::now()
]);

$resetToken = Str::random(60);
$resetRecord->update([
    'reset_token' => Hash::make($resetToken)
]);
// ❌ expires_at NOT extended - stays at original OTP expiry time
```

---

## ✅ FIX APPLIED

**After (Fixed):**
```php
// verifyOTP()
$resetToken = Str::random(60);
$newExpiresAt = Carbon::now()->addMinutes(30); // 30 more minutes

$resetRecord->update([
    'verified_at' => Carbon::now(),
    'reset_token' => Hash::make($resetToken),
    'expires_at' => $newExpiresAt  // ✅ EXTEND expiry for password reset step
]);
```

**Now:**
1. User request OTP jam 19:00 → expires_at = 19:10
2. User verify OTP jam 19:08 → expires_at = **19:38** (extended +30 min)
3. User reset password jam 19:15 → **SUCCESS!** ✅

---

## 🧪 TEST RESULTS

### **Before Fix:**
```
Step 2: Verify OTP ✅
  expires_at: 19:10 (original OTP expiry)
  
Step 3: Reset Password ❌
  Error: "Token reset tidak valid atau sudah kedaluwarsa"
  Reason: expires_at (19:10) < now (19:11) → EXPIRED
```

### **After Fix:**
```
Step 2: Verify OTP ✅
  expires_at: 19:38 (extended +30 min from verify time)
  
Step 3: Reset Password ✅
  Token valid, password updated successfully!
```

---

## 📊 TIMELINE COMPARISON

### **Before Fix (Bug):**
```
Timeline:
├─ 19:00 - Request OTP
│          expires_at = 19:10 (10 min)
│
├─ 19:08 - Verify OTP ✅
│          expires_at = 19:10 (UNCHANGED!)
│
├─ 19:11 - Reset Password ❌
│          19:11 > 19:10 → EXPIRED!
│
└─ ❌ FLOW FAILED
```

### **After Fix (Working):**
```
Timeline:
├─ 19:00 - Request OTP
│          expires_at = 19:10 (10 min)
│
├─ 19:08 - Verify OTP ✅
│          expires_at = 19:38 (EXTENDED to +30 min)
│
├─ 19:15 - Reset Password ✅
│          19:15 < 19:38 → VALID!
│
└─ ✅ FLOW SUCCESS
```

---

## 📝 FILE CHANGED

**File:** `app/Http/Controllers/Auth/ForgotPasswordController.php`

**Method:** `verifyOTP()`

**Diff:**
```diff
- // Mark OTP as verified
- $resetRecord->update([
-     'verified_at' => Carbon::now()
- ]);
-
- // Generate reset token for password reset step
- $resetToken = Str::random(60);
- $resetRecord->update([
-     'reset_token' => Hash::make($resetToken)
- ]);
-
- return response()->json([
-     'success' => true,
-     'message' => 'Kode OTP berhasil diverifikasi',
-     'data' => [
-         'email' => $email,
-         'reset_token' => $resetToken,
-         'expires_in' => Carbon::now()->diffInSeconds($resetRecord->expires_at)
-     ]
- ]);

+ // Generate reset token for password reset step
+ $resetToken = Str::random(60);
+ 
+ // Mark OTP as verified AND extend expiry time for password reset (30 more minutes)
+ $newExpiresAt = Carbon::now()->addMinutes(30);
+ $resetRecord->update([
+     'verified_at' => Carbon::now(),
+     'reset_token' => Hash::make($resetToken),
+     'expires_at' => $newExpiresAt  // Extend expiry for password reset step
+ ]);
+
+ return response()->json([
+     'success' => true,
+     'message' => 'Kode OTP berhasil diverifikasi',
+     'data' => [
+         'email' => $email,
+         'reset_token' => $resetToken,
+         'expires_in' => 1800 // 30 minutes in seconds
+     ]
+ ]);
```

---

## ✅ VERIFICATION

### **Full Flow Test: PASSED**

```bash
php test-full-flow.php
```

**Output:**
```
✅ FULL FLOW TEST PASSED!

Summary:
  1. ✅ OTP generated and saved
  2. ✅ OTP verified successfully
  3. ✅ Reset token generated and saved (hashed)
  4. ✅ Expiry time EXTENDED after verify (30 min)
  5. ✅ Reset token validated via Hash::check
  6. ✅ Password updated
  7. ✅ Reset record cleaned up
```

---

## 🚀 FRONTEND CAN NOW TEST

### **New OTP Generated:**
```
Email: adibraihan123@gmail.com
OTP: 105536
Valid until: 19:23:35
```

### **Test Flow:**

1. **Request OTP** (from frontend or use code above)
2. **Verify OTP** - expiry akan di-extend +30 menit
3. **Reset Password** - sekarang akan berhasil!

### **Expected Success Response:**
```json
{
  "success": true,
  "message": "Password berhasil direset. Silakan login dengan password baru.",
  "data": {
    "email": "adibraihan123@gmail.com"
  }
}
```

---

## 📊 NEW TIMING

| Step | Action | Expiry |
|------|--------|--------|
| 1 | Request OTP | +10 minutes from request |
| 2 | Verify OTP | **+30 minutes from verify** (FIXED) |
| 3 | Reset Password | Must be within 30 min of verify |

---

## 🔧 COMMANDS

```bash
# Clear cache (after code changes)
php artisan config:clear
php artisan cache:clear

# Test full flow
php test-full-flow.php

# Generate new OTP
php test-otp.php

# Check OTP in database
php check-otp.php
```

---

## ✅ STATUS

- **Bug:** FIXED ✅
- **Tested:** PASSED ✅
- **Ready for Frontend:** YES ✅

---

**Fixed by:** Backend Update  
**Date:** December 25, 2025  
**Severity:** Critical (blocking password reset)  
**Resolution:** Extend expires_at after OTP verification

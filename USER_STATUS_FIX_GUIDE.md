# 🔍 USER STATUS VERIFICATION GUIDE
**Mendaur - Check & Fix User Status**  
**Date:** December 25, 2025

---

## ❗ PROBLEM IDENTIFIED

**Email:** `adibraihan123@gmail.com`  
**Error:** 403 Forbidden  
**Backend Message:** "Akun tidak aktif. Silakan hubungi administrator."

**Root Cause:**  
User exists in database but `status` column is NOT `'active'`

---

## 🔍 STEP 1: CHECK USER STATUS IN DATABASE

### **Option 1: Via MySQL/phpMyAdmin**

```sql
-- Check user status
SELECT 
  user_id,
  nama,
  email,
  status,
  created_at,
  updated_at
FROM users 
WHERE email = 'adibraihan123@gmail.com';
```

**Expected Output:**
```
user_id | nama  | email                      | status    | created_at | updated_at
--------|-------|----------------------------|-----------|------------|------------
XX      | Adib  | adibraihan123@gmail.com    | inactive  | ...        | ...
```

**Possible Status Values:**
- `active` ✅ (can login and use forgot password)
- `inactive` ❌ (403 Forbidden)
- `suspended` ❌ (403 Forbidden)

---

## 🔧 STEP 2: FIX USER STATUS

### **Option A: Update Via SQL (Recommended for Testing)**

```sql
-- Update user status to active
UPDATE users 
SET status = 'active',
    updated_at = NOW()
WHERE email = 'adibraihan123@gmail.com';

-- Verify the change
SELECT 
  user_id,
  nama,
  email,
  status,
  updated_at
FROM users 
WHERE email = 'adibraihan123@gmail.com';
```

### **Option B: Via Laravel Tinker**

```bash
# Open Laravel tinker
php artisan tinker

# Find user and update status
$user = App\Models\User::where('email', 'adibraihan123@gmail.com')->first();
$user->status = 'active';
$user->save();

# Verify
$user->refresh();
echo $user->status; // Should print: active
```

### **Option C: Create Activation Script**

Save this as `activate_user.php` in Laravel root:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'adibraihan123@gmail.com';

$user = User::where('email', $email)->first();

if ($user) {
    $user->status = 'active';
    $user->save();
    echo "✅ User {$email} activated successfully!\n";
    echo "Status: {$user->status}\n";
} else {
    echo "❌ User {$email} not found!\n";
}
```

**Run:**
```bash
php activate_user.php
```

---

## 🧪 STEP 3: TEST AGAIN

After activating the user:

1. **Refresh your browser** (or clear cache)
2. Go to Forgot Password page
3. Enter: `adibraihan123@gmail.com`
4. Click "Kirim Kode OTP"

**Expected Result:**
```javascript
Forgot Password Response Status: 200
Forgot Password Response Data: {
  success: true,
  message: "OTP has been sent to your email"
}
```

**UI Should:**
- ✅ Show success message: "Kode OTP telah dikirim ke email Anda"
- ✅ Move to OTP verification step
- ✅ Start 60-second countdown timer
- ✅ Display 6 OTP input boxes

---

## 🔍 STEP 4: VERIFY EMAIL IN INBOX

Check email inbox for:
- **Subject:** Something like "Reset Password OTP - Mendaur"
- **Content:** 6-digit OTP code
- **Sender:** Your configured email

**Note:** If email not received:
1. Check spam/junk folder
2. Verify Laravel mail configuration
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify mail driver is configured (`.env` file)

---

## 📋 COMMON SCENARIOS

### **Scenario 1: User Just Registered**
**Status:** Usually `active` by default  
**If inactive:** Backend may set new users to `inactive` until email verification

### **Scenario 2: User Was Suspended**
**Status:** `suspended`  
**Reason:** Admin action (violation, suspicious activity, etc.)  
**Fix:** Admin must manually reactivate

### **Scenario 3: User Self-Deactivated**
**Status:** `inactive`  
**Reason:** User requested account deactivation  
**Fix:** Support ticket or admin reactivation

---

## 🛠️ BACKEND CONFIGURATION CHECK

### **Check User Registration Default Status**

**File:** `app/Http/Controllers/AuthController.php` or similar

```php
// Check register method
public function register(Request $request) {
    $user = User::create([
        'nama' => $request->nama,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'status' => 'active', // ⬅️ Should be 'active' by default
        // ... other fields
    ]);
}
```

**Default Value in Migration:**

**File:** `database/migrations/XXXX_create_users_table.php`

```php
Schema::create('users', function (Blueprint $table) {
    // ...
    $table->enum('status', ['active', 'inactive', 'suspended'])
          ->default('active'); // ⬅️ Should default to 'active'
    // ...
});
```

---

## 🎯 QUICK FIX SQL COMMAND

```sql
-- Activate ALL inactive users (for testing only!)
UPDATE users 
SET status = 'active',
    updated_at = NOW()
WHERE status != 'active';

-- Activate specific user
UPDATE users 
SET status = 'active',
    updated_at = NOW()
WHERE email = 'adibraihan123@gmail.com';

-- Check all users status
SELECT 
  user_id,
  nama,
  email,
  status,
  created_at
FROM users 
ORDER BY user_id DESC
LIMIT 10;
```

---

## 📊 VERIFICATION CHECKLIST

After fixing:

- [ ] User status in database is `'active'`
- [ ] `updated_at` timestamp is recent
- [ ] Forgot password returns 200 (not 403)
- [ ] OTP sent to email
- [ ] Can move to OTP verification step
- [ ] Timer starts counting down
- [ ] No 403 error in console

---

## 🚨 IF STILL GETTING 403 AFTER ACTIVATION

### **Check These:**

1. **Database Connection**
   ```bash
   # Verify Laravel is using correct database
   php artisan config:cache
   php artisan cache:clear
   ```

2. **Case Sensitivity**
   - Email in database might have different casing
   - Try: `SELECT * FROM users WHERE LOWER(email) = 'adibraihan123@gmail.com'`

3. **Multiple Users**
   - Check if there are multiple records with same email
   ```sql
   SELECT COUNT(*) FROM users WHERE email = 'adibraihan123@gmail.com';
   ```

4. **Backend Cache**
   ```bash
   # Clear Laravel cache
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

5. **Check Exact Backend Logic**
   - Look at `ForgotPasswordController` or equivalent
   - Verify status check logic

---

## 💡 PERMANENT FIX RECOMMENDATION

### **For New Users - Set Active by Default**

**In UserController or AuthController:**

```php
public function register(Request $request) {
    $validated = $request->validate([...]);
    
    $user = User::create([
        ...$validated,
        'status' => 'active', // ✅ Always active on registration
        'password' => Hash::make($validated['password']),
    ]);
    
    return response()->json([
        'status' => 'success',
        'message' => 'Registration successful',
        'data' => $user
    ]);
}
```

### **Optional: Email Verification Flow**

If you want users to verify email before being active:

1. Set status to `'inactive'` on registration
2. Send verification email with token
3. On email verification success, set status to `'active'`
4. Forgot password only works for `'active'` users

---

## 🎬 NEXT STEPS

1. **Run SQL Command:**
   ```sql
   UPDATE users SET status = 'active' WHERE email = 'adibraihan123@gmail.com';
   ```

2. **Test Forgot Password Again**

3. **Share Console Output:**
   - Should show `200` status
   - Should show success message
   - Should move to OTP step

4. **Check Email Inbox:**
   - Look for OTP email
   - Note the 6-digit code

5. **Complete OTP Flow:**
   - Enter OTP code
   - Set new password
   - Verify password reset works

---

## 📧 EMAIL NOT RECEIVED?

### **Check Laravel Mail Config**

**File:** `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mendaur.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

### **Test Email Sending**

```bash
php artisan tinker

# Test mail
Mail::raw('Test email from Mendaur', function($msg) {
    $msg->to('adibraihan123@gmail.com')->subject('Test');
});
```

---

## ✅ SUCCESS INDICATORS

You'll know it's fixed when:

1. ✅ Console shows: `Forgot Password Response Status: 200`
2. ✅ No 403 error
3. ✅ Success message appears
4. ✅ OTP input step shows
5. ✅ Email with OTP arrives
6. ✅ Can complete password reset

---

**End of Guide**  
**Last Updated:** December 25, 2025  

**Quick Command to Copy-Paste:**
```sql
UPDATE users SET status = 'active', updated_at = NOW() WHERE email = 'adibraihan123@gmail.com';
```

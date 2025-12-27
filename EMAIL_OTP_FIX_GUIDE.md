# 📧 EMAIL OTP FIX GUIDE - Forgot Password
**Mendaur Bank Sampah API**  
**Date:** December 25, 2025

---

## 🚨 MASALAH TERIDENTIFIKASI

**Gejala:**
- Frontend tidak menerima email OTP
- Endpoint `/api/forgot-password` berhasil (200), tapi email tidak sampai
- User tidak bisa reset password

**Root Cause:**
```env
MAIL_MAILER=log  ← EMAIL TIDAK DIKIRIM, HANYA DI-LOG!
```

**Artinya:** Laravel mencatat email ke file log (`storage/logs/laravel.log`) tapi **TIDAK MENGIRIM** ke email sungguhan.

---

## ✅ SOLUSI 1: MENGGUNAKAN GMAIL SMTP (RECOMMENDED)

### **Step 1: Persiapkan Gmail Account**

1. **Login ke Gmail** yang akan digunakan untuk mengirim OTP
2. **Enable 2-Step Verification:**
   - Go to: https://myaccount.google.com/security
   - Enable "2-Step Verification"

3. **Generate App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Windows Computer"
   - Generate password (16 karakter)
   - **COPY dan SIMPAN** password ini

### **Step 2: Update `.env` File**

Buka file `.env` di root project, ubah bagian MAIL:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-digit-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

**Contoh Lengkap:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=adibraihan123@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=adibraihan123@gmail.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

### **Step 3: Clear Config Cache**

```bash
cd c:\Users\Adib\Desktop\mendaur-api2
php artisan config:clear
php artisan cache:clear
```

### **Step 4: Test Email Sending**

Buat file `test-email.php` di root project:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email dari Mendaur - OTP System Working!', function($message) {
        $message->to('adibraihan123@gmail.com')
                ->subject('Test Email - Mendaur');
    });
    
    echo "✅ Email sent successfully! Check your inbox.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

**Run:**
```bash
php test-email.php
```

**Expected:** Email muncul di inbox dalam 1-2 menit

---

## ✅ SOLUSI 2: MENGGUNAKAN MAILTRAP (FOR TESTING)

### **Perfect untuk Development/Testing**

1. **Sign up** di https://mailtrap.io (FREE)
2. **Copy credentials** dari inbox settings

### **Update `.env`:**

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@mendaur.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

### **Clear Cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

**Keuntungan Mailtrap:**
- ✅ Tidak perlu Gmail App Password
- ✅ Semua email tertangkap di dashboard Mailtrap
- ✅ Bisa test tanpa kirim email sungguhan
- ✅ Perfect untuk development

---

## ✅ SOLUSI 3: SENDGRID / MAILGUN (FOR PRODUCTION)

### **SendGrid Setup:**

1. Sign up di https://sendgrid.com
2. Create API Key
3. Verify sender identity

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

---

## 🧪 TESTING FORGOT PASSWORD FLOW

### **Step 1: Update .env dan Clear Cache**
```bash
# Update MAIL_MAILER dari 'log' ke 'smtp'
# Isi semua MAIL_* credentials

php artisan config:clear
php artisan cache:clear
```

### **Step 2: Test dengan cURL**

```bash
curl -X POST http://localhost:8000/api/forgot-password \
  -H "Content-Type: application/json" \
  -d "{\"email\": \"adibraihan123@gmail.com\"}"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "OTP sent to your email address",
  "data": {
    "email": "adibraihan123@gmail.com",
    "expires_at": "2025-12-25 15:00:00"
  }
}
```

### **Step 3: Check Email Inbox**

**Subject:** "Reset Your Password - Mendaur Bank Sampah"  
**Content:** 6-digit OTP code  

**If using Mailtrap:** Check Mailtrap inbox  
**If using Gmail:** Check Gmail inbox (and spam folder)

### **Step 4: Verify OTP**

```bash
curl -X POST http://localhost:8000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d "{\"email\": \"adibraihan123@gmail.com\", \"otp\": \"123456\"}"
```

### **Step 5: Reset Password**

```bash
curl -X POST http://localhost:8000/api/reset-password \
  -H "Content-Type: application/json" \
  -d "{\"reset_token\": \"your-token-here\", \"password\": \"newPassword123\", \"password_confirmation\": \"newPassword123\"}"
```

---

## 🔍 TROUBLESHOOTING

### **Problem 1: "Connection refused" Error**

**Cause:** SMTP credentials salah atau firewall blocking

**Fix:**
```bash
# Test koneksi SMTP
telnet smtp.gmail.com 587

# If failed, check:
# 1. Firewall settings
# 2. Antivirus blocking
# 3. Credentials di .env
```

### **Problem 2: "Authentication failed"**

**Cause:** Gmail App Password salah atau expired

**Fix:**
1. Regenerate Gmail App Password
2. Update `.env` dengan password baru
3. Run `php artisan config:clear`

### **Problem 3: Email masuk Spam**

**Fix:**
1. Check "Not Spam" di Gmail
2. Add sender ke contacts
3. Use verified domain untuk production (SendGrid/Mailgun)

### **Problem 4: "Swift_TransportException"**

**Cause:** Port atau encryption salah

**Fix:**
```env
# Try different port combinations:

# Option 1 (TLS):
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Option 2 (SSL):
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

---

## 📋 QUICK CHECKLIST

Sebelum test forgot password:

- [ ] `.env` updated dengan MAIL_MAILER=smtp
- [ ] MAIL_HOST diisi (gmail/mailtrap/sendgrid)
- [ ] MAIL_USERNAME diisi
- [ ] MAIL_PASSWORD diisi (App Password untuk Gmail)
- [ ] MAIL_FROM_ADDRESS diisi
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan cache:clear`
- [ ] Test dengan `test-email.php` script
- [ ] Verify email sampai di inbox
- [ ] Test forgot password flow dari frontend

---

## 🎯 QUICK FIX COMMAND

**Copy-paste ini ke terminal:**

```powershell
# Navigate to project
cd c:\Users\Adib\Desktop\mendaur-api2

# Clear all cache
php artisan config:clear; php artisan cache:clear; php artisan route:clear

# Verify routes registered
php artisan route:list | Select-String -Pattern "forgot"

# Check logs for email content
Get-Content storage\logs\laravel.log | Select-String -Pattern "OTP" -Context 5
```

---

## 📧 EMAIL TEMPLATE PREVIEW

Saat email terkirim, user akan menerima:

```
Subject: Reset Your Password - Mendaur Bank Sampah

Body:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Reset Your Password
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Hello,

You requested to reset your password. 
Use the following OTP code:

┌─────────────────────┐
│   OTP: 123456      │
└─────────────────────┘

This code will expire in 10 minutes.

If you didn't request this, please ignore.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
© 2025 Mendaur Bank Sampah
```

---

## 🚀 AFTER FIX - FRONTEND INTEGRATION

Frontend akan berfungsi normal:

1. **User enter email** → Click "Kirim OTP"
2. **Backend send email** → Returns 200 success
3. **User receives email** → Copy 6-digit OTP
4. **User enter OTP** → Verify OTP
5. **Backend returns token** → Allow password reset
6. **User set new password** → Password updated

---

## 📝 CONFIGURATION COMPARISON

### **BEFORE (Broken):**
```env
MAIL_MAILER=log          ❌ Email tidak terkirim
MAIL_HOST=127.0.0.1      ❌ Invalid host
MAIL_USERNAME=null       ❌ No credentials
MAIL_PASSWORD=null       ❌ No credentials
```

### **AFTER (Working):**
```env
MAIL_MAILER=smtp                        ✅ Send real emails
MAIL_HOST=smtp.gmail.com                ✅ Valid Gmail SMTP
MAIL_USERNAME=adibraihan123@gmail.com   ✅ Valid email
MAIL_PASSWORD=abcd efgh ijkl mnop       ✅ App Password
MAIL_ENCRYPTION=tls                     ✅ Secure connection
MAIL_FROM_ADDRESS=adibraihan123@gmail.com  ✅ Valid sender
```

---

## 🎬 NEXT STEPS

1. **Pilih mail provider:**
   - Mailtrap (development/testing)
   - Gmail (quick production)
   - SendGrid/Mailgun (professional production)

2. **Update `.env` file** dengan credentials yang benar

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Test email sending** dengan script test

5. **Test forgot password** dari frontend

6. **Verify email received** di inbox

7. **Complete OTP flow** end-to-end

---

## 🆘 NEED HELP?

**Check Laravel Log:**
```bash
Get-Content storage\logs\laravel.log | Select-Object -Last 100
```

**Check Queue Workers (if using queue):**
```bash
php artisan queue:work --once
```

**Test mail config:**
```bash
php artisan tinker
Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

---

**Status:** Ready to implement  
**Priority:** HIGH (blocking forgot password feature)  
**Est. Time:** 10-15 minutes to configure  

**Quick Command:**
```powershell
# 1. Edit .env file manually
# 2. Run this:
cd c:\Users\Adib\Desktop\mendaur-api2; php artisan config:clear; php artisan cache:clear
```

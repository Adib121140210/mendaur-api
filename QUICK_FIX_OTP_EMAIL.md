# 🎯 FORGOT PASSWORD OTP - MASALAH & SOLUSI LENGKAP
**Mendaur Bank Sampah API**  
**Date:** December 25, 2025

---

## 🚨 MASALAH YANG TERIDENTIFIKASI

### **Masalah Utama: EMAIL OTP TIDAK TERKIRIM**

**Gejala:**
- ✅ Frontend request berhasil (200 OK)
- ✅ Backend response success
- ❌ **Email OTP TIDAK sampai ke inbox**

**Root Cause:**

```env
MAIL_MAILER=log  ← Ini penyebabnya!
```

**Artinya:**
Laravel **TIDAK MENGIRIM EMAIL** ke inbox sungguhan. Email hanya di-log ke file `storage/logs/laravel.log`.

---

## ✅ SOLUSI LENGKAP - STEP BY STEP

### **STEP 1: Update File .env**

Buka file `.env` di root project, cari section MAIL (sekitar line 51), lalu ubah:

**BEFORE (❌ Salah):**
```env
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**AFTER (✅ Benar):**

#### **Option A: Gunakan Gmail (Production Ready)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=adibraihan123@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=adibraihan123@gmail.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

**Note:** `MAIL_PASSWORD` adalah **App Password** dari Gmail (16 karakter), BUKAN password login biasa!

**Cara mendapatkan Gmail App Password:**
1. Login Gmail
2. Buka: https://myaccount.google.com/security
3. Enable "2-Step Verification" (jika belum)
4. Buka: https://myaccount.google.com/apppasswords
5. Select "Mail" → "Windows Computer"
6. Copy password 16 digit
7. Paste ke `.env` di `MAIL_PASSWORD`

#### **Option B: Gunakan Mailtrap (Testing/Development)**

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@mendaur.com
MAIL_FROM_NAME="Mendaur Bank Sampah"
```

**Cara mendapatkan Mailtrap credentials:**
1. Sign up FREE di: https://mailtrap.io
2. Buka inbox → Settings → SMTP
3. Copy Username dan Password
4. Paste ke `.env`

---

### **STEP 2: Clear Laravel Cache**

Setelah update `.env`, **WAJIB** clear cache:

```powershell
cd c:\Users\Adib\Desktop\mendaur-api2

php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

**Output yang diharapkan:**
```
✅ Configuration cache cleared successfully.
✅ Application cache cleared successfully.
✅ Route cache cleared successfully.
```

---

### **STEP 3: Test Email Configuration**

Jalankan test script untuk memastikan email berfungsi:

```powershell
php test-email.php
```

**Saat diminta email, masukkan:** `adibraihan123@gmail.com`

**Expected Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📧 EMAIL CONFIGURATION TEST
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📋 Current Email Configuration:
   MAIL_MAILER: smtp
   MAIL_HOST: smtp.gmail.com
   MAIL_PORT: 587
   MAIL_USERNAME: adibraihan123@gmail.com
   ...

Enter test email address: adibraihan123@gmail.com

🔄 Sending test email...

✅ Email sent successfully!

📬 Next steps:
   1. Check inbox: adibraihan123@gmail.com
   2. Check spam/junk folder if not in inbox
   3. Wait 1-2 minutes for delivery
```

**Jika ERROR:**
- Cek credentials di `.env`
- Pastikan Gmail App Password benar
- Run `php artisan config:clear` lagi

---

### **STEP 4: Test OTP System**

Jalankan test OTP lengkap:

```powershell
php test-otp.php
```

**Saat diminta email, masukkan:** `adibraihan123@gmail.com`

**Expected Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔐 FORGOT PASSWORD OTP TEST
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Enter email address to test: adibraihan123@gmail.com

🔍 Checking user...
✅ User found: Adib
   Status: active

🔢 Generating OTP...
   OTP: 123456
   Expires: 2025-12-25 15:30:00

✅ OTP record created in database

📧 Sending OTP email...
✅ Email sent successfully!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 TEST SUMMARY
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Email: adibraihan123@gmail.com
OTP Code: 123456
Expires: 2025-12-25 15:30:00
Status: ✅ Ready for testing
```

**Check inbox** → Email OTP harus sampai!

---

### **STEP 5: Test dari Frontend**

Sekarang frontend sudah bisa test:

1. **Buka halaman Forgot Password**
2. **Enter email:** `adibraihan123@gmail.com`
3. **Click:** "Kirim Kode OTP"
4. **Wait:** 1-2 menit
5. **Check inbox** → Buka email OTP
6. **Copy OTP** (6 digit)
7. **Paste di form** → Verify
8. **Set new password** → Reset

**Expected Console:**
```javascript
Forgot Password Response Status: 200
Forgot Password Response Data: {
  success: true,
  message: "OTP sent to your email address"
}
```

**Expected Email:**
```
Subject: Reset Your Password - Mendaur Bank Sampah

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
```

---

## 🔥 QUICK FIX COMMAND (ALL-IN-ONE)

Copy-paste ini ke PowerShell untuk fix cepat:

```powershell
# Navigate to project
cd c:\Users\Adib\Desktop\mendaur-api2

# 1. Clear all cache
php artisan config:clear; php artisan cache:clear; php artisan route:clear

# 2. Test email config
php test-email.php

# 3. Test OTP system
php test-otp.php
```

**Manual step:** Edit `.env` file terlebih dahulu (lihat STEP 1)

---

## 📋 CHECKLIST SEBELUM PRODUCTION

Sebelum go-live, pastikan:

- [ ] ✅ `.env` updated: `MAIL_MAILER=smtp`
- [ ] ✅ SMTP credentials configured (Gmail/SendGrid/Mailgun)
- [ ] ✅ Gmail App Password (bukan password biasa)
- [ ] ✅ Cache cleared: `php artisan config:clear`
- [ ] ✅ Test email berhasil: `php test-email.php`
- [ ] ✅ Test OTP berhasil: `php test-otp.php`
- [ ] ✅ Email sampai di inbox (bukan spam)
- [ ] ✅ User status = 'active' di database
- [ ] ✅ Frontend test berhasil end-to-end
- [ ] ✅ Password reset berhasil

---

## 🐛 TROUBLESHOOTING

### **Problem: "Authentication failed" Error**

**Error:**
```
Swift_TransportException: Failed to authenticate on SMTP server
```

**Solution:**
- Gmail App Password salah → Regenerate
- 2-Step Verification belum aktif → Enable di Gmail
- Run `php artisan config:clear` setelah update `.env`

---

### **Problem: Email masuk Spam**

**Solution:**
- Click "Not Spam" di Gmail
- Add sender ke contacts
- Untuk production: gunakan verified domain (SendGrid/Mailgun)

---

### **Problem: "Connection refused"**

**Error:**
```
Connection could not be established with host smtp.gmail.com
```

**Solution:**
- Check internet connection
- Check firewall/antivirus blocking port 587
- Try port 465 dengan `MAIL_ENCRYPTION=ssl`

---

### **Problem: User status tidak aktif**

**Error (403):**
```json
{
  "success": false,
  "message": "Akun tidak aktif"
}
```

**Solution:**
```sql
UPDATE users 
SET status = 'active', updated_at = NOW() 
WHERE email = 'adibraihan123@gmail.com';
```

---

## 📊 BEFORE vs AFTER

### **BEFORE (Tidak jalan):**
```
User → Frontend → Backend → Log file ❌
                           └─ Email TIDAK terkirim
```

### **AFTER (Jalan dengan benar):**
```
User → Frontend → Backend → Gmail SMTP → User Inbox ✅
                           └─ Email TERKIRIM
```

---

## 🎯 SUMMARY

**Masalah:** Email OTP tidak sampai karena `MAIL_MAILER=log`

**Solusi:**
1. Update `.env`: `MAIL_MAILER=smtp`
2. Tambah Gmail App Password
3. Clear cache: `php artisan config:clear`
4. Test: `php test-email.php`
5. Test: `php test-otp.php`
6. Frontend test forgot password flow

**Time needed:** 10-15 menit

**Priority:** 🔴 HIGH (blocking feature)

---

## 📞 SUPPORT

**Jika masih error:**

1. Check Laravel log:
```powershell
Get-Content storage\logs\laravel.log | Select-Object -Last 50
```

2. Check mail config:
```powershell
php artisan tinker
Config::get('mail')
```

3. Share error message untuk debugging

---

**Status:** Ready to implement ✅  
**Files created:**
- ✅ `test-email.php` - Email configuration test
- ✅ `test-otp.php` - OTP system test
- ✅ `EMAIL_OTP_FIX_GUIDE.md` - Detailed email fix guide
- ✅ `FRONTEND_FORGOT_PASSWORD_GUIDE.md` - Frontend integration guide
- ✅ `FORGOT_PASSWORD_API.md` - API documentation
- ✅ `USER_STATUS_FIX_GUIDE.md` - User status fix guide

**All documentation and test scripts ready!** 🚀

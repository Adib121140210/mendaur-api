# 🐛 FORGOT PASSWORD - DEBUGGING GUIDE
**Frontend Error Troubleshooting**  
**Date:** December 25, 2025

---

## ❌ ERROR: "Kode OTP tidak valid atau sudah kedaluwarsa"

### **Error Log dari Frontend:**
```
POST http://127.0.0.1:8000/api/verify-otp 400 (Bad Request)
Verify OTP error: Error: Kode OTP tidak valid atau sudah kedaluwarsa
```

### **Successful Steps:**
✅ Step 1: Send OTP - **200 OK**
```javascript
{
  success: true,
  message: 'Kode OTP telah dikirim ke email Anda',
  data: { email: 'adibraihan123@gmail.com', expires_in: 900 }
}
```

❌ Step 2: Verify OTP - **400 Bad Request**

---

## 🔍 POSSIBLE CAUSES

### **1. OTP Expired (Most Common)**
**Symptom:** OTP valid untuk 10 menit (900 detik)

**Check:**
```bash
php check-otp.php
```

**Output:**
```
Email: adibraihan123@gmail.com
OTP: 691700
Expires: 2025-12-25 16:10:01
Status: ❌ EXPIRED
```

**Solution:** Request OTP baru dengan click "Resend OTP"

---

### **2. Wrong OTP Code**
**Symptom:** User memasukkan OTP yang salah

**Common Mistakes:**
- Typo saat input
- Copy paste dengan spasi extra
- Menggunakan OTP lama
- Case sensitivity (shouldn't be, but check)

**Solution:**
- Double check OTP dari email
- Pastikan tidak ada spasi di awal/akhir
- Copy paste langsung dari email

---

### **3. OTP Not Found in Database**
**Symptom:** Database tidak punya record OTP

**Check:**
```bash
php check-otp.php
```

**If empty:** Backend gagal save OTP ke database

**Solution:**
- Check database connection
- Check `password_resets` table exists
- Request OTP baru

---

### **4. Email Mismatch**
**Symptom:** Email di verify berbeda dengan email saat request

**Example:**
```javascript
// Send OTP
{ email: 'user@gmail.com' }

// Verify OTP (WRONG - different email!)
{ email: 'user@yahoo.com', otp: '123456' }
```

**Solution:** Pastikan email sama persis

---

## ✅ HOW TO FIX - FRONTEND

### **Solution 1: Add Expiry Timer**

Show countdown timer to user:

```javascript
const [expiryTime, setExpiryTime] = useState(null);
const [timeLeft, setTimeLeft] = useState(600); // 10 minutes = 600 seconds

// After sending OTP
const handleSendOTP = async () => {
  const response = await sendOTP(email);
  if (response.success) {
    const expirySeconds = response.data.expires_in; // 900
    setTimeLeft(expirySeconds);
    startCountdown(expirySeconds);
  }
};

// Countdown timer
const startCountdown = (seconds) => {
  const interval = setInterval(() => {
    setTimeLeft(prev => {
      if (prev <= 1) {
        clearInterval(interval);
        return 0;
      }
      return prev - 1;
    });
  }, 1000);
};

// Display timer
<div className="otp-timer">
  {timeLeft > 0 ? (
    <p>Kode OTP valid untuk: {Math.floor(timeLeft / 60)}:{(timeLeft % 60).toString().padStart(2, '0')}</p>
  ) : (
    <p className="expired">❌ Kode OTP kedaluwarsa. Silakan request ulang.</p>
  )}
</div>
```

---

### **Solution 2: Show Resend Button**

```javascript
{timeLeft === 0 && (
  <button onClick={handleResendOTP}>
    Kirim Ulang OTP
  </button>
)}
```

---

### **Solution 3: Better Error Messages**

```javascript
const handleVerifyOTP = async () => {
  try {
    const response = await fetch('/api/verify-otp', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, otp })
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      // Handle specific error
      if (response.status === 400) {
        if (data.message.includes('kedaluwarsa')) {
          setError('Kode OTP sudah kedaluwarsa. Silakan request ulang.');
          setShowResend(true);
        } else {
          setError('Kode OTP salah. Silakan cek kembali email Anda.');
        }
      }
      return;
    }
    
    // Success
    setResetToken(data.data.reset_token);
    goToPasswordResetStep();
    
  } catch (error) {
    setError('Terjadi kesalahan. Silakan coba lagi.');
  }
};
```

---

### **Solution 4: Input Validation**

```javascript
// Validate OTP input
const validateOTP = (otp) => {
  // Remove spaces
  const cleanOTP = otp.trim().replace(/\s/g, '');
  
  // Check length
  if (cleanOTP.length !== 6) {
    return { valid: false, error: 'OTP harus 6 digit' };
  }
  
  // Check numeric
  if (!/^\d{6}$/.test(cleanOTP)) {
    return { valid: false, error: 'OTP hanya boleh angka' };
  }
  
  return { valid: true, otp: cleanOTP };
};

// Use in submit
const handleVerifyOTP = async () => {
  const validation = validateOTP(otpInput);
  
  if (!validation.valid) {
    setError(validation.error);
    return;
  }
  
  // Send to API
  const response = await verifyOTP(email, validation.otp);
  // ...
};
```

---

## 🧪 TESTING WORKFLOW

### **Step 1: Request New OTP**

```bash
# Backend
php test-otp.php
# Enter: adibraihan123@gmail.com
```

**Expected Output:**
```
✅ User found: adib123
🔢 Generating OTP...
   OTP: 123456
   Expires: 2025-12-25 16:30:00
✅ Email sent successfully!
```

---

### **Step 2: Check Database**

```bash
php check-otp.php
```

**Expected:**
```
Email: adibraihan123@gmail.com
OTP: 123456
Expires: 2025-12-25 16:30:00
Status: ✅ VALID
```

---

### **Step 3: Test Verify (Immediate)**

**Within 10 minutes**, test verify:

```javascript
// Frontend
await fetch('/api/verify-otp', {
  method: 'POST',
  body: JSON.stringify({
    email: 'adibraihan123@gmail.com',
    otp: '123456' // Use actual OTP from step 1
  })
});
```

**Expected:** 200 OK with reset_token

---

## 📊 BACKEND TIMING

```
OTP Request Time: 15:55:01
OTP Expires: 16:05:01 (10 minutes later)
Current Time: 16:00:03

Time Left: ~5 minutes ✅ VALID

If Current Time: 16:10:02
Time Left: EXPIRED ❌
```

---

## 🔧 BACKEND FIXES (If Needed)

### **If you want LONGER expiry:**

**File:** `app/Http/Controllers/Auth/ForgotPasswordController.php`

```php
// Change from 15 to 30 minutes
'expires_at' => Carbon::now()->addMinutes(30),
```

**Update response:**
```php
'data' => [
    'email' => $email,
    'expires_in' => 1800 // 30 minutes in seconds
]
```

---

## 💡 QUICK FIX FOR TESTING

**If OTP expired, request new one:**

```bash
# Backend - Generate new OTP
php test-otp.php

# Copy the OTP from output
# OTP: 123456

# Use immediately in frontend (within 10 minutes!)
```

---

## 📝 COMPLETE FRONTEND FLOW WITH TIMER

```javascript
import { useState, useEffect } from 'react';

function ForgotPassword() {
  const [email, setEmail] = useState('');
  const [otp, setOtp] = useState('');
  const [step, setStep] = useState(1); // 1: email, 2: otp, 3: password
  const [timeLeft, setTimeLeft] = useState(0);
  const [canResend, setCanResend] = useState(false);
  const [error, setError] = useState('');

  // Timer effect
  useEffect(() => {
    if (timeLeft <= 0) {
      setCanResend(true);
      return;
    }

    const timer = setInterval(() => {
      setTimeLeft(prev => prev - 1);
    }, 1000);

    return () => clearInterval(timer);
  }, [timeLeft]);

  const handleSendOTP = async () => {
    try {
      const res = await fetch('/api/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });

      const data = await res.json();

      if (res.ok) {
        setStep(2);
        setTimeLeft(data.data.expires_in); // 900 seconds
        setCanResend(false);
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Network error');
    }
  };

  const handleVerifyOTP = async () => {
    // Validate first
    if (timeLeft <= 0) {
      setError('Kode OTP sudah kedaluwarsa. Silakan request ulang.');
      return;
    }

    try {
      const res = await fetch('/api/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, otp: otp.trim() })
      });

      const data = await res.json();

      if (res.ok) {
        setStep(3);
        // Save reset_token for password reset
        sessionStorage.setItem('reset_token', data.data.reset_token);
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Network error');
    }
  };

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div>
      {step === 2 && (
        <div>
          <h2>Enter OTP</h2>
          <p>OTP sent to {email}</p>
          
          {timeLeft > 0 ? (
            <p className="timer">⏱️ Valid for: {formatTime(timeLeft)}</p>
          ) : (
            <p className="expired">❌ OTP expired</p>
          )}

          <input
            value={otp}
            onChange={e => setOtp(e.target.value)}
            maxLength={6}
            placeholder="Enter 6-digit OTP"
          />

          <button 
            onClick={handleVerifyOTP}
            disabled={timeLeft <= 0}
          >
            Verify OTP
          </button>

          {canResend && (
            <button onClick={handleSendOTP}>
              Resend OTP
            </button>
          )}

          {error && <p className="error">{error}</p>}
        </div>
      )}
    </div>
  );
}
```

---

## ✅ CHECKLIST SEBELUM VERIFY

- [ ] OTP request successful (200 OK)
- [ ] Email received in inbox
- [ ] OTP copied correctly (6 digits)
- [ ] No extra spaces in OTP
- [ ] Same email used for send and verify
- [ ] Within 10 minutes of request
- [ ] Timer shows time remaining

---

## 🎯 SOLUTION SUMMARY

**Problem:** OTP expired (10 minutes timeout)

**Immediate Fix:**
1. Request new OTP (click "Resend")
2. Check email for new code
3. Enter new OTP immediately
4. Verify within 10 minutes

**Long-term Fix:**
1. Add countdown timer in UI
2. Show "expired" message when timer hits 0
3. Auto-show resend button when expired
4. Validate OTP before sending to API
5. Better error messages

---

**Test Script Available:**
```bash
# Check OTP in database
php check-otp.php

# Generate new OTP
php test-otp.php
```

---

**Status:** Issue identified - OTP expired  
**Solution:** Request new OTP and verify within 10 minutes  
**Frontend Improvement:** Add countdown timer ⏱️

# 🔧 FORGOT PASSWORD - TROUBLESHOOTING GUIDE FOR FRONTEND
**Mendaur Bank Sampah**  
**Date:** December 25, 2025

---

## 🚨 CURRENT ISSUE: OTP Email Not Received

### **Symptom:**
- Frontend sends request to `/api/forgot-password` ✅
- Backend returns 200 success ✅  
- Email NEVER arrives in inbox ❌

### **Root Cause:**
Backend email configuration is set to `log` mode instead of `smtp`.

---

## 📋 BACKEND REQUIREMENTS CHECKLIST

Share this with backend developer:

- [ ] **Update `.env` file** - Change `MAIL_MAILER=log` to `MAIL_MAILER=smtp`
- [ ] **Configure SMTP credentials** - Add Gmail/Mailtrap/SendGrid credentials
- [ ] **Clear Laravel cache** - Run `php artisan config:clear`
- [ ] **Test email sending** - Run `php test-email.php`
- [ ] **Verify OTP email** - Run `php test-otp.php` with test email
- [ ] **Check email arrives** - Verify inbox receives OTP email

---

## 🎯 QUICK FIX FOR BACKEND

**Tell backend to run these commands:**

```powershell
# 1. Edit .env file and update:
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-digit-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Mendaur Bank Sampah"

# 2. Clear cache
cd c:\Users\Adib\Desktop\mendaur-api2
php artisan config:clear
php artisan cache:clear

# 3. Test email
php test-email.php

# 4. Test OTP
php test-otp.php
```

---

## 🧪 TESTING WORKFLOW (AFTER BACKEND FIX)

### **Step 1: Test Send OTP API**

**Endpoint:** `POST /api/forgot-password`

**Request:**
```javascript
fetch('http://localhost:8000/api/forgot-password', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'test@example.com'
  })
})
.then(res => res.json())
.then(data => {
  console.log('Response:', data);
  // Expected: { success: true, message: "OTP sent..." }
});
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "OTP sent to your email address",
  "data": {
    "email": "test@example.com",
    "expires_at": "2025-12-25 15:30:00"
  }
}
```

**Check email inbox** → OTP email should arrive in 1-2 minutes

---

### **Step 2: Test Verify OTP API**

**Endpoint:** `POST /api/verify-otp`

**Request:**
```javascript
fetch('http://localhost:8000/api/verify-otp', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    email: 'test@example.com',
    otp: '123456' // From email
  })
})
.then(res => res.json())
.then(data => {
  console.log('Response:', data);
  // Expected: { success: true, data: { reset_token: "..." } }
  // SAVE reset_token for step 3
});
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "OTP verified successfully",
  "data": {
    "reset_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_at": "2025-12-25 15:45:00"
  }
}
```

**Save `reset_token`** → Use in step 3

---

### **Step 3: Test Reset Password API**

**Endpoint:** `POST /api/reset-password`

**Request:**
```javascript
const resetToken = 'eyJ0eXAiOiJKV1QiLCJhbGc...'; // From step 2

fetch('http://localhost:8000/api/reset-password', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    reset_token: resetToken,
    password: 'newPassword123',
    password_confirmation: 'newPassword123'
  })
})
.then(res => res.json())
.then(data => {
  console.log('Response:', data);
  // Expected: { success: true, message: "Password reset successfully" }
});
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

---

## 🐛 COMMON ERRORS & SOLUTIONS

### **Error 1: 403 Forbidden - "Akun tidak aktif"**

**Response:**
```json
{
  "success": false,
  "message": "Akun tidak aktif. Silakan hubungi administrator."
}
```

**Cause:** User status is NOT 'active' in database

**Backend Fix:**
```sql
UPDATE users SET status = 'active' WHERE email = 'user@example.com';
```

**Frontend Handling:**
```javascript
if (response.status === 403) {
  alert('Your account is inactive. Please contact support.');
}
```

---

### **Error 2: 404 Not Found - "User not found"**

**Response:**
```json
{
  "success": false,
  "message": "User not found with this email address"
}
```

**Cause:** Email doesn't exist in database

**Frontend Handling:**
```javascript
if (response.status === 404) {
  alert('No account found with this email. Please check your email or register.');
}
```

---

### **Error 3: 429 Too Many Requests - Rate Limited**

**Response:**
```json
{
  "success": false,
  "message": "Please wait before requesting another OTP"
}
```

**Cause:** User requested OTP within last 60 seconds

**Frontend Handling:**
```javascript
if (response.status === 429) {
  // Show countdown timer
  startCountdown(60); // seconds
  alert('Please wait 1 minute before requesting another OTP');
}
```

---

### **Error 4: 400 Bad Request - "Invalid or expired OTP"**

**Response:**
```json
{
  "success": false,
  "message": "Invalid or expired OTP"
}
```

**Cause:** OTP is wrong or expired (10 minutes)

**Frontend Handling:**
```javascript
if (response.status === 400) {
  alert('Invalid OTP code. Please check your email and try again.');
  // Offer "Resend OTP" button
}
```

---

### **Error 5: Email Never Arrives**

**Symptoms:**
- API returns 200 ✅
- No error in console ✅
- Email never arrives ❌

**Causes:**
1. **Backend using `MAIL_MAILER=log`** (most common)
2. Email in spam/junk folder
3. Wrong email address entered
4. Backend SMTP not configured

**Frontend Actions:**
1. Show message: "Email sent! Please check spam folder if not in inbox"
2. Add "Resend OTP" button (after 60 seconds)
3. Show email entered for confirmation

**Backend Check:**
```bash
# Check if email was logged (not sent)
Get-Content storage\logs\laravel.log | Select-String -Pattern "OTP"
```

---

## 📱 FRONTEND UX IMPROVEMENTS

### **1. Loading States**

```javascript
async function sendOTP(email) {
  setLoading(true);
  setError(null);
  
  try {
    const response = await fetch('/api/forgot-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    });
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to send OTP');
    }
    
    // Success
    setSuccess('OTP sent to your email!');
    showOTPInput();
    startCountdown(60);
    
  } catch (error) {
    setError(error.message);
  } finally {
    setLoading(false);
  }
}
```

---

### **2. Countdown Timer for Resend**

```javascript
function startCountdown(seconds) {
  let remaining = seconds;
  const timer = setInterval(() => {
    remaining--;
    document.getElementById('countdown').textContent = `Resend OTP in ${remaining}s`;
    
    if (remaining <= 0) {
      clearInterval(timer);
      enableResendButton();
    }
  }, 1000);
}
```

---

### **3. Email Confirmation**

```javascript
// Show email for user confirmation
function showEmailConfirmation(email) {
  return (
    <div className="email-confirmation">
      <p>OTP will be sent to:</p>
      <strong>{email}</strong>
      <button onClick={editEmail}>Change email</button>
    </div>
  );
}
```

---

### **4. Spam Folder Warning**

```javascript
function OTPSentMessage({ email }) {
  return (
    <div className="success-message">
      <h3>✅ OTP Sent!</h3>
      <p>Check your email: <strong>{email}</strong></p>
      <div className="warning">
        <p>⚠️ Can't find the email?</p>
        <ul>
          <li>Check spam/junk folder</li>
          <li>Wait 1-2 minutes</li>
          <li>Click "Resend OTP" if needed</li>
        </ul>
      </div>
    </div>
  );
}
```

---

### **5. OTP Input Auto-Focus**

```javascript
function OTPInput() {
  const inputs = useRef([]);
  
  const handleInput = (index, value) => {
    if (value && index < 5) {
      inputs.current[index + 1].focus();
    }
  };
  
  return (
    <div className="otp-inputs">
      {[0,1,2,3,4,5].map(i => (
        <input
          key={i}
          ref={el => inputs.current[i] = el}
          type="text"
          maxLength="1"
          onChange={e => handleInput(i, e.target.value)}
          className="otp-box"
        />
      ))}
    </div>
  );
}
```

---

## 🎨 USER FLOW DIAGRAM

```
┌─────────────────────────────────────────────────┐
│ 1. ENTER EMAIL                                  │
│    ┌──────────────────────────┐                │
│    │ email@example.com        │                │
│    └──────────────────────────┘                │
│    [Send OTP] ← Click                          │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 2. LOADING                                      │
│    ⏳ Sending OTP to your email...             │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 3. SUCCESS MESSAGE                              │
│    ✅ OTP sent to email@example.com            │
│    Check spam if not in inbox                   │
│    Resend OTP in 60s                           │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 4. ENTER OTP                                    │
│    ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐ ┌─┐                   │
│    │1│ │2│ │3│ │4│ │5│ │6│                   │
│    └─┘ └─┘ └─┘ └─┘ └─┘ └─┘                   │
│    [Verify OTP]                                │
│    [Resend OTP] ← Available after 60s          │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 5. VERIFIED                                     │
│    ✅ OTP verified successfully                │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 6. NEW PASSWORD                                 │
│    New Password:     ●●●●●●●●                  │
│    Confirm Password: ●●●●●●●●                  │
│    [Reset Password]                            │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ 7. SUCCESS                                      │
│    ✅ Password changed successfully!           │
│    Redirecting to login...                     │
└─────────────────────────────────────────────────┘
```

---

## 📝 COMPLETE FRONTEND EXAMPLE

```javascript
import { useState } from 'react';

function ForgotPasswordFlow() {
  const [step, setStep] = useState(1); // 1: email, 2: otp, 3: password
  const [email, setEmail] = useState('');
  const [otp, setOtp] = useState('');
  const [resetToken, setResetToken] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirm, setPasswordConfirm] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [countdown, setCountdown] = useState(0);

  // Step 1: Send OTP
  const handleSendOTP = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const res = await fetch('/api/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });
      
      const data = await res.json();
      
      if (!res.ok) {
        throw new Error(data.message);
      }
      
      setStep(2);
      startCountdown(60);
      
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Step 2: Verify OTP
  const handleVerifyOTP = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const res = await fetch('/api/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, otp })
      });
      
      const data = await res.json();
      
      if (!res.ok) {
        throw new Error(data.message);
      }
      
      setResetToken(data.data.reset_token);
      setStep(3);
      
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  // Step 3: Reset Password
  const handleResetPassword = async () => {
    if (password !== passwordConfirm) {
      setError('Passwords do not match');
      return;
    }
    
    setLoading(true);
    setError(null);
    
    try {
      const res = await fetch('/api/reset-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          reset_token: resetToken,
          password,
          password_confirmation: passwordConfirm
        })
      });
      
      const data = await res.json();
      
      if (!res.ok) {
        throw new Error(data.message);
      }
      
      alert('Password reset successfully!');
      window.location.href = '/login';
      
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const startCountdown = (seconds) => {
    setCountdown(seconds);
    const timer = setInterval(() => {
      setCountdown(prev => {
        if (prev <= 1) {
          clearInterval(timer);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  };

  return (
    <div className="forgot-password-container">
      {error && <div className="error">{error}</div>}
      
      {step === 1 && (
        <div>
          <h2>Forgot Password</h2>
          <input
            type="email"
            value={email}
            onChange={e => setEmail(e.target.value)}
            placeholder="Enter your email"
          />
          <button onClick={handleSendOTP} disabled={loading}>
            {loading ? 'Sending...' : 'Send OTP'}
          </button>
        </div>
      )}
      
      {step === 2 && (
        <div>
          <h2>Enter OTP</h2>
          <p>OTP sent to {email}</p>
          <input
            type="text"
            value={otp}
            onChange={e => setOtp(e.target.value)}
            placeholder="Enter 6-digit OTP"
            maxLength="6"
          />
          <button onClick={handleVerifyOTP} disabled={loading}>
            {loading ? 'Verifying...' : 'Verify OTP'}
          </button>
          {countdown > 0 ? (
            <p>Resend OTP in {countdown}s</p>
          ) : (
            <button onClick={handleSendOTP}>Resend OTP</button>
          )}
        </div>
      )}
      
      {step === 3 && (
        <div>
          <h2>New Password</h2>
          <input
            type="password"
            value={password}
            onChange={e => setPassword(e.target.value)}
            placeholder="New password"
          />
          <input
            type="password"
            value={passwordConfirm}
            onChange={e => setPasswordConfirm(e.target.value)}
            placeholder="Confirm password"
          />
          <button onClick={handleResetPassword} disabled={loading}>
            {loading ? 'Resetting...' : 'Reset Password'}
          </button>
        </div>
      )}
    </div>
  );
}

export default ForgotPasswordFlow;
```

---

## ✅ BACKEND READY CHECKLIST

Before frontend testing, confirm backend has:

- [ ] ✅ Email configuration changed from `log` to `smtp`
- [ ] ✅ SMTP credentials configured (Gmail/Mailtrap)
- [ ] ✅ Cache cleared (`php artisan config:clear`)
- [ ] ✅ Email test script passed (`php test-email.php`)
- [ ] ✅ OTP test script passed (`php test-otp.php`)
- [ ] ✅ Test email received in inbox
- [ ] ✅ All 4 routes working: forgot-password, verify-otp, reset-password, resend-otp
- [ ] ✅ User status is 'active' in database

---

## 🆘 NEED IMMEDIATE FIX?

**Backend Quick Fix (2 minutes):**

1. Open `.env` file
2. Change line 51: `MAIL_MAILER=smtp`
3. Add Gmail credentials (lines 52-58)
4. Run: `php artisan config:clear`
5. Test: `php test-email.php`

**Done!** Frontend can now test forgot password.

---

**Last Updated:** December 25, 2025  
**Status:** Documentation Complete ✅  
**Backend Fix Required:** Email configuration ⚠️

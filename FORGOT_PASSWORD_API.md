# Forgot Password API Endpoints

## Overview
The forgot password system uses a 3-step OTP (One-Time Password) verification process:
1. Send OTP to email
2. Verify OTP 
3. Reset password with new password

## Base URL
```
{your-api-base-url}/api/
```

---

## 1. Send OTP to Email

**Endpoint:** `POST /api/forgot-password`

**Description:** Sends a 6-digit OTP to the user's email address.

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "OTP sent to your email address",
    "data": {
        "email": "user@example.com",
        "expires_at": "2025-12-25 10:15:00"
    }
}
```

**Error Responses:**
- **404 - User not found:**
```json
{
    "success": false,
    "message": "User not found with this email address"
}
```

- **429 - Rate Limited:**
```json
{
    "success": false,
    "message": "Please wait before requesting another OTP"
}
```

---

## 2. Verify OTP

**Endpoint:** `POST /api/verify-otp`

**Description:** Verifies the OTP sent to the user's email and returns a reset token.

**Request Body:**
```json
{
    "email": "user@example.com",
    "otp": "123456"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "OTP verified successfully",
    "data": {
        "reset_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expires_at": "2025-12-25 10:45:00"
    }
}
```

**Error Responses:**
- **400 - Invalid/Expired OTP:**
```json
{
    "success": false,
    "message": "Invalid or expired OTP"
}
```

- **404 - No pending OTP:**
```json
{
    "success": false,
    "message": "No pending OTP found for this email"
}
```

---

## 3. Reset Password

**Endpoint:** `POST /api/reset-password`

**Description:** Resets the user's password using the reset token from OTP verification.

**Request Body:**
```json
{
    "reset_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "password": "newPassword123",
    "password_confirmation": "newPassword123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password reset successfully"
}
```

**Error Responses:**
- **400 - Invalid/Expired Token:**
```json
{
    "success": false,
    "message": "Invalid or expired reset token"
}
```

- **422 - Validation Error:**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "password": ["The password confirmation does not match."]
    }
}
```

---

## 4. Resend OTP (Optional)

**Endpoint:** `POST /api/resend-otp`

**Description:** Resends OTP if the previous one expired or was not received.

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "OTP resent to your email address",
    "data": {
        "email": "user@example.com",
        "expires_at": "2025-12-25 10:20:00"
    }
}
```

---

## Security Features

1. **Rate Limiting:** Users can only request OTP every 1 minute
2. **OTP Expiration:** OTPs expire after 10 minutes
3. **Token Expiration:** Reset tokens expire after 30 minutes
4. **One-time Use:** Both OTP and reset tokens are single-use
5. **Email Validation:** All requests validate email format and user existence

## Frontend Integration Flow

```javascript
// Step 1: Send OTP
const sendOTP = async (email) => {
    const response = await fetch('/api/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    });
    return await response.json();
};

// Step 2: Verify OTP
const verifyOTP = async (email, otp) => {
    const response = await fetch('/api/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, otp })
    });
    return await response.json();
};

// Step 3: Reset Password
const resetPassword = async (resetToken, password, passwordConfirmation) => {
    const response = await fetch('/api/reset-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            reset_token: resetToken,
            password,
            password_confirmation: passwordConfirmation
        })
    });
    return await response.json();
};
```

## Testing with cURL

```bash
# 1. Send OTP
curl -X POST http://your-domain.com/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com"}'

# 2. Verify OTP (replace 123456 with actual OTP from email)
curl -X POST http://your-domain.com/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com", "otp": "123456"}'

# 3. Reset Password (replace token with actual reset_token from step 2)
curl -X POST http://your-domain.com/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{"reset_token": "your-reset-token", "password": "newPassword123", "password_confirmation": "newPassword123"}'
```

## Email Configuration

Make sure your Laravel `.env` file has proper email configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

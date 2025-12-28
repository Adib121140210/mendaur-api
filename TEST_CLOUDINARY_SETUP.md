# 🧪 Test Script - Verify Cloudinary Setup

## PowerShell Test Commands

### 1. Test Debug Endpoint
```powershell
# Should return cloudinary_configured: true
Invoke-RestMethod "https://mendaur.up.railway.app/api/debug/cloudinary-config" | ConvertTo-Json
```

**Expected:**
```json
{
    "status": "ok",
    "cloudinary_configured": true,
    "cloud_name": "dqk***",
    "api_key_set": true,
    "api_secret_set": true,
    "upload_folder": "mendaur"
}
```

---

### 2. Test Login Response
```powershell
$body = '{"email":"demo@mendaur.id","password":"demo123"}'
$login = Invoke-RestMethod "https://mendaur.up.railway.app/api/login" -Method Post -Body $body -ContentType "application/json"

# Show all user properties
$login.data.user | Format-List *
```

**Expected Output:**
```
user_id     : 7
nama        : Demo Nasabah
email       : demo@mendaur.id
no_hp       : 081200000007
foto_profil : null              ← THIS FIELD MUST EXIST!
actual_poin : 148
level       : bronze
role_id     : 1
role        : nasabah
permissions : 17
```

**✅ Success Indicator:** Field `foto_profil` ada (value `null` atau URL Cloudinary)

---

### 3. Test Upload Foto
```powershell
# Login first
$body = '{"email":"demo@mendaur.id","password":"demo123"}'
$login = Invoke-RestMethod "https://mendaur.up.railway.app/api/login" -Method Post -Body $body -ContentType "application/json"

$token = $login.data.token
$userId = $login.data.user.user_id

# Upload foto (adjust path to your image file)
$headers = @{ "Authorization" = "Bearer $token" }
$form = @{ foto_profil = Get-Item "C:\path\to\photo.jpg" }

Invoke-RestMethod "https://mendaur.up.railway.app/api/users/$userId/update-photo" `
    -Method Post -Headers $headers -Form $form
```

**Expected Response:**
```json
{
    "status": "success",
    "message": "Photo updated successfully",
    "data": {
        "user_id": 7,
        "nama": "Demo Nasabah",
        "foto_profil": "https://res.cloudinary.com/dqk8er1qp/image/upload/v1735369200/mendaur/profiles/abc123.jpg",
        "actual_poin": 148
    }
}
```

---

### 4. Test Login Again After Upload
```powershell
# Login lagi - foto_profil harus berisi Cloudinary URL
$body = '{"email":"demo@mendaur.id","password":"demo123"}'
$login = Invoke-RestMethod "https://mendaur.up.railway.app/api/login" -Method Post -Body $body -ContentType "application/json"

$login.data.user.foto_profil
# Should output: https://res.cloudinary.com/dqk8er1qp/image/upload/...
```

---

## 🔧 Troubleshooting

### Issue: `foto_profil` still missing after setup
**Check:**
1. Railway deployment finished? (check Railway dashboard logs)
2. Variables saved correctly? (no typos)
3. Try hard refresh browser (Ctrl+Shift+R)

### Issue: Upload returns 500 error
**Check:**
1. Cloudinary API secret correct?
2. Check Railway logs: `railway logs -f`
3. Image size < 2MB?

### Issue: Frontend still shows 🌱 emoji
**Root Cause:** Frontend fallback for missing foto_profil
**Solution:** After Railway env setup, frontend will receive proper `foto_profil` field (null or URL)

---

## 📋 Quick Checklist

Before testing frontend:
- [ ] Railway environment variables added (all 4)
- [ ] Railway deployment completed (green status)
- [ ] `/api/debug/cloudinary-config` returns `cloudinary_configured: true`
- [ ] Login response includes `foto_profil` field
- [ ] Upload foto test successful
- [ ] Foto muncul di Cloudinary dashboard (https://cloudinary.com/console)

---

**Status:** Waiting for Railway environment variables setup  
**ETA:** 5 minutes (setup + redeploy)  
**Priority:** HIGH - Blocking frontend foto profil feature

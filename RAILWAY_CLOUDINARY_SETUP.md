# 🚂 Railway Cloudinary Setup Guide

**Date:** December 28, 2025  
**Status:** ⚠️ REQUIRED - Environment Variables Missing

---

## 🔴 Masalah

Login response tidak mengembalikan `foto_profil` field karena:
1. ❌ Cloudinary environment variables belum diset di Railway
2. ❌ Tanpa env vars, `getPhotoUrl()` method tidak jalan dengan banar

---

## ✅ Solusi: Set Environment Variables di Railway

### Step 1: Login ke Railway Dashboard
1. Buka https://railway.app/
2. Login dengan akun Anda
3. Pilih project **mendaur-api**

### Step 2: Tambahkan Environment Variables
1. Klik tab **"Variables"** di project mendaur-api
2. Klik tombol **"+ New Variable"**
3. Tambahkan 4 variables berikut:

```env
CLOUDINARY_CLOUD_NAME=dqk8er1qp
CLOUDINARY_API_KEY=724816866574254
CLOUDINARY_API_SECRET=t7wflXyMnaZpSIyaGGT3gJXfOiE
CLOUDINARY_UPLOAD_FOLDER=mendaur
```

### Step 3: Deploy Ulang
Setelah menambahkan variables:
1. Railway akan **otomatis redeploy**
2. Atau klik tombol **"Redeploy"** manual
3. Tunggu build selesai (2-3 menit)

---

## 🧪 Verifikasi Setup

### 1. Test Debug Endpoint
```bash
curl https://mendaur.up.railway.app/api/debug/cloudinary-config
```

**Expected Response:**
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

### 2. Test Login Response
```bash
curl -X POST https://mendaur.up.railway.app/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@mendaur.id","password":"demo123"}'
```

**Expected Response (harus ada `foto_profil`):**
```json
{
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "user": {
            "user_id": 7,
            "nama": "Demo Nasabah",
            "email": "demo@mendaur.id",
            "no_hp": "081200000007",
            "foto_profil": null,  // ← FIELD INI HARUS ADA (null jika belum upload)
            "actual_poin": 148,
            "level": "bronze",
            "role_id": 1,
            "role": "nasabah",
            "permissions": 17
        },
        "token": "..."
    }
}
```

---

## 📸 Cloudinary Account Info

**Dashboard:** https://cloudinary.com/console  
**Cloud Name:** dqk8er1qp  
**API Key:** 724816866574254  
**API Secret:** t7wflXyMnaZpSIyaGGT3gJXfOiE  

**Storage Limit:** 25 GB (free tier)  
**Bandwidth:** 25 GB/month (free tier)

---

## 📁 Upload Folder Structure

Setelah setup, foto akan disimpan di:

```
Cloudinary Media Library
└── mendaur/
    ├── profiles/          # Foto profil user
    │   └── abc123_1234567890.jpg
    ├── waste/            # Foto sampah (tabung_sampah)
    │   └── def456_1234567891.jpg
    └── products/         # Foto produk
        └── ghi789_1234567892.jpg
```

---

## 🔧 Troubleshooting

### Issue 1: `foto_profil` masih tidak muncul setelah setup
**Solusi:**
1. Clear browser cache
2. Logout dan login ulang
3. Cek Railway logs: `railway logs`

### Issue 2: Upload foto gagal 500 error
**Check:**
1. Environment variables sudah benar?
2. Cloudinary API secret tidak salah ketik?
3. Cek Railway logs untuk error message

### Issue 3: Foto tidak muncul di Cloudinary dashboard
**Solusi:**
1. Test upload dengan endpoint `/api/users/{id}/update-photo`
2. Cek response - harus ada `secure_url` dari Cloudinary
3. Login ke Cloudinary dashboard → Media Library
4. Cari folder `mendaur/profiles`

---

## 🚀 Testing Upload Foto (After Setup)

### PowerShell Test
```powershell
# 1. Login
$body = '{"email":"demo@mendaur.id","password":"demo123"}'
$login = Invoke-RestMethod "https://mendaur.up.railway.app/api/login" `
    -Method Post -Body $body -ContentType "application/json"

# 2. Upload foto
$token = $login.data.token
$userId = $login.data.user.user_id

$headers = @{
    "Authorization" = "Bearer $token"
}

# Pastikan file foto.jpg ada
Invoke-RestMethod "https://mendaur.up.railway.app/api/users/$userId/update-photo" `
    -Method Post -Headers $headers -Form @{
        foto_profil = Get-Item "C:\path\to\foto.jpg"
    }
```

### Expected Upload Response
```json
{
    "status": "success",
    "message": "Photo updated successfully",
    "data": {
        "user_id": 7,
        "nama": "Demo Nasabah",
        "email": "demo@mendaur.id",
        "foto_profil": "https://res.cloudinary.com/dqk8er1qp/image/upload/v1735369200/mendaur/profiles/abc123_1735369200.jpg",
        "actual_poin": 148,
        "level": "bronze"
    }
}
```

---

## ✅ Checklist Setup

Gunakan checklist ini untuk memastikan setup lengkap:

- [ ] Login ke Railway Dashboard
- [ ] Buka project **mendaur-api**
- [ ] Klik tab **Variables**
- [ ] Tambahkan `CLOUDINARY_CLOUD_NAME=dqk8er1qp`
- [ ] Tambahkan `CLOUDINARY_API_KEY=724816866574254`
- [ ] Tambahkan `CLOUDINARY_API_SECRET=t7wflXyMnaZpSIyaGGT3gJXfOiE`
- [ ] Tambahkan `CLOUDINARY_UPLOAD_FOLDER=mendaur`
- [ ] Tunggu auto-redeploy selesai (atau klik Redeploy)
- [ ] Test `/api/debug/cloudinary-config` - harus return `cloudinary_configured: true`
- [ ] Test login - response harus ada field `foto_profil`
- [ ] Test upload foto
- [ ] Verifikasi foto muncul di Cloudinary dashboard

---

## 📞 Next Steps

**Setelah setup Railway environment variables:**

1. ✅ Login response akan include `foto_profil` field (null jika belum upload)
2. ✅ Upload foto akan tersimpan di Cloudinary
3. ✅ Foto tidak hilang saat Railway restart
4. ✅ URL foto bisa diakses dari frontend

**Frontend sudah siap!** Tinggal tunggu backend Railway dikonfigurasi.

---

**Priority:** 🔴 HIGH - Blocking frontend testing  
**ETA:** 5 minutes (setup variables + redeploy)

# 📸 Foto Profil - Fix Documentation

**Date:** December 28, 2025  
**Status:** ✅ Fixed & Deployed

---

## 🐛 Masalah Yang Ditemukan

Frontend melaporkan:
1. **Foto profil tidak terganti** setelah upload berhasil
2. **Tidak bisa melihat foto** yang diupload di Cloudinary

---

## 🔍 Root Cause Analysis

### Masalah 1: Config Path Salah
```php
// ❌ SALAH - Key tidak ada
config('cloudinary.upload_folder')

// ✅ BENAR - Key yang ada di config/services.php
config('services.cloudinary.upload_folder', 'mendaur')
```

### Masalah 2: `foto_profil_public_id` Tidak Di-fillable
Model User tidak punya `foto_profil_public_id` di `$fillable` array, jadi Cloudinary public_id tidak tersimpan di database.

### Masalah 3: Login Response Tidak Return `foto_profil`
AuthController tidak mengembalikan `foto_profil` pada login response, sehingga frontend tidak mendapat URL foto setelah login.

### Masalah 4: URL Tidak Ter-resolve Dengan Benar
UserResource langsung return `$this->foto_profil` tanpa konversi ke full URL untuk local storage.

---

## ✅ Fixes Applied

### 1. CloudinaryService.php - Fixed Config Path
```php
// BEFORE
$publicId = $folder . '/' . Str::random(20) . '_' . time();
'folder' => config('cloudinary.upload_folder') . '/' . $folder,

// AFTER  
$publicId = Str::random(20) . '_' . time();
$uploadFolder = config('services.cloudinary.upload_folder', 'mendaur') . '/' . $folder;
```

### 2. CloudinaryService.php - Added Logging
```php
\Log::info('Cloudinary upload attempt', [
    'folder' => $uploadFolder,
    'public_id' => $publicId,
    'cloud_name' => config('services.cloudinary.cloud_name'),
]);

\Log::info('Cloudinary upload success', [
    'secure_url' => $result['secure_url'],
    'public_id' => $result['public_id']
]);
```

### 3. User.php - Added `foto_profil_public_id` to $fillable
```php
protected $fillable = [
    // ... existing fields
    'foto_profil',
    'foto_profil_public_id', // ← ADDED
    // ...
];
```

### 4. UserResource.php - Smart URL Resolution
```php
private function getPhotoUrl(): ?string
{
    if (empty($this->foto_profil)) {
        return null;
    }

    // If Cloudinary URL, return as-is
    if (str_starts_with($this->foto_profil, 'http://') || str_starts_with($this->foto_profil, 'https://')) {
        return $this->foto_profil;
    }

    // Local storage - convert to full URL
    return asset('storage/' . $this->foto_profil);
}
```

### 5. AuthController.php - Added `foto_profil` to Login Response
```php
'user' => [
    'user_id' => $user->user_id,
    'nama' => $user->nama,
    'email' => $user->email,
    'no_hp' => $user->no_hp,
    'foto_profil' => $this->getPhotoUrl($user->foto_profil), // ← ADDED
    // ...
],
```

---

## 🧪 Testing Upload Foto

### Endpoint untuk Upload
```
POST /api/users/{id}/update-photo
```

### Headers
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Body
```
foto_profil: [FILE] (image/jpeg, image/png, image/gif - max 2MB)
```

### Example Response (Success)
```json
{
    "status": "success",
    "message": "Photo updated successfully",
    "data": {
        "user_id": 7,
        "nama": "Demo Nasabah",
        "email": "demo@mendaur.id",
        "foto_profil": "https://res.cloudinary.com/dqk8er1qp/image/upload/v1234567890/mendaur/profiles/abc123_1234567890.jpg",
        "actual_poin": 150,
        "level": "bronze"
    }
}
```

### Test dengan cURL
```bash
# Login first
TOKEN=$(curl -s -X POST https://mendaur.up.railway.app/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@mendaur.id","password":"demo123"}' | jq -r '.data.token')

# Upload foto
curl -X POST https://mendaur.up.railway.app/api/users/7/update-photo \
  -H "Authorization: Bearer $TOKEN" \
  -F "foto_profil=@/path/to/photo.jpg"
```

---

## 📍 Di Mana Foto Tersimpan?

### Cloudinary Dashboard
1. Login ke https://cloudinary.com/console
2. Cloud Name: **dqk8er1qp**
3. Navigasi ke **Media Library**
4. Folder: `mendaur/profiles/`

### URL Pattern
```
https://res.cloudinary.com/dqk8er1qp/image/upload/v{version}/mendaur/profiles/{public_id}.{format}
```

### Transformations Applied
- Quality: `auto:eco` (optimize for web)
- Format: `auto` (WebP for modern browsers)

---

## 🔧 Debug Endpoint

```
GET /api/debug/cloudinary-config
```

Response:
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

## ⚠️ Environment Variables Required

Di Railway, pastikan environment variables berikut sudah diset:

```env
CLOUDINARY_CLOUD_NAME=dqk8er1qp
CLOUDINARY_API_KEY=724816866574254
CLOUDINARY_API_SECRET=t7wflXyMnaZpSIyaGGT3gJXfOiE
CLOUDINARY_UPLOAD_FOLDER=mendaur
```

---

## 📋 Checklist Frontend

- [ ] Pastikan upload menggunakan endpoint `/api/users/{id}/update-photo`
- [ ] Gunakan `multipart/form-data` sebagai Content-Type
- [ ] Field name harus `foto_profil` (bukan `avatar` atau `photo`)
- [ ] Setelah upload berhasil, update local state dengan URL dari response
- [ ] Untuk menampilkan foto, gunakan langsung value `foto_profil` dari API (sudah full URL)

---

## 🚀 Commits

1. `be1e1be` - fix: Cloudinary integration - fix config path, add logging
2. `d07d037` - fix: Add foto_profil to login response with proper URL handling

---

## 📞 Testing Steps

1. Login dengan `demo@mendaur.id` / `demo123`
2. Upload foto via `/api/users/7/update-photo`
3. Cek response - `foto_profil` harus berisi Cloudinary URL
4. Refresh halaman / Re-login
5. `foto_profil` di login response harus tetap menampilkan URL Cloudinary
6. Buka URL tersebut di browser - gambar harus tampil

---

**Status:** Menunggu verifikasi dari frontend team setelah Railway deployment complete.

# 📷 Fix: Upload Foto Langsung dari Kamera

## 🐛 Masalah Yang Dilaporkan
Nasabah mengalami masalah saat mengupload foto **langsung dari kamera** smartphone. Namun, jika foto tersebut **disimpan ke galeri terlebih dahulu**, upload berhasil.

## 🔍 Root Cause Analysis

Foto langsung dari kamera sering memiliki karakteristik berbeda:

1. **MIME Type**: Beberapa smartphone mengirim foto dengan MIME type `application/octet-stream` ketika diambil langsung dari kamera, bukan `image/jpeg`
2. **Temporary File Handling**: File temporary dari kamera mungkin tidak terdeteksi dengan benar
3. **File Extension**: Nama file dari kamera kadang tidak memiliki extension yang valid
4. **EXIF Data**: Metadata orientasi bisa mempengaruhi processing

## ✅ Fixes Applied

### 1. TabungSampahController.php - More Lenient File Validation
```php
// Before: Strict Laravel validation
'foto_sampah' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:10240'

// After: Custom validation dengan handling untuk camera photos
$allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/octet-stream'];

// If MIME is octet-stream, validate by extension
if ($mimeType === 'application/octet-stream') {
    $extension = strtolower($file->getClientOriginalExtension());
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
        // Allow upload
    }
}

// Fallback: Use getimagesize() to verify it's a real image
$imageInfo = @getimagesize($file->getRealPath());
```

### 2. UserController.php - Same Fix for Profile Photos
Applied same lenient validation untuk upload foto profil.

### 3. Enhanced Logging
```php
\Log::info('Image upload attempt', [
    'file_type' => $file->getMimeType(),
    'file_extension' => $file->getClientOriginalExtension(),
    'guessed_extension' => $file->guessExtension(),
    'is_valid' => $file->isValid(),
    'error' => $file->getError(),
]);
```

### 4. Better Error Messages
```php
private function getUploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi limit server)',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        // ...
    ];
}
```

## 📁 Files Changed
- `app/Http/Controllers/TabungSampahController.php`
  - `store()` method - custom validation untuk foto sampah
  - `update()` method - custom validation untuk foto sampah
  - Added `getUploadErrorMessage()` helper
  
- `app/Http/Controllers/UserController.php`
  - `updatePhoto()` method - custom validation untuk foto profil
  - Added `getUploadErrorMessage()` helper

## 🧪 Testing Checklist

- [ ] Upload foto langsung dari kamera smartphone
- [ ] Upload foto dari galeri (should still work)
- [ ] Upload foto dengan size ~3-4MB dari kamera
- [ ] Upload foto dengan size < 2MB untuk profil
- [ ] Verify foto tersimpan dengan benar
- [ ] Check logs untuk debugging info

## 📝 Log Messages to Look For

Saat debugging, check logs untuk:

```
"Camera photo detected with octet-stream mime, allowing based on extension"
"File passed getimagesize check despite MIME mismatch"
"Invalid file upload" (jika ada error)
```

## ⚠️ Important Notes

1. **Security**: Validasi tetap dilakukan menggunakan `getimagesize()` untuk memastikan file benar-benar gambar
2. **Size Limits**: 
   - Foto sampah: max 10MB
   - Foto profil: max 2MB
3. **Allowed Formats**: JPG, JPEG, PNG, GIF

## 🚀 Deployment

Setelah push, deploy ke Railway:
```bash
git add .
git commit -m "fix: allow camera photos with octet-stream MIME type"
git push origin master
```

Railway akan auto-deploy dari master branch.

---

**Date Fixed**: 28 Desember 2025  
**Reported By**: Frontend Team / Nasabah  
**Fixed By**: Backend Team

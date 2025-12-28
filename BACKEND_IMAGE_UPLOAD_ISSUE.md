# Backend Issue: Image Upload Failure dari Mobile Camera

## Problem Description
User melaporkan upload foto dari kamera smartphone dengan size **3460.1 KB (3.38 MB)** selalu gagal/failed, padahal masih di bawah limit 5MB yang ditetapkan frontend.

## Expected Behavior
- User seharusnya bisa upload foto sampah dari kamera smartphone
- File size 3.4 MB masih dalam batas wajar untuk foto kamera modern
- Upload harus berhasil dalam waktu reasonable (< 30 detik)

## Current Behavior
- Upload selalu failed untuk foto dari kamera smartphone
- Tidak ada error message yang jelas dari backend
- Kemungkinan timeout atau rejected oleh server

## Technical Details

### Endpoint yang bermasalah:
```
POST /api/tabung-sampah
```

### Request Format:
```
Content-Type: multipart/form-data
Authorization: Bearer {token}

Fields:
- user_id: integer
- jadwal_penyetoran_id: integer
- nama_lengkap: string
- no_hp: string
- titik_lokasi: string
- jenis_sampah: string
- foto_sampah: file (image/jpeg, ~3.4MB)
```

## Suspected Issues

### 1. PHP Upload Limits (Most Likely)
Laravel/PHP mungkin punya limit default yang lebih kecil dari yang diharapkan:

**Check & Update php.ini atau .env:**
```ini
upload_max_filesize = 10M     # Currently might be 2M
post_max_size = 12M            # Currently might be 8M
max_execution_time = 60        # Currently might be 30
max_input_time = 60            # Currently might be 30
memory_limit = 256M            # Should be enough
```

### 2. Nginx/Web Server Limits
Jika mengpakai Nginx, check `client_max_body_size`:
```nginx
client_max_body_size 10M;
```

### 3. Railway Platform Limits
Check apakah Railway punya limit untuk file upload size atau timeout

### 4. Request Timeout
Connection might be timing out sebelum upload selesai. Check:
- Database connection timeout
- HTTP client timeout
- Railway request timeout

## Frontend Changes (Already Implemented)
Untuk mengurangi beban server, frontend sudah diupdate dengan:

✅ **Auto Image Compression**
- Foto > 1MB otomatis dikompres
- Resize max 1920px dengan quality 80%
- Foto 3.4MB → ~600-800KB setelah compress

✅ **Upload Timeout Protection**
- Timeout dinaikkan ke 30 detik
- Better error handling untuk HTTP 413 (Payload Too Large)
- Abort controller untuk graceful timeout

✅ **Better Error Messages**
- Log original dan compressed file size
- Specific error untuk timeout dan file size issues

## Recommended Backend Fixes

### Priority 1: Update PHP Upload Limits
```php
// config/upload.php atau .env
'upload_max_filesize' => env('UPLOAD_MAX_FILESIZE', '10M'),
'post_max_size' => env('UPLOAD_MAX_SIZE', '12M'),
'max_execution_time' => env('MAX_EXECUTION_TIME', 60),
```

### Priority 2: Add Validation Response
```php
// In TabungSampahController@store
$request->validate([
    'foto_sampah' => 'required|image|max:10240', // 10MB in KB
    // ... other validations
]);

// Better error response
if ($request->file('foto_sampah')->getSize() > 10485760) { // 10MB
    return response()->json([
        'status' => 'error',
        'message' => 'Ukuran foto terlalu besar. Maksimal 10MB',
        'errors' => ['foto_sampah' => ['File size exceeds 10MB limit']]
    ], 413);
}
```

### Priority 3: Optimize Storage
```php
// Compress image sebelum save (optional, karena frontend sudah compress)
use Intervention\Image\Facades\Image;

$image = Image::make($request->file('foto_sampah'))
    ->resize(1920, 1920, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    })
    ->encode('jpg', 80);

Storage::disk('public')->put($path, (string) $image);
```

### Priority 4: Add Logging
```php
Log::info('Image upload attempt', [
    'user_id' => $request->user_id,
    'file_size' => $request->file('foto_sampah')->getSize(),
    'file_type' => $request->file('foto_sampah')->getMimeType(),
    'original_name' => $request->file('foto_sampah')->getClientOriginalName(),
]);
```

## Testing Checklist
- [ ] Verify PHP upload limits di server
- [ ] Test upload dengan file 3-4MB dari mobile device
- [ ] Test upload dengan file 1-2MB (should work fine)
- [ ] Test upload dengan file > 10MB (should reject with proper error)
- [ ] Check error logs untuk detail error yang terjadi
- [ ] Verify stored images tetap berkualitas baik

## Additional Notes
- Frontend sudah handle compression, jadi backend bisa relax limitnya sedikit
- Recommended backend limit: **10MB** (safety buffer)
- Frontend akan compress foto besar ke ~600-800KB sebelum upload
- Error messages harus informatif untuk debugging

## Contact
Frontend sudah updated dengan commit: `6e81ee5`
- Auto image compression implemented
- Upload timeout increased to 30s
- Better error handling added

---
**Status:** 🔴 Waiting for Backend Fix
**Priority:** High (User-facing issue)
**Impact:** Users tidak bisa upload foto dari kamera smartphone

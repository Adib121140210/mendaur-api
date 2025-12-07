# 🔧 CORS Fix - Step by Step Guide

## ✅ What I Fixed:

### 1. **Updated `bootstrap/app.php`**
Added CSRF exception for API routes to prevent redirects:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
    
    // Disable CSRF protection for API routes
    $middleware->validateCsrfTokens(except: [
        'api/*',
    ]);
})
```

### 2. **CORS Config Already Exists**
Your `config/cors.php` is properly configured:
- ✅ Allows all origins (`*`)
- ✅ Allows all methods
- ✅ Allows all headers
- ✅ Applies to `api/*` paths

### 3. **Cleared All Caches**
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 4. **Restarted Server**
```bash
php artisan serve
```

---

## 🧪 Testing CORS

### Option 1: Open Test File
Open in browser: `http://127.0.0.1:8000/test-cors.html`

Click "Run Test 1" to verify CORS is working.

### Option 2: Browser Console Test
Open browser console (F12) and run:

```javascript
fetch('http://127.0.0.1:8000/api/jadwal-penyetoran', {
    method: 'GET',
    headers: { 'Accept': 'application/json' }
})
.then(r => r.json())
.then(d => console.log('✅ CORS Working:', d))
.catch(e => console.error('❌ Error:', e));
```

### Option 3: Test POST with FormData
```javascript
const formData = new FormData();
formData.append('user_id', '1');
formData.append('jadwal_id', '1');
formData.append('nama_lengkap', 'Test');
formData.append('no_hp', '08123456789');
formData.append('titik_lokasi', 'Test Location');
formData.append('jenis_sampah', 'Plastik');

fetch('http://127.0.0.1:8000/api/tabung-sampah', {
    method: 'POST',
    headers: { 'Accept': 'application/json' },
    body: formData
})
.then(r => r.json())
.then(d => console.log('✅ POST Success:', d))
.catch(e => console.error('❌ Error:', e));
```

---

## 📝 Updated React Code

Use this in your React component:

```javascript
const handleSubmit = async (e) => {
  e.preventDefault();
  
  const validation = validate();
  if (Object.keys(validation).length > 0) {
    setErrors(validation);
    return;
  }

  setLoading(true);
  
  const data = new FormData();
  data.append("user_id", userId || 1); // Make sure userId is valid
  data.append("jadwal_id", formData.jadwalId);
  data.append("nama_lengkap", formData.nama);
  data.append("no_hp", formData.noHp);
  data.append("titik_lokasi", formData.lokasi);
  data.append("jenis_sampah", formData.jenis);
  
  if (formData.foto) {
    data.append("foto_sampah", formData.foto);
  }

  try {
    const res = await fetch("http://127.0.0.1:8000/api/tabung-sampah", {
      method: "POST",
      headers: {
        'Accept': 'application/json',
        // DO NOT set Content-Type for FormData
      },
      body: data,
    });

    const result = await res.json();

    if (!res.ok) {
      if (result.errors) {
        const backendErrors = {};
        Object.keys(result.errors).forEach(key => {
          backendErrors[key] = result.errors[key][0];
        });
        setErrors(backendErrors);
        throw new Error(result.message || "Validasi gagal");
      }
      throw new Error(result.message || "Terjadi kesalahan");
    }

    console.log("Respons backend:", result);

    if (result.status === "success") {
      alert(result.message || "Setor sampah berhasil!");
      setFormData({
        nama: "",
        noHp: "",
        lokasi: "",
        jenis: "",
        foto: null,
        jadwalId: "",
      });
      onClose();
    }
  } catch (err) {
    console.error("Error submit:", err);
    alert(err.message || "Gagal mengirim data");
  } finally {
    setLoading(false);
  }
};
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: "Failed to fetch"
**Cause:** Server not running or wrong URL
**Solution:**
```bash
# Make sure server is running
php artisan serve

# Check it responds at:
# http://127.0.0.1:8000/api/jadwal-penyetoran
```

### Issue 2: "No 'Access-Control-Allow-Origin' header"
**Cause:** CORS not configured or cached config
**Solution:**
```bash
php artisan config:clear
php artisan serve
```

### Issue 3: Redirect to localhost:5173
**Cause:** CSRF protection redirecting unauthenticated requests
**Solution:** Already fixed in `bootstrap/app.php` - API routes now exempt from CSRF

### Issue 4: 422 Validation Error
**Cause:** Missing required fields or invalid data
**Solution:** Check backend response for validation errors:
```javascript
if (result.errors) {
  console.log('Validation errors:', result.errors);
}
```

---

## 🎯 What Should Work Now:

1. ✅ React app can fetch from `http://127.0.0.1:8000/api/*`
2. ✅ No CORS errors
3. ✅ No CSRF redirects
4. ✅ File uploads work
5. ✅ Validation errors are returned properly

---

## 🚀 Next Steps:

1. **Open test page:** `http://127.0.0.1:8000/test-cors.html`
2. **Run all 3 tests** - they should all pass or show validation errors
3. **Try your React form** - it should work now!

---

## 📞 Still Having Issues?

If you still see CORS errors:

1. **Check Network tab** in browser DevTools (F12)
2. **Look at the request headers** - should include Origin
3. **Look at the response headers** - should include Access-Control-Allow-Origin
4. **Check if redirect is happening** - status should be 200 or 422, NOT 302

Share the Network tab screenshot if issues persist!

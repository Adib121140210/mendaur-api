# 🔧 CORS FIX - Local Development Setup

**Problem Found**: CORS blocked frontend from connecting to backend  
**Solution**: Updated CORS config to allow localhost  
**Status**: ✅ FIXED

---

## 📋 What Was Changed

### Before (❌ BLOCKED localhost):
```php
'allowed_origins' => ['https://mendaur.up.railway.app'],
'supports_credentials' => false,
```

### After (✅ ALLOWS localhost):
```php
'allowed_origins' => [
    'https://mendaur.up.railway.app',  // Production
    'http://localhost:5173',            // Local Vite dev
    'http://127.0.0.1:5173',            // Local IP Vite dev
    'http://localhost:3000',            // Alternative local
    'http://127.0.0.1:3000',            // Alternative local IP
],
'supports_credentials' => true,
```

---

## 🚀 How to Test

### Step 1: Clear any running services
```bash
# Kill existing Laravel dev server (if running)
# Kill existing Vite dev server (if running)
```

### Step 2: Start Backend
```bash
cd C:\Users\Adib\Desktop\mendaur-api2
php artisan serve
```

Output should show:
```
Laravel development server started on [http://127.0.0.1:8000]
```

### Step 3: Start Frontend
```bash
cd C:\Users\Adib\mendaur-TA
npm run dev
# or
pnpm dev
```

Output should show:
```
Local: http://localhost:5173
Press q + enter to stop
```

### Step 4: Test Login
1. Open browser: `http://localhost:5173`
2. Use credentials:
   ```
   Email: admin@mendaur.test
   Password: password
   ```
3. Check browser console for success message ✅

---

## 🔍 Verify CORS is Fixed

### In Browser Console (F12):
```javascript
// Should show success, not CORS error
// If working:
fetch('http://127.0.0.1:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@mendaur.test',
    password: 'password'
  })
})
.then(r => r.json())
.then(d => console.log('✅ Success:', d))
.catch(e => console.error('❌ Error:', e))
```

---

## ⚙️ Config File Details

**File Modified**: `config/cors.php`

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'https://mendaur.up.railway.app',  // Production URL
        'http://localhost:5173',            // Vite default
        'http://127.0.0.1:5173',            // Vite IP
        'http://localhost:3000',            // React/Next.js default
        'http://127.0.0.1:3000',            // React/Next.js IP
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],  // Allow all headers
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,  // Allow cookies/auth
];
```

---

## 🎯 How CORS Works

1. **Browser sends preflight request** (OPTIONS) to backend
2. **Backend checks CORS config** to see if origin is allowed
3. **If allowed**: Backend sends `Access-Control-Allow-Origin` header
4. **Browser receives header** and allows the actual request
5. **Frontend gets response** from API ✅

---

## ✅ All Allowed Origins

| Origin | Use Case | Status |
|--------|----------|--------|
| `https://mendaur.up.railway.app` | Production | ✅ |
| `http://localhost:5173` | Local Vite | ✅ |
| `http://127.0.0.1:5173` | Local Vite (IP) | ✅ |
| `http://localhost:3000` | Alt React | ✅ |
| `http://127.0.0.1:3000` | Alt React (IP) | ✅ |

---

## 🐛 If Still Getting CORS Error

### Checklist:
- [ ] Backend running on `http://127.0.0.1:8000`
- [ ] Frontend running on one of allowed ports (5173, 3000, etc)
- [ ] No cache in browser (open DevTools, reload)
- [ ] Check config/cors.php has correct origins
- [ ] Restart backend server after config change
- [ ] Check Laravel logs: `storage/logs/laravel.log`

### Check Laravel Logs:
```bash
cd C:\Users\Adib\Desktop\mendaur-api2
tail -f storage/logs/laravel.log
```

### Clear Laravel Cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📝 Important Notes

### credentials: true
```javascript
// This is now enabled in CORS config
// Allows sending cookies and authorization headers
```

### For Production
Make sure to:
1. Add your production frontend URL to `allowed_origins`
2. Set appropriate `max_age` for browser cache
3. Only allow necessary headers

---

## 🔐 Security Considerations

✅ **Current Config**:
- Specific origins whitelist (not wildcard *)
- Credentials enabled for auth tokens
- All methods allowed (GET, POST, PUT, DELETE, PATCH)
- All headers allowed

⚠️ **For Production**:
- Add only your production domains
- Consider limiting methods if needed
- Consider limiting headers if needed
- Review against security requirements

---

## 🎉 You're All Set!

CORS is now configured correctly for local development.

**Next Steps**:
1. Make sure both backend and frontend are running
2. Try logging in from frontend
3. Test admin API endpoints
4. Create admin components (AdminWaste, AdminUsers, etc)

---

**Updated**: 23 December 2025  
**Backend**: Laravel 10 + Sanctum  
**Frontend**: React/Vue Vite  
**Status**: ✅ CORS FIXED

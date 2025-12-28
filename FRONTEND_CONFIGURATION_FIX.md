# Frontend Configuration Fix Guide

## 🚨 **Current Issue**
The frontend di Railway sedang mencoba mengakses localhost (127.0.0.1:8000) instead of Railway backend (https://mendaur.up.railway.app).

## 🔧 **Solution Required**

### 1. **Update API Base URL di Frontend Repository**

Di frontend project, cari file konfigurasi API (biasanya):
- `src/config/api.js`
- `src/utils/api.js` 
- `src/services/api.js`
- `.env` file
- `vite.config.js` atau `next.config.js`

**Update dari:**
```javascript
const API_BASE_URL = 'http://127.0.0.1:8000/api'
// atau
const API_BASE_URL = 'http://localhost:8000/api'
```

**Menjadi:**
```javascript
const API_BASE_URL = 'https://mendaur.up.railway.app/api'
```

### 2. **Environment Variables (Recommended)**

Dalam `.env` file di frontend:
```env
VITE_API_URL=https://mendaur.up.railway.app/api
# atau jika menggunakan Next.js:
NEXT_PUBLIC_API_URL=https://mendaur.up.railway.app/api
```

Dan di kode:
```javascript
const API_BASE_URL = process.env.VITE_API_URL || process.env.NEXT_PUBLIC_API_URL
```

### 3. **Railway Environment Variables**

Di Railway dashboard frontend project, tambahkan environment variable:
```
API_URL=https://mendaur.up.railway.app/api
```

### 4. **Fix Image URLs**

Update image references dari:
```
http://127.0.0.1:8000/storage/filename
```

Menjadi:
```
https://res.cloudinary.com/dqk8er1qp/image/upload/...
```

## 🎯 **Files to Check in Frontend**

1. **API Configuration Files**
2. **Axios/Fetch Base URL Setup** 
3. **Environment Variables**
4. **Build Configuration**
5. **Image Components**

## ✅ **After Fix, Expected Behavior**

- ✅ API calls ke https://mendaur.up.railway.app
- ✅ Images dari Cloudinary CDN  
- ✅ No mixed content errors
- ✅ Faster loading dengan CDN

## 📞 **Current Status**

- **Backend**: ✅ Online dan working
- **Login**: ✅ Working (status 200)
- **Database**: ✅ Connected dengan 7 test users
- **Cloudinary**: ✅ Integrated dan ready
- **Frontend**: ❌ Wrong API URLs

## 🔨 **Immediate Action**

Frontend perlu rebuild dan redeploy setelah API URL configuration diupdate.

# ✅ COMPLETE SETUP CHECKLIST - Backend & Frontend Integration

**Date**: 23 December 2025  
**Status**: 🚀 READY FOR TESTING

---

## ✅ BACKEND SETUP (Sudah Selesai)

### Database
- [x] All migrations running: `php artisan migrate:fresh`
- [x] All seeders working: `php artisan migrate:fresh --seed`
- [x] Test data generated: ~400+ records
- [x] Test accounts created:
  - Email: `admin@mendaur.test`, Password: `password`, Role: `admin`
  - Email: `superadmin@mendaur.test`, Password: `password`, Role: `superadmin`

### API Endpoints
- [x] Authentication: `/api/login`, `/api/logout`
- [x] Admin endpoints: 50+ endpoints ready
- [x] All CRUD operations: Create, Read, Update, Delete
- [x] Role-based access control: Working

### CORS Configuration
- [x] Fixed CORS to allow localhost
- [x] Added origins:
  - `http://localhost:5173` (Vite)
  - `http://127.0.0.1:5173` (Vite IP)
  - `http://localhost:3000` (React)
  - `http://127.0.0.1:3000` (React IP)
  - `https://mendaur.up.railway.app` (Production)
- [x] `supports_credentials` set to `true`

### API Service Layer
- [x] `adminApi.js` created with 90+ endpoints
- [x] All endpoints verified with backend
- [x] 15 fixes applied (path /admin → /superadmin)
- [x] Documentation generated

### Verification
- [x] All endpoints respond correctly
- [x] Authentication working
- [x] Database seeding successful
- [x] CORS headers being sent

---

## 🚀 FRONTEND SETUP (Current)

### Prerequisites
- [x] React/Vue project exists at `C:\Users\Adib\mendaur-TA`
- [x] Node.js and npm/pnpm installed
- [x] Project dependencies installed

### Integration Steps

#### Step 1: Copy Backend API Service
- [ ] Copy `adminApi_FIXED.js` to `src/api/adminApi.js`
- [ ] Verify import paths in components
- [ ] Test one endpoint to confirm connection

#### Step 2: Environment Setup
- [ ] Create `.env.local` file in frontend with:
  ```
  VITE_API_URL=http://127.0.0.1:8000/api
  ```
- [ ] Verify Vite running on port 5173
- [ ] Verify Backend running on port 8000

#### Step 3: Login Component
- [ ] Verify login form exists
- [ ] Check credentials: admin@mendaur.test / password
- [ ] Verify token stored in localStorage
- [ ] Check Network tab for successful request

#### Step 4: Admin Components
- [ ] Create `AdminWaste.vue` (waste deposit management)
- [ ] Create `AdminUsers.vue` (user management)
- [ ] Create `AdminProducts.vue` (product management)
- [ ] Create `AdminBadges.vue` (badge management)

---

## 🔧 COMPLETE SETUP INSTRUCTIONS

### Terminal 1: Start Backend
```bash
cd C:\Users\Adib\Desktop\mendaur-api2

# First time setup
composer install
php artisan migrate:fresh --seed
php artisan key:generate

# Start server
php artisan serve
```

Expected output:
```
Laravel development server started on [http://127.0.0.1:8000]
Press Ctrl+C to quit
```

### Terminal 2: Start Frontend
```bash
cd C:\Users\Adib\mendaur-TA

# First time setup
npm install
# or
pnpm install

# Copy admin API
cp ../mendaur-api2/adminApi_FIXED.js src/api/adminApi.js

# Start dev server
npm run dev
# or
pnpm dev
```

Expected output:
```
Local:   http://localhost:5173/
Press q + enter to stop
```

### Terminal 3: Monitor Backend Logs (Optional)
```bash
cd C:\Users\Adib\Desktop\mendaur-api2
tail -f storage/logs/laravel.log
```

---

## 🧪 TESTING CHECKLIST

### Login Test
- [ ] Navigate to `http://localhost:5173`
- [ ] Enter credentials: `admin@mendaur.test` / `password`
- [ ] Click Login
- [ ] Check: No CORS errors in console
- [ ] Check: Token in localStorage: `console.log(localStorage.getItem('token'))`
- [ ] Check: Redirected to dashboard

### API Connection Test
```javascript
// In browser console
adminApi.getAllUsers().then(res => console.log('✅ Success:', res))
// Should show users list, not error
```

### Admin Endpoints Test
- [ ] Get all users: `adminApi.getAllUsers()`
- [ ] Get waste deposits: `adminApi.listWasteDeposits()`
- [ ] Get products: `adminApi.getAllProducts()`
- [ ] Get badges: `adminApi.getAllBadges()`

### Database State Test
```bash
# Check how much data is available
php artisan tinker
>>> App\Models\User::count()
>>> App\Models\TabungSampah::count()
>>> App\Models\Produk::count()
>>> App\Models\Badge::count()
```

---

## 📊 EXPECTED DATA AVAILABLE

After seeding, you should have:

| Table | Count | Purpose |
|-------|-------|---------|
| Users | 20+ | Test accounts |
| TabungSampah | 56 | Waste deposits |
| Produk | 10+ | Products for redemption |
| Badge | 5+ | Badges for achievements |
| PenukaranProduk | 30+ | Product exchanges |
| PenarikanTunai | 30+ | Cash withdrawals |
| Notifikasi | 89 | Notifications |
| JadwalPenyetoran | 10+ | Waste deposit schedules |

---

## 🐛 TROUBLESHOOTING

### CORS Error
```
Access to fetch at 'http://127.0.0.1:8000/api/login' 
from origin 'http://localhost:5173' has been blocked by CORS
```

**Fix**:
1. Verify `config/cors.php` has localhost entries
2. Restart backend: `php artisan serve`
3. Clear browser cache
4. Try different port if 5173 not available

### 404 Not Found
```
GET http://127.0.0.1:8000/api/admin/users → 404
```

**Fix**:
1. Check endpoint exists: `php artisan route:list | grep users`
2. Check auth token valid
3. Verify user has admin role

### 500 Server Error
```
GET http://127.0.0.1:8000/api/admin/users → 500
```

**Fix**:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check database connection: `php artisan tinker`
3. Run migrations: `php artisan migrate`

### Connection Refused
```
Failed to fetch: net::ERR_FAILED
```

**Fix**:
1. Is backend running? `php artisan serve`
2. Is it running on 8000? `netstat -an | grep 8000`
3. Try direct URL: `curl http://127.0.0.1:8000`

---

## 📋 FILE REFERENCES

### Backend Files (Already Updated)
- ✅ `config/cors.php` - CORS config (FIXED)
- ✅ `bootstrap/app.php` - Middleware setup
- ✅ `routes/api.php` - All API endpoints
- ✅ `database/seeders/` - Test data seeders

### Frontend Files (Need to Update)
- [ ] `src/api/adminApi.js` - Copy from `adminApi_FIXED.js`
- [ ] `.env.local` - API URL configuration
- [ ] `src/pages/Login.jsx` - Login component
- [ ] `src/pages/Dashboard.jsx` - Dashboard after login
- [ ] `src/components/AdminWaste.vue` - Waste management
- [ ] `src/components/AdminUsers.vue` - User management
- [ ] `src/components/AdminProducts.vue` - Product management
- [ ] `src/components/AdminBadges.vue` - Badge management

### Documentation Files (Backend)
- ✅ `ADMINAPI_VERIFICATION_REPORT.md` - Audit results
- ✅ `adminApi_FIXED.js` - Fixed API service
- ✅ `CORS_FIX_GUIDE.md` - CORS setup guide
- ✅ `FRONTEND_LOGIN_SETUP_GUIDE.md` - Login setup
- ✅ `DELIVERY_PACKAGE_UNTUK_FRONTEND.md` - Delivery package

---

## 🎯 SUCCESS CRITERIA

### When Setup is Complete ✅
- [ ] Backend running on `http://127.0.0.1:8000`
- [ ] Frontend running on `http://localhost:5173`
- [ ] Login page loads without errors
- [ ] Can login with test credentials
- [ ] Token stored in localStorage
- [ ] Can fetch data from admin endpoints
- [ ] All CRUD operations working
- [ ] No CORS errors in console
- [ ] All 50+ endpoints tested
- [ ] Ready for component development

---

## 🚀 NEXT PHASE

Once setup is complete and tested:

1. **Create Admin Components**
   - AdminWaste.vue - List, approve, reject deposits
   - AdminUsers.vue - List, edit, delete users
   - AdminProducts.vue - CRUD for products
   - AdminBadges.vue - CRUD for badges

2. **Implement Features**
   - Data tables with pagination
   - CRUD forms
   - Status management
   - Filters and search
   - Real-time updates

3. **Testing**
   - Unit tests for components
   - Integration tests with API
   - E2E tests for workflows

4. **Deployment**
   - Build frontend: `npm run build`
   - Deploy to hosting
   - Configure production API URL

---

## 📞 SUPPORT

If you encounter any issues:

1. Check this checklist first
2. Read relevant documentation file
3. Check backend logs: `storage/logs/laravel.log`
4. Check browser console (F12)
5. Verify both servers are running

---

**Status**: 🎉 Backend Ready, Frontend Setup in Progress  
**Estimated Time to Complete**: 30 minutes  
**Next Check**: After login works on frontend

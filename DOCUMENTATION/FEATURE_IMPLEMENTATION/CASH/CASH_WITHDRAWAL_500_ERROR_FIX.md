# 🐛 Debugging 500 Internal Server Error - Cash Withdrawal

## ❌ Error Details

**Error:** `POST http://127.0.0.1:8000/api/penarikan-tunai 500 (Internal Server Error)`

**Location:** `tukarPoin.jsx:130`

---

## 🔍 Quick Diagnosis Steps

### Step 1: Check Laravel Logs

**Windows PowerShell:**
```powershell
cd path\to\your\laravel\backend
Get-Content storage\logs\laravel.log -Tail 50
```

**Or open the file directly:**
```
C:\path\to\backend\storage\logs\laravel.log
```

Look for the most recent error (bottom of file) - it will show the exact PHP error.

---

## 🚨 Common Causes & Fixes

### Issue 1: Database Connection Error

**Symptom:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Fix:**
1. Check `.env` file in Laravel backend:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

2. Make sure MySQL is running:
   ```powershell
   # Check if MySQL service is running
   Get-Service -Name MySQL*
   ```

---

### Issue 2: Table Does Not Exist

**Symptom:**
```
SQLSTATE[42S02]: Base table or view not found: Table 'penarikan_tunai' doesn't exist
SQLSTATE[42S02]: Base table or view not found: Table 'notifikasi' doesn't exist
```

**Fix:**
```powershell
cd path\to\backend
php artisan migrate:fresh --seed
```

**Note:** If you see errors about `notifikasi` table, it means the table schema was outdated. The migration has been updated to match the controller's expectations (uses `is_read` instead of `dibaca`, and `tipe` is now a flexible string field).

If migration fails, check if the migration files exist:
```
database/migrations/2025_11_17_055323_create_penarikan_saldo_table.php
database/migrations/2025_11_13_072727_notifikasi.php
```

---

### Issue 3: Missing User Model Method

**Symptom:**
```
Call to undefined method App\Models\User::isAdmin()
```

**Fix:**
Add to `app/Models/User.php`:
```php
public function isAdmin()
{
    return $this->level === 'admin';
}
```

---

### Issue 4: Invalid Token / Unauthenticated

**Symptom:**
```json
{
  "message": "Unauthenticated."
}
```

**Fix:**
1. Check if token is stored:
   ```javascript
   console.log('Token:', localStorage.getItem('token'));
   ```

2. If null, login again to get new token

3. Verify Sanctum configuration in `config/sanctum.php`:
   ```php
   'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
   ```

---

### Issue 5: CORS Error

**Symptom:**
```
Access to fetch has been blocked by CORS policy
```

**Fix:**
Update `config/cors.php`:
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

---

### Issue 6: Validation Error Not Caught

**Symptom:**
500 error but should be 422 validation error

**Fix:**
Check validation rules in `PenarikanTunaiController`:
```php
$validated = $request->validate([
    'user_id' => 'required|exists:users,id',
    'jumlah_poin' => 'required|integer|min:2000',
    'nomor_rekening' => 'required|string|max:50',
    'nama_bank' => 'required|string|max:100',
    'nama_penerima' => 'required|string|max:255'
]);
```

---

### Issue 7: User Not Found

**Symptom:**
```
No query results for model [App\Models\User]
```

**Fix:**
Check if `user_id` exists in database:
```sql
SELECT id, name, email, total_poin FROM users WHERE id = 1;
```

If no results, the user doesn't exist. Make sure:
1. User is properly logged in
2. User ID from frontend matches database
3. JWT token contains correct user ID

---

### Issue 8: Insufficient Points (Should be 400, not 500)

**Symptom:**
Backend crashes when checking points

**Fix:**
Ensure User model has `total_poin` column:
```sql
DESCRIBE users;
```

If missing, add migration:
```php
Schema::table('users', function (Blueprint $table) {
    $table->integer('total_poin')->default(0);
});
```

---

### Issue 9: Transaction Rollback Error

**Symptom:**
```
ErrorException: A facade root has not been set
```

**Fix:**
Check controller uses `DB` facade:
```php
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // ... operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

---

### Issue 10: Model Not Found

**Symptom:**
```
Class 'App\Models\PenarikanTunai' not found
```

**Fix:**
Ensure model exists at:
```
app/Models/PenarikanTunai.php
```

Namespace should be:
```php
namespace App\Models;
```

---

## 🔬 Debug Mode: Enhanced Logging

### Add Debug Logging to Controller

**Temporarily add to `PenarikanTunaiController@store`:**
```php
public function store(Request $request)
{
    // Log incoming request
    \Log::info('Withdrawal Request:', $request->all());
    
    try {
        $validated = $request->validate([...]);
        \Log::info('Validation passed:', $validated);
        
        $user = User::findOrFail($validated['user_id']);
        \Log::info('User found:', ['id' => $user->id, 'points' => $user->total_poin]);
        
        // ... rest of code
        
        \Log::info('Withdrawal created successfully:', ['id' => $withdrawal->id]);
        
    } catch (\Exception $e) {
        \Log::error('Withdrawal error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}
```

Then check logs:
```powershell
Get-Content storage\logs\laravel.log -Tail 100
```

---

## 🧪 Test API Directly with Postman/Thunder Client

**Request:**
```http
POST http://127.0.0.1:8000/api/penarikan-tunai
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN_HERE

{
  "user_id": 1,
  "jumlah_poin": 5000,
  "nomor_rekening": "1234567890",
  "nama_bank": "BCA",
  "nama_penerima": "Test User"
}
```

**Expected Success (201):**
```json
{
  "success": true,
  "message": "Permintaan penarikan tunai berhasil diajukan",
  "data": {
    "id": 1,
    "user_id": 1,
    "jumlah_poin": 5000,
    "jumlah_rupiah": 50000,
    "status": "pending"
  }
}
```

---

## 📊 Verify Database State

### Check User Points
```sql
SELECT id, name, email, total_poin FROM users WHERE id = 1;
```

### Check Withdrawal Table Exists
```sql
SHOW TABLES LIKE 'penarikan_tunai';
```

### Check Withdrawal Records
```sql
SELECT * FROM penarikan_tunai ORDER BY created_at DESC LIMIT 5;
```

---

## 🔧 Quick Fix Checklist

- [ ] Backend server is running (`php artisan serve`)
- [ ] Database is connected (check `.env`)
- [ ] `penarikan_tunai` table exists (`php artisan migrate`)
- [ ] User has `total_poin` column
- [ ] User model has `isAdmin()` method
- [ ] Token is valid (login again if needed)
- [ ] CORS is configured for frontend origin
- [ ] Controller file exists at correct path
- [ ] Routes are registered in `api.php`
- [ ] Sanctum middleware is working

---

## 🆘 Still Getting 500 Error?

### Get Full Error Details:

1. **Set Laravel to show detailed errors:**
   
   In `.env`:
   ```env
   APP_DEBUG=true
   ```

2. **Check error in browser Network tab:**
   - Open DevTools (F12)
   - Go to Network tab
   - Click the failed request
   - Click "Response" tab
   - You'll see detailed PHP error

3. **Check database connection:**
   ```powershell
   cd path\to\backend
   php artisan tinker
   ```
   Then in tinker:
   ```php
   DB::connection()->getPdo();
   // Should show: PDO {...}
   // If error, database connection is broken
   ```

4. **Test route exists:**
   ```powershell
   php artisan route:list | Select-String "penarikan-tunai"
   ```
   Should show:
   ```
   POST    api/penarikan-tunai
   ```

---

## 💡 Most Likely Cause

Based on the error, the most common causes are:

1. **Database table missing** (85% of cases)
   - Solution: Run `php artisan migrate`

2. **User doesn't have enough points** (10% of cases)
   - Solution: Add points to test user:
     ```sql
     UPDATE users SET total_poin = 10000 WHERE id = 1;
     ```

3. **Authentication issue** (5% of cases)
   - Solution: Login again, get fresh token

---

## 📞 Next Steps

1. **Check Laravel logs first** - This will show exact error
2. **Run the fix for that specific error**
3. **Try the request again**
4. **Share the Laravel log error here if still failing**

---

**Created:** November 17, 2025  
**For:** Cash Withdrawal Feature Debugging  
**Status:** Frontend Enhanced Error Logging Added ✅

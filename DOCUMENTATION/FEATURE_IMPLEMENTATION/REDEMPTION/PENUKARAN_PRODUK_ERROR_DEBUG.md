# 🔍 Penukaran Produk Error Debugging

## Issues Identified

### 1. ❌ GET /api/penukaran-produk Returns 500
**Location**: riwayatTransaksi.jsx:115  
**Status**: 500 Internal Server Error  
**When**: Trying to fetch redemption history

### 2. ❌ POST /api/penukaran-produk Returns 400
**Location**: tukarPoin.jsx:353  
**Status**: 400 Bad Request  
**Message**: "Poin tidak mencukupi untuk penukaran ini" (Insufficient points)  
**Context**: User has 150 poin, trying to redeem item costing 50 poin

---

## 📊 Request Data

### POST Request (Redemption)
```javascript
{
  "produk_id": 1,
  "jumlah_poin": 50,
  "jumlah": 1,
  "alamat_pengiriman": "Ambil di Bank Sampah"
}
```

**User Status:**
- Current Points: 150 poin
- Required: 50 poin
- Should Pass ✓ (150 >= 50)
- But Getting Error ✗

---

## 🎯 Root Causes Analysis

### Issue 1: Insufficient Points Error (Despite Having 150)

**Possible Causes:**

#### A. **Points Column Mismatch**
Backend might be checking a different column:
```php
// Possible issues:
$user->total_poin          // ✓ Correct (likely)
$user->poin_tersedia       // ❌ Available points (might be 0)
$user->poin                // ❌ Different column
```

**Frontend is sending**: 50 poin  
**Backend is checking**: Might be checking $user->poin_tersedia (which could be 0)

#### B. **Points Already Deducted**
Backend might be attempting to deduct points during creation, causing:
```php
// Logic issue:
if ($user->total_poin < $jumlah_poin) {
    // This checks if we can deduct
    // But might be wrong if points already partially used
}
```

#### C. **User Points Cache Not Updated**
Frontend might be showing outdated cached points.

#### D. **Database Consistency Issue**
Multiple columns with point values out of sync:
- total_poin: 150
- poin_tersedia: 50 (Already spent in pending redemptions)
- poin_claimed: 100 (Already claimed)

---

### Issue 2: GET Returns 500 Error

**Possible Causes:**

#### A. **Authentication Failure**
The GET endpoint might not have proper middleware:
```php
// Might be missing this
Route::get('/penukaran-produk', [...])
    ->middleware('auth:sanctum'); // ← If missing, could cause issues
```

#### B. **Database Query Error**
```php
// Might fail if:
$penukaran = PenukaranProduk::where('user_id', auth()->id())
    ->with('produk') // ← If produk relation doesn't exist
    ->get();
```

#### C. **Missing Relationship**
Model relationship might not be defined:
```php
// In PenukaranProduk model
public function produk() {
    return $this->belongsTo(Produk::class); // ← Must be defined
}
```

---

## 🔧 Backend Debugging Steps

### Step 1: Check User Points Structure
```sql
-- Check what point columns exist
DESCRIBE users;

-- Expected columns:
-- id
-- total_poin (main points column)
-- poin_tersedia (optional - available points)
-- poin_pending (optional - pending redemptions)
-- poin_claimed (optional - already claimed)

-- Check user's actual values
SELECT id, total_poin, poin_tersedia FROM users WHERE id = 1;
```

### Step 2: Check Redemption Table
```sql
-- Verify table exists and has data
SELECT * FROM penukaran_produk LIMIT 5;

-- Check structure
DESCRIBE penukaran_produk;
```

### Step 3: Review POST Endpoint Logic
```php
// In PenukaranProdukController@store

public function store(Request $request)
{
    $user = auth()->user();
    
    Log::info('User points check', [
        'user_id' => $user->id,
        'total_poin' => $user->total_poin,
        'requested_poin' => $request->jumlah_poin,
        'comparison' => $user->total_poin >= $request->jumlah_poin,
    ]);
    
    // This is likely where it fails
    if ($user->total_poin < $request->jumlah_poin) {
        Log::warning('Insufficient points', [
            'available' => $user->total_poin,
            'needed' => $request->jumlah_poin,
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Poin tidak mencukupi untuk penukaran ini'
        ], 400);
    }
    
    // ... continue with creation
}
```

### Step 4: Review GET Endpoint Logic
```php
// In PenukaranProdukController@index

public function index()
{
    try {
        $user = auth()->user();
        
        Log::info('Fetching redemptions for user', [
            'user_id' => $user->id,
        ]);
        
        $penukaran = PenukaranProduk::where('user_id', $user->id)
            ->with('produk')
            ->orderBy('created_at', 'desc')
            ->get();
        
        Log::info('Redemptions fetched', [
            'count' => $penukaran->count(),
        ]);
        
        return response()->json([
            'status' => 'success',
            'data' => $penukaran
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching redemptions: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Error fetching redemptions',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}
```

---

## 🧪 Test Cases

### Test 1: Direct User Points Query
```bash
# SSH into server or use Artisan
php artisan tinker

# Check user points
$user = User::find(1);
$user->total_poin;           # Should be 150
$user->poin_tersedia ?? null # Check if exists
$user->poin_pending ?? null  # Check if exists

# Verify arithmetic
$user->total_poin >= 50;     # Should return true
```

### Test 2: Test POST with cURL
```bash
TOKEN="5|NcezJVcB6k0O0S800V..."

curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 1,
    "jumlah_poin": 50,
    "jumlah": 1,
    "alamat_pengiriman": "Ambil di Bank Sampah"
  }'

# Should return 201 Created, not 400 Bad Request
```

### Test 3: Test GET with cURL
```bash
TOKEN="5|NcezJVcB6k0O0S800V..."

curl -X GET http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN"

# Should return 200 with array of redemptions
```

---

## 📋 Likely Solutions

### Solution 1: Fix Points Checking Logic
```php
// WRONG - checks if user has enough points
if ($user->total_poin < $request->jumlah_poin) { ... }

// RIGHT - should be >= not <
if ($user->total_poin < $request->jumlah_poin) { 
    // User doesn't have enough - CORRECT
} else {
    // User has enough - proceed
}
```

### Solution 2: Check Multiple Point Columns
```php
// If there are multiple columns, combine them
$availablePoints = $user->total_poin + ($user->poin_tersedia ?? 0);

if ($availablePoints < $request->jumlah_poin) {
    return response()->json([...], 400);
}
```

### Solution 3: Fix GET Endpoint Query
```php
// Add proper error handling and logging
try {
    $penukaran = PenukaranProduk::where('user_id', auth()->id())
        ->with(['produk']) // Ensure relationship exists
        ->orderBy('created_at', 'desc')
        ->get();
        
    return response()->json([
        'status' => 'success',
        'data' => $penukaran
    ]);
    
} catch (\Exception $e) {
    // Return proper error response
    return response()->json([
        'status' => 'error',
        'message' => $e->getMessage()
    ], 500);
}
```

---

## 🎯 Quick Fixes to Try

### Immediate Action Items:

1. **Check Backend Logs**
```bash
tail -f storage/logs/laravel.log
# Look for error details about the 500 and 400 errors
```

2. **Add Debug Mode**
```bash
# In .env
APP_DEBUG=true

# Will show actual error messages instead of generic 500
```

3. **Test User Points**
```bash
# In tinker
$user = User::find(1);
$user->total_poin; # Should be 150
```

4. **Check if Produk Exists**
```bash
# In tinker
Produk::find(1); # Should exist
```

---

## 📊 Expected vs Actual

### Expected Flow:
```
1. User has 150 poin ✓
2. Redemption requires 50 poin ✓
3. POST /api/penukaran-produk with data ✓
4. Backend validates: 150 >= 50 ✓ TRUE
5. Creates penukaran record ✓
6. Returns 201 Created ✓
7. User sees success message ✓
8. Points deducted to 100 ✓
9. GET /api/penukaran-produk returns list ✓
```

### Actual Flow:
```
1. User has 150 poin ✓
2. Redemption requires 50 poin ✓
3. POST /api/penukaran-produk with data ✓
4. Backend returns 400 error ✗
   Message: "Poin tidak mencukupi"
5. User sees error ✗
6. GET /api/penukaran-produk returns 500 error ✗
```

---

## 🎯 Priority Actions

1. **Check Laravel logs immediately** - Will show actual error for 500
2. **Verify user points in database** - Confirm 150 poin is saved
3. **Test POST endpoint with Postman** - Isolate if issue is frontend or backend
4. **Enable debug mode** - Get real error messages
5. **Review backend points logic** - Ensure comparison is correct

---

## 📝 Summary

**Two Separate Issues:**

1. **POST 400 Error**: Backend thinks points insufficient (likely logic error)
   - User has: 150 poin
   - Needs: 50 poin
   - Should work but returns error

2. **GET 500 Error**: Backend crashes when fetching redemption history
   - Likely missing relationship or database query error
   - Check logs for details

**Both need backend investigation!**


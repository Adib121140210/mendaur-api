# 🚨 PENUKARAN PRODUK - 500 Error Debugging Guide

**Status**: ✅ Points validation fixed | ❌ 500 error on creation  
**Time to Fix**: 15-30 minutes  
**Priority**: HIGH

---

## 🎯 The Problem

Frontend sends valid redemption request → Backend returns **500 error**  
"Terjadi kesalahan saat membuat penukaran produk"

### What We Know ✅
- ✅ Authentication works (token is valid)
- ✅ Points validation works (total_poin check is correct)
- ✅ Stock validation works (stok check is correct)
- ✅ Database structure is correct
- ✅ Migration has been run
- ❌ Something fails when creating the record

---

## 🔍 Root Cause Analysis

The error likely comes from ONE of these:

1. **Database Constraint Violation** (Most Likely)
   - Foreign key issue (user_id or produk_id doesn't exist)
   - NOT NULL constraint violated
   - Data type mismatch

2. **Mass Assignment Issue** (Less Likely, but Possible)
   - Fillable array missing a field
   - Guarded array blocking a field

3. **Transaction/Locking Issue** (Least Likely)
   - Database lock during transaction
   - Race condition with concurrent requests

4. **Model Relationship Issue**
   - Relationship not properly defined
   - Cache stale

---

## 🛠️ Step-by-Step Debugging

### Step 1: Enable Debug Mode (2 minutes)

```bash
# Edit .env file
APP_DEBUG=true
APP_LOG=single
```

Then try the request again. The 500 error should now show actual error details.

---

### Step 2: Check Laravel Logs (2 minutes)

```bash
# View real-time logs
tail -f storage/logs/laravel.log

# OR: See last 50 lines
tail -50 storage/logs/laravel.log

# Look for:
# - "SQLSTATE" (SQL errors)
# - "Integrity constraint violation"
# - "Column not found"
# - Any stack trace
```

**Expected Output Format:**
```
[2025-11-19 10:30:00] local.ERROR: SQLSTATE[HY000]: General error: ...
Stack trace:
  #0 /path/to/file.php(line)
  #1 /path/to/file.php(line)
```

---

### Step 3: Verify Database Connection (3 minutes)

```bash
# Test with php artisan
php artisan tinker

# Try to fetch data
>>> $user = User::find(1);
>>> echo $user->email;  // Should show email

# Check if users table is accessible
>>> User::count();  // Should return a number
```

---

### Step 4: Manual Record Creation Test (5 minutes)

This is the MOST IMPORTANT test!

```bash
php artisan tinker
```

Then run these commands ONE BY ONE:

```php
# Step 4a: Get a real user
$user = User::find(1);
if (!$user) {
    echo "ERROR: No user with ID 1. List users:";
    User::pluck('id', 'email');
    exit;
}
echo "User found: " . $user->email;

# Step 4b: Get a real product
$product = Produk::find(1);
if (!$product) {
    echo "ERROR: No product with ID 1. List products:";
    Produk::pluck('id', 'nama');
    exit;
}
echo "Product found: " . $product->nama;

# Step 4c: Check user points
echo "User total_poin: " . $user->total_poin;

# Step 4d: Check product stock
echo "Product stok: " . $product->stok;

# Step 4e: Try creating a redemption
$redemption = PenukaranProduk::create([
    'user_id' => $user->id,
    'produk_id' => $product->id,
    'nama_produk' => $product->nama,
    'poin_digunakan' => 50,
    'jumlah' => 1,
    'status' => 'pending',
    'alamat_pengiriman' => 'Test Address',
    'tanggal_penukaran' => now(),
]);

# If successful:
echo "SUCCESS! ID: " . $redemption->id;

# If error, you'll see the actual error message
```

---

### Step 5: Check Model Relationships (3 minutes)

```bash
php artisan tinker
```

```php
# Verify relationships work
$redemption = PenukaranProduk::first();
echo $redemption->user()->exists() ? "User exists" : "User missing!";
echo $redemption->produk()->exists() ? "Produk exists" : "Produk missing!";

# Verify fillable
$model = new PenukaranProduk();
echo "Fillable: " . implode(', ', $model->getFillable());
```

---

### Step 6: Test via API with Detailed Error Logging (5 minutes)

Add temporary logging to controller:

**File:** `app/Http/Controllers/PenukaranProdukController.php`

Around line 195, add logging before create:

```php
\Log::info('Creating redemption with data:', [
    'user_id' => $user->id,
    'produk_id' => $produk->id,
    'nama_produk' => $produk->nama,
    'poin_digunakan' => $totalPoin,
    'jumlah' => $jumlah,
    'alamat_pengiriman' => $validated['alamat_pengiriman'],
]);

try {
    $redemption = PenukaranProduk::create([
        'user_id' => $user->id,
        'produk_id' => $produk->id,
        'nama_produk' => $produk->nama,
        'poin_digunakan' => $totalPoin,
        'jumlah' => $jumlah,
        'status' => 'pending',
        'alamat_pengiriman' => $validated['alamat_pengiriman'],
        'tanggal_penukaran' => now(),
    ]);
    \Log::info('Redemption created successfully:', ['id' => $redemption->id]);
} catch (\Exception $e) {
    \Log::error('Failed to create redemption:', [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
    throw $e;
}
```

Then make the API request again and check logs.

---

## 📋 Common Solutions

### Solution 1: User Not Found
**Error:** "Integrity constraint violation: 1452 Cannot add or update a child row"

**Fix:** Check if user_id exists
```bash
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 1,
    "jumlah": 1,
    "alamat_pengiriman": "Test"
  }'
```

Make sure you're authenticated as a valid user!

---

### Solution 2: Product Not Found
**Error:** "Integrity constraint violation: 1452 Cannot add or update a child row"

**Fix:** Verify produk_id exists
```bash
# Check which products exist
php artisan tinker
>>> Produk::pluck('id', 'nama');
```

Then use a valid product ID.

---

### Solution 3: Missing Required Field
**Error:** "Column ... cannot be null"

**Fix:** Check which field is missing. The store() method should provide:
- ✅ user_id (from auth)
- ✅ produk_id (from request)
- ✅ nama_produk (from product)
- ✅ poin_digunakan (from calculation)
- ✅ jumlah (from request or default 1)
- ✅ status (set to 'pending')
- ✅ alamat_pengiriman (from request)
- ✅ tanggal_penukaran (set to now())

All these MUST be provided in the create() call.

---

### Solution 4: Fillable Array Issue
**Error:** "MassAssignmentException" or field not saved

**Fix:** Verify model fillable array includes all needed fields

**File:** `app/Models/PenukaranProduk.php`

```php
protected $fillable = [
    'user_id',
    'produk_id',
    'nama_produk',
    'poin_digunakan',
    'jumlah',
    'status',
    'alamat_pengiriman',
    'no_resi',
    'catatan',
    'tanggal_penukaran',
    'tanggal_pengiriman',
    'tanggal_diterima',
];
```

If a field is missing, add it!

---

## 🧪 Complete Test Procedure

Once you've debugged the issue, run this complete test:

```bash
# 1. Get token
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  | jq -r '.data.token')

echo "Token: $TOKEN"

# 2. Get user info
curl -s -X GET http://127.0.0.1:8000/api/profile \
  -H "Authorization: Bearer $TOKEN" | jq '.data | {id, email, total_poin}'

# 3. Get products
curl -s -X GET http://127.0.0.1:8000/api/produk \
  -H "Authorization: Bearer $TOKEN" | jq '.data[0:2] | .[] | {id, nama, poin, stok}'

# 4. Create redemption
curl -s -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 1,
    "jumlah": 1,
    "alamat_pengiriman": "Jl. Test No. 123"
  }' | jq .

# 5. Check history
curl -s -X GET http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" | jq '.data[0]'

# 6. Verify points deducted
curl -s -X GET http://127.0.0.1:8000/api/profile \
  -H "Authorization: Bearer $TOKEN" | jq '.data.total_poin'
```

---

## 📞 If Still Stuck

1. **Share the log error** - Copy the exact error from `storage/logs/laravel.log`
2. **Run tinker test** - Share the output of Step 4
3. **Check data** - Verify user and product IDs exist in database
4. **Verify migration** - Run: `php artisan migrate:status`

---

## ✅ Success Criteria

After fix:
- ✅ POST /api/penukaran-produk returns **201 Created**
- ✅ Response contains redemption data with ID
- ✅ Database has new record in penukaran_produk table
- ✅ User's total_poin decreased
- ✅ Product's stok decreased
- ✅ GET /api/penukaran-produk shows the new record
- ✅ No errors in Laravel logs

---

## 🚀 Final Checklist

Before declaring "FIXED":
- [ ] 500 error is gone
- [ ] 201 Created response received
- [ ] Data saved to database
- [ ] Points deducted correctly
- [ ] Stock reduced correctly
- [ ] No Laravel errors
- [ ] Can retrieve created record via GET

---

**Document Version:** 1.0  
**Last Updated:** November 19, 2025  
**Status:** Ready for Debugging

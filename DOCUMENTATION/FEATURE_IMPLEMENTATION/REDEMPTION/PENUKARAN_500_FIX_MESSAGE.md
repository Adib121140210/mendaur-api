# ⚡ URGENT: 500 Error When Creating Redemption

**Status**: Points validation fixed ✅ | Creation fails with 500 ❌  
**Likely Cause**: Database constraint or data validation issue  
**Time to Fix**: 15-30 minutes with debugging steps  

---

## 🚨 FOR BACKEND DEVELOPER (READ NOW!)

### Quick Test (Do This First!)

```bash
# 1. Open tinker
php artisan tinker

# 2. Get real user and product
$user = User::find(1);
$product = Produk::find(1);

# 3. Try creating redemption manually
$r = PenukaranProduk::create([
    'user_id' => $user->id,
    'produk_id' => $product->id,
    'nama_produk' => $product->nama,
    'poin_digunakan' => 50,
    'jumlah' => 1,
    'status' => 'pending',
    'alamat_pengiriman' => 'Test Address',
    'tanggal_penukaran' => now(),
]);

# If you see an error here, COPY IT and check "Solutions" section below
# If successful, the issue is somewhere else (auth, validation, etc)
```

---

## 🔍 What Could Be Wrong

| Issue | Error Message | Fix |
|-------|---------------|-----|
| User doesn't exist | "Cannot add or update a child row" | Check user_id is valid |
| Product doesn't exist | "Cannot add or update a child row" | Check product_id is valid |
| Missing field | "Column X cannot be null" | Add field to create() |
| Wrong data type | "Truncated incorrect X value" | Check data types |
| Migration not run | "Table 'penukaran_produk' doesn't exist" | Run `php artisan migrate` |

---

## 📊 The Code (Is Correct!)

The controller **already has**:
- ✅ Status field set to 'pending'
- ✅ All required fields in create() call
- ✅ Transaction management
- ✅ Error handling
- ✅ Database foreign keys defined

So the issue is likely:
1. Database state problem (missing data)
2. Data type mismatch
3. Constraint violation

---

## 🛠️ Debugging Steps

### Step 1: Check Logs (2 min)
```bash
tail -f storage/logs/laravel.log
# Then try the request again
# Look for actual error message
```

### Step 2: Manual Creation Test (3 min)
```bash
php artisan tinker
# Copy/paste the "Quick Test" code above
# See actual error if it occurs
```

### Step 3: Verify Data Exists (2 min)
```bash
php artisan tinker

# Check users
>>> User::count();  // Should show number > 0
>>> User::first()->email;  // Should show email

# Check products
>>> Produk::count();  // Should show number > 0
>>> Produk::first()->nama;  // Should show product name

# Check penukaran_produk table exists
>>> Schema::hasTable('penukaran_produk');  // Should show true
```

### Step 4: Add Debug Logging (5 min)

Edit `app/Http/Controllers/PenukaranProdukController.php` around line 195:

```php
\Log::info('DEBUG: Creating redemption', [
    'user_id' => $user->id,
    'produk_id' => $produk->id,
    'totalPoin' => $totalPoin,
    'jumlah' => $jumlah,
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
    \Log::info('DEBUG: Redemption created!', ['id' => $redemption->id]);
} catch (\Exception $e) {
    \Log::error('DEBUG: Creation failed!', [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
    ]);
    throw $e;
}
```

Then try the API request and check logs.

---

## 🎯 Verification (What Should Work After Fix)

```bash
# Get token
TOKEN="your_token_here"

# Try creating redemption
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 1,
    "jumlah": 1,
    "alamat_pengiriman": "Jl. Test No. 123"
  }'

# Expected: 201 Created with data
# Current: 500 Internal Server Error
```

---

## 📞 Need More Help?

See **PENUKARAN_PRODUK_500_ERROR_FIX.md** for:
- Detailed 6-step debugging guide
- Common solutions with fixes
- Complete test procedures
- Root cause analysis

---

**Status:** Ready for debugging  
**Priority:** HIGH (blocks feature)  
**Estimated Time:** 15-30 min to resolve

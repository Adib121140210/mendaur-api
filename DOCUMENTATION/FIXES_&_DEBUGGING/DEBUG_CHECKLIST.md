# ✅ Penukaran Produk - Debugging Checklist

**For Backend Developer**  
**Issue**: 500 error when creating redemption  
**Time to Fix**: 15-30 minutes  

---

## 🎯 Quick Start (Pick One Path)

### Path A: "I'm in a hurry" (10 min)
```bash
# 1. Check logs (1 min)
tail -f storage/logs/laravel.log

# 2. Test in tinker (5 min)
php artisan tinker
$r = PenukaranProduk::create([
    'user_id' => 1,
    'produk_id' => 1,
    'nama_produk' => 'Test',
    'poin_digunakan' => 50,
    'jumlah' => 1,
    'status' => 'pending',
    'alamat_pengiriman' => 'Test',
    'tanggal_penukaran' => now(),
]);

# 3. If error, COPY IT and ask for help
```

### Path B: "Give me all steps" (30 min)
See **PENUKARAN_500_FIX_MESSAGE.md**

---

## 📋 Pre-Debug Checklist

Before debugging, verify:

- [ ] Laravel is running: `php artisan serve`
- [ ] Database is connected: `php artisan tinker` → `DB::connection()->getPdo()`
- [ ] APP_DEBUG=true in .env
- [ ] Not in production mode
- [ ] Terminal has access to laravel logs

---

## 🔍 Step-by-Step Debugging

### Step 1: Get the Actual Error (2 minutes)
**Goal**: Find out WHAT the error is

```bash
# Open new terminal and run:
tail -f storage/logs/laravel.log
```

**In another terminal, make the API request:**
```bash
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"produk_id":1,"jumlah":1,"alamat_pengiriman":"Test"}'
```

**Look in logs for lines like:**
```
[2025-11-19 10:30:00] local.ERROR: SQLSTATE[HY000]: General error:
Stack trace:
  #0 /path/to/file.php(line): 
```

**Copy the error and note it below:**
```
ERROR MESSAGE:
_________________________________________


LINE NUMBER:
_________________________________________
```

---

### Step 2: Verify Data Exists (3 minutes)
**Goal**: Make sure we're not trying to create with non-existent foreign keys

```bash
php artisan tinker

# Check if users exist
>>> User::count()
# Should show: 1 or more

# Check if products exist
>>> Produk::count()
# Should show: 1 or more

# Check if a user has enough points
>>> $u = User::first(); $u->total_poin
# Should show: number > 0

# Check if a product has stock
>>> $p = Produk::first(); $p->stok
# Should show: number > 0

# Check if table exists
>>> Schema::hasTable('penukaran_produk')
# Should show: true
```

**Mark here what works:**
- [ ] User::count() returns > 0
- [ ] Produk::count() returns > 0
- [ ] User has total_poin > 0
- [ ] Product has stok > 0
- [ ] penukaran_produk table exists

**If any marked false**: Database might be empty. Check data!

---

### Step 3: Test Manual Creation (5 minutes)
**Goal**: Reproduce the error in tinker to see exact message

```bash
php artisan tinker
```

**Copy/paste this EXACTLY, one line at a time:**

```php
$user = User::find(1);
if (!$user) { echo "ERROR: No user with ID 1"; exit; }
echo "✓ User found: " . $user->email . "\n";

$product = Produk::find(1);
if (!$product) { echo "ERROR: No product with ID 1"; exit; }
echo "✓ Product found: " . $product->nama . "\n";

echo "✓ User points: " . $user->total_poin . "\n";
echo "✓ Product stock: " . $product->stok . "\n";

try {
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
    echo "✓ SUCCESS! ID: " . $r->id . "\n";
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
```

**Result:**
- ✅ If you see "SUCCESS! ID: X" → Issue is elsewhere (auth, middleware)
- ❌ If you see "ERROR: ..." → Copy the error message below

```
TINKER ERROR:
_________________________________________


```

---

### Step 4: Check Model & Fillable (2 minutes)
**Goal**: Make sure mass assignment isn't blocking

```bash
php artisan tinker

# Check fillable array
>>> $m = new PenukaranProduk()
>>> $m->getFillable()

# Should include ALL of these:
# user_id, produk_id, nama_produk, poin_digunakan, jumlah, 
# status, alamat_pengiriman, tanggal_penukaran, etc
```

**Verify fillable contains:**
- [ ] user_id
- [ ] produk_id
- [ ] nama_produk
- [ ] poin_digunakan
- [ ] jumlah
- [ ] status
- [ ] alamat_pengiriman
- [ ] tanggal_penukaran

If any missing, that's your issue!

---

### Step 5: Check Table Structure (2 minutes)
**Goal**: Make sure columns actually exist

```bash
php artisan tinker

# Get all columns
>>> Schema::getColumnListing('penukaran_produk')

# Should show: Array with all columns listed above
```

**Verify columns:**
- [ ] user_id exists
- [ ] produk_id exists
- [ ] nama_produk exists
- [ ] poin_digunakan exists
- [ ] jumlah exists
- [ ] status exists
- [ ] alamat_pengiriman exists
- [ ] tanggal_penukaran exists

If column doesn't exist, migration didn't run!

---

### Step 6: Enable Debug Logging (5 minutes)
**Goal**: Get detailed error information

**Edit**: `app/Http/Controllers/PenukaranProdukController.php`

Around line 195, add:

```php
\Log::info('Creating redemption:', [
    'user_id' => $user->id,
    'produk_id' => $produk->id,
    'totalPoin' => $totalPoin,
    'jumlah' => $jumlah,
    'alamat' => $validated['alamat_pengiriman'],
]);

try {
    $redemption = PenukaranProduk::create([
        // ... existing fields ...
    ]);
    \Log::info('✓ Redemption created!', ['id' => $redemption->id]);
} catch (\Exception $e) {
    \Log::error('✗ Creation failed!', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
    ]);
    throw $e;
}
```

Then try API request again and check logs.

---

## 🎯 Common Issues & Fixes

### Issue: "Cannot add or update a child row"
**Cause**: Foreign key doesn't exist  
**Fix**: Verify user_id and produk_id exist
```bash
php artisan tinker
>>> User::pluck('id');
>>> Produk::pluck('id');
```

---

### Issue: "Column 'X' cannot be null"
**Cause**: Required field not provided  
**Fix**: Add field to create() call or provide value
```php
// Make sure ALL of these are in create():
'user_id', 'produk_id', 'nama_produk', 'poin_digunakan', 
'jumlah', 'status', 'alamat_pengiriman', 'tanggal_penukaran'
```

---

### Issue: "Table 'penukaran_produk' doesn't exist"
**Cause**: Migration not run  
**Fix**: Run migration
```bash
php artisan migrate
```

---

### Issue: "SQLSTATE[22007]: Invalid datetime format"
**Cause**: tanggal_penukaran in wrong format  
**Fix**: Use now() or Carbon instance
```php
'tanggal_penukaran' => now(),  // ✓ Correct
// NOT: 'tanggal_penukaran' => '2025-11-19',  // ✗ Wrong format
```

---

## ✅ When You've Found the Issue

**Do this:**
1. Note the exact error message
2. Note which step it failed on
3. Share with team:
   - Error message
   - Which step in checklist
   - What you already tried
4. Team will help with fix

---

## 📞 Help Resources

**Need quick answer?**  
→ Share your error from Step 1 and Step 3

**Need comprehensive guide?**  
→ Read `PENUKARAN_PRODUK_500_ERROR_FIX.md`

**Need more context?**  
→ Read `PENUKARAN_500_FIX_MESSAGE.md`

---

## ⏱️ Time Tracking

| Step | Time | Status |
|------|------|--------|
| Step 1: Get Error | 2 min | ⏳ |
| Step 2: Verify Data | 3 min | ⏳ |
| Step 3: Test Creation | 5 min | ⏳ |
| Step 4: Check Model | 2 min | ⏳ |
| Step 5: Check Table | 2 min | ⏳ |
| Step 6: Debug Logging | 5 min | ⏳ |
| **TOTAL** | **~20 min** | ⏳ |

---

## 📋 Final Checklist

**Before asking for help, verify:**
- [ ] I ran all 6 steps
- [ ] I copied the exact error message
- [ ] I checked the logs
- [ ] I tested in tinker
- [ ] I verified data exists
- [ ] I added debug logging

**Ready to share with team:**
- [ ] Error message from Step 1 & 3
- [ ] Which step failed
- [ ] Tinker test result
- [ ] Logs content

---

**Status**: Ready for debugging  
**Time to Complete**: 15-30 minutes  
**Next Action**: Start with Step 1  

**Good Luck! You've got this! 💪**

---

*Generated: November 19, 2025*  
*Document Version: 1.0*

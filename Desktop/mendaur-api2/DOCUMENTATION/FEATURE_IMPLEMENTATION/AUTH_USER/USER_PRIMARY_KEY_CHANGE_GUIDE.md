# 📋 USER TABLE PRIMARY KEY CHANGE - Implementation Guide

**Date**: November 25, 2025  
**Change**: Convert primary key from `id` to `no_hp` (phone number)  
**Status**: ✅ Ready for Migration  

---

## 📊 What Changed

### **Before**
```
users table:
├── id (BIGINT) - Primary Key, Auto-Increment
├── nama (VARCHAR)
├── email (VARCHAR) - UNIQUE
├── no_hp (VARCHAR) - Nullable
├── alamat (TEXT)
├── foto_profil (VARCHAR)
├── total_poin (INT)
├── total_setor_sampah (INT)
├── level (VARCHAR)
└── timestamps
```

### **After**
```
users table:
├── no_hp (VARCHAR) - Primary Key (NOT NULL)
├── nama (VARCHAR)
├── email (VARCHAR) - UNIQUE
├── alamat (TEXT)
├── foto_profil (VARCHAR)
├── total_poin (INT)
├── total_setor_sampah (INT)
├── level (VARCHAR)
└── timestamps
```

---

## 🔄 What Was Updated

### **1. Migration File Created**
**File**: `database/migrations/2025_11_25_000000_change_users_primary_key_to_no_hp.php`

**Changes in UP migration**:
- ✅ Drop all foreign key constraints from child tables
- ✅ Remove `id` column from users table
- ✅ Make `no_hp` the primary key (NOT NULL)
- ✅ Re-create all foreign key constraints with new primary key

**Affected child tables**:
- tabung_sampah
- penukaran_produk
- transaksi
- penarikan_tunai
- notifikasi
- log_aktivitas
- user_badges
- badge_progress
- poin_transaksis
- sessions

### **2. User Model Updated**
**File**: `app/Models/User.php`

**New properties added**:
```php
protected $primaryKey = 'no_hp';      // Specify primary key
public $incrementing = false;          // no_hp is NOT auto-increment
protected $keyType = 'string';         // no_hp is VARCHAR/string
```

---

## ⚠️ Important Considerations

### **Before Running Migration**

1. **Backup Your Database**
   ```bash
   # MySQL backup
   mysqldump -u root -p mendaur_db > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Ensure all users have no_hp**
   ```sql
   -- Check for NULL no_hp values
   SELECT COUNT(*) FROM users WHERE no_hp IS NULL;
   
   -- If any NULL values exist, update them first
   UPDATE users SET no_hp = CONCAT('62', SUBSTRING(RAND()*1000000000000000, 1, 10)) 
   WHERE no_hp IS NULL;
   ```

3. **Ensure no_hp values are UNIQUE**
   ```sql
   -- Check for duplicate no_hp values
   SELECT no_hp, COUNT(*) as count FROM users 
   GROUP BY no_hp HAVING count > 1;
   
   -- If duplicates exist, resolve them before migration
   ```

### **Data Implications**

- ❌ You **cannot** use `$user->id` anymore - use `$user->no_hp` instead
- ❌ Foreign keys will now reference `no_hp` instead of `id`
- ✅ `$user->find()` will now find by `no_hp` instead of `id`
- ✅ All relationships still work the same way

---

## 🚀 How to Run the Migration

### **Step 1: Backup Database**
```bash
mysqldump -u root -p mendaur_db > backup_before_primary_key_change.sql
```

### **Step 2: Run Migration**
```bash
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
php artisan migrate
```

**Expected output**:
```
Migrating: 2025_11_25_000000_change_users_primary_key_to_no_hp
Migrated:  2025_11_25_000000_change_users_primary_key_to_no_hp (X.XXs)
```

### **Step 3: Verify Changes**
```bash
# Check users table structure
php artisan tinker
>>> DB::statement('DESCRIBE users');

# Check that no_hp is now the primary key
>>> DB::statement("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
```

---

## 🔌 Code Changes Required in Your Application

### **Controller Changes**

**Before**:
```php
// Getting user by ID
$user = User::find($id);
$userId = auth()->user()->id;
```

**After**:
```php
// Getting user by phone number
$user = User::find($no_hp);
$userPhone = auth()->user()->no_hp;
```

### **Query Changes**

**Before**:
```php
$users = User::where('id', $userId)->first();
return response()->json(['user_id' => $user->id]);
```

**After**:
```php
$users = User::where('no_hp', $userPhone)->first();
return response()->json(['user_id' => $user->no_hp]);
```

### **Route Model Binding**

**Before**:
```php
Route::get('/users/{user}', function (User $user) {
    // Laravel finds user by id
    return $user;
});
```

**After**:
```php
// In AppServiceProvider or RouteServiceProvider
Route::model('user', User::class);

// Or in routes directly with explicit binding
Route::get('/users/{user:no_hp}', function (User $user) {
    // Laravel finds user by no_hp
    return $user;
});
```

### **API Response Changes**

**Before**:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "nama": "Budi",
    "no_hp": "08123456789"
  }
}
```

**After**:
```json
{
  "status": "success",
  "data": {
    "no_hp": "08123456789",
    "nama": "Budi"
  }
}
```

---

## 📝 Update Your Controllers

### **Example: UserController Updates**

```php
// BEFORE
public function show($id)
{
    $user = User::findOrFail($id);
    return response()->json($user);
}

// AFTER
public function show($no_hp)
{
    $user = User::findOrFail($no_hp);
    return response()->json($user);
}
```

### **Example: AuthController Updates**

```php
// BEFORE
public function login(Request $request)
{
    // ... validation ...
    $user = User::where('email', $request->email)->first();
    return response()->json([
        'user' => [
            'id' => $user->id,  // REMOVE THIS
            'no_hp' => $user->no_hp,
        ]
    ]);
}

// AFTER
public function login(Request $request)
{
    // ... validation ...
    $user = User::where('email', $request->email)->first();
    return response()->json([
        'user' => [
            'no_hp' => $user->no_hp,  // Use no_hp as identifier
        ]
    ]);
}
```

---

## 🧪 Testing Checklist

After migration, test these scenarios:

- [ ] User registration still works
- [ ] User login still works
- [ ] Getting user profile works
- [ ] User relationships work (tabung_sampah, transaksi, etc.)
- [ ] Foreign key constraints work (try deleting a user)
- [ ] Queries by no_hp work correctly
- [ ] Route model binding works (if using {user:no_hp})
- [ ] API responses show correct user identifier
- [ ] Sanctum tokens still work properly
- [ ] All user-related endpoints work

### **Test Queries**

```php
// Test 1: Find user by no_hp
$user = User::find('08123456789');
// Should return the user

// Test 2: Create new user
$user = User::create([
    'no_hp' => '08987654321',
    'nama' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
]);
// Should work without specifying id

// Test 3: User relationships
$user = User::find('08123456789');
$tabungSampah = $user->tabungSampahs()->get();
// Should return user's tabung_sampah records

// Test 4: Delete cascade
$user = User::find('08123456789');
$user->delete();
// Should cascade delete all related records
```

---

## ⚡ Performance Considerations

### **Advantages**
- ✅ Phone number is more meaningful identifier
- ✅ No artificial ID needed
- ✅ Clearer business logic
- ✅ Direct phone-based lookups faster

### **Disadvantages**
- ⚠️ String primary key slightly slower than integer
- ⚠️ String comparisons are slower than numeric
- ⚠️ Foreign key columns are now VARCHAR instead of BIGINT
- ⚠️ Indexes will be larger

### **Mitigation**
```sql
-- Add index for faster lookups
CREATE INDEX idx_no_hp ON users(no_hp);

-- Add index on related tables
CREATE INDEX idx_user_phone ON tabung_sampah(user_id);
CREATE INDEX idx_user_phone ON penukaran_produk(user_id);
```

---

## 🔄 Rollback Instructions

If you need to rollback this change:

```bash
php artisan migrate:rollback
```

This will:
- ✅ Restore `id` as primary key
- ✅ Make `no_hp` nullable again
- ✅ Restore original foreign key constraints
- ✅ Return to original state

---

## 📋 Related Files That May Need Updates

Search your codebase for these patterns and update accordingly:

```php
// Pattern 1: Using id directly
$user->id
// Change to: $user->no_hp

// Pattern 2: route parameters
route('user.show', $user->id)
// Change to: route('user.show', $user->no_hp)

// Pattern 3: Foreign key references
$table->foreignId('user_id')
// Already updated in migration

// Pattern 4: API responses
'user_id' => $user->id
// Change to: 'user_id' => $user->no_hp
```

---

## 📞 Troubleshooting

### **Migration Fails with Foreign Key Error**
```bash
# Solution: Disable foreign key checks temporarily
mysql> SET FOREIGN_KEY_CHECKS = 0;
php artisan migrate
mysql> SET FOREIGN_KEY_CHECKS = 1;
```

### **Queries Not Finding Users**
```php
// Check if no_hp is correct format
User::where('no_hp', '08123456789')->first(); // Should work
User::where('no_hp', '+628123456789')->first(); // Different format!
```

### **Foreign Key Constraint Fails**
```bash
# Check if all users have valid no_hp
SELECT * FROM users WHERE no_hp IS NULL OR no_hp = '';

# Check for duplicates
SELECT no_hp, COUNT(*) FROM users GROUP BY no_hp HAVING COUNT(*) > 1;
```

---

## 📊 Migration Stats

| Item | Value |
|------|-------|
| Migration File | 2025_11_25_000000_change_users_primary_key_to_no_hp.php |
| Tables Affected | 11 tables |
| Foreign Keys Updated | 10 constraints |
| Model Changes | 1 file (User.php) |
| Estimated Migration Time | 5-10 seconds |
| Reversible | Yes (rollback available) |

---

## ✅ Summary

**What This Does**:
- Changes the users table primary key from `id` (BIGINT auto-increment) to `no_hp` (VARCHAR string)
- Updates all foreign key relationships in child tables
- Configures the User model to recognize `no_hp` as the primary key

**When to Use This**:
- When phone number should be the unique identifier for users
- When you want to organize system around phone numbers instead of artificial IDs
- When phone numbers are guaranteed unique and non-null

**Things to Remember**:
- ✅ Backup database before running
- ✅ Ensure no_hp values are unique and not null
- ✅ Update code to use `$user->no_hp` instead of `$user->id`
- ✅ Update routes/API responses accordingly
- ✅ Test thoroughly after migration

---

**Status**: Ready for migration  
**Date Created**: November 25, 2025  
**Reversible**: Yes (rollback available)  

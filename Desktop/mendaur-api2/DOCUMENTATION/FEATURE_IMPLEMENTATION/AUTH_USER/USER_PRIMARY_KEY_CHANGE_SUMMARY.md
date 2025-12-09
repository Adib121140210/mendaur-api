# 🔑 UNIQUE KEY CHANGE - QUICK SUMMARY

**Change**: User table primary key `id` → `no_hp` (phone number)  
**Date**: November 25, 2025  
**Status**: ✅ Ready to migrate  

---

## ✅ What Was Done

### **1. Created Migration File**
```
📄 database/migrations/2025_11_25_000000_change_users_primary_key_to_no_hp.php
```

This migration:
- ✅ Drops all foreign key constraints
- ✅ Removes `id` column from users table
- ✅ Makes `no_hp` the primary key (NOT NULL)
- ✅ Updates all foreign keys in 10 child tables
- ✅ Includes rollback functionality

### **2. Updated User Model**
```
📄 app/Models/User.php
```

Added 3 properties:
```php
protected $primaryKey = 'no_hp';      // Primary key is now no_hp
public $incrementing = false;          // Not auto-increment
protected $keyType = 'string';         // It's a string, not integer
```

---

## 📋 Important Before Running

**Prerequisites**:
1. ✅ All users must have a `no_hp` value (NOT NULL)
2. ✅ All `no_hp` values must be UNIQUE
3. ✅ **Backup your database first!**

```bash
# Backup command
mysqldump -u root -p mendaur_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 🚀 How to Apply

### **Step 1: Run the migration**
```bash
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
php artisan migrate
```

### **Step 2: Update your code**

**Replace all instances of**:
```php
// OLD
$user->id                          // DON'T USE
User::find($id)                    // DON'T USE
auth()->user()->id                 // DON'T USE

// NEW
$user->no_hp                       // USE THIS
User::find($no_hp)                 // USE THIS
auth()->user()->no_hp              // USE THIS
```

### **Step 3: Test everything**
```bash
php artisan tinker
>>> User::find('08123456789');    // Should return user
>>> User::first()->no_hp;          // Should show phone number
>>> User::count();                 // Should show all users
```

---

## 📊 Affected Tables

Child tables with foreign keys to users:
1. tabung_sampah
2. penukaran_produk
3. transaksi
4. penarikan_tunai
5. notifikasi
6. log_aktivitas
7. user_badges
8. badge_progress
9. poin_transaksis
10. sessions

All foreign keys updated automatically by the migration.

---

## ⚠️ Code Changes Needed

### **In Controllers**
```php
// BEFORE
public function show($id) {
    $user = User::find($id);
}

// AFTER
public function show($no_hp) {
    $user = User::find($no_hp);
}
```

### **In Routes** (if using model binding)
```php
// BEFORE
Route::get('/users/{user}', ...)

// AFTER
Route::get('/users/{user:no_hp}', ...)
```

### **In API Responses**
```php
// BEFORE
['id' => $user->id, 'name' => $user->nama]

// AFTER
['no_hp' => $user->no_hp, 'name' => $user->nama]
```

---

## 🔄 Rollback If Needed

```bash
php artisan migrate:rollback

# This will restore:
# ✅ id as primary key
# ✅ no_hp as nullable
# ✅ Original foreign keys
```

---

## 📁 Files Created/Modified

```
✅ Created:
   └── database/migrations/2025_11_25_000000_change_users_primary_key_to_no_hp.php

📝 Modified:
   └── app/Models/User.php

📖 Documentation:
   └── USER_PRIMARY_KEY_CHANGE_GUIDE.md (Complete guide with details)
   └── USER_PRIMARY_KEY_CHANGE_SUMMARY.md (This file)
```

---

## ✨ Summary

| Item | Details |
|------|---------|
| **Primary Key** | `id` → `no_hp` |
| **Type** | BIGINT auto-increment → VARCHAR string |
| **Migration File** | 2025_11_25_000000_change_users_primary_key_to_no_hp.php |
| **Model Updates** | User.php (3 new properties) |
| **Child Tables Affected** | 10 tables |
| **Foreign Keys Updated** | 10 constraints |
| **Reversible** | Yes (rollback available) |
| **Estimated Time** | 5-10 seconds |

---

## 🎯 Next Steps

1. ✅ Backup database
2. ✅ Run migration: `php artisan migrate`
3. ✅ Update all `$user->id` to `$user->no_hp` in code
4. ✅ Update routes if using model binding
5. ✅ Update API responses
6. ✅ Test thoroughly
7. ✅ Deploy

---

**Ready to migrate!** 🚀

For complete details, see `USER_PRIMARY_KEY_CHANGE_GUIDE.md`

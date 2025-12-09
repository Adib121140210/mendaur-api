# ✅ USER PRIMARY KEY CHANGE - IMPLEMENTATION CHECKLIST

**Date**: November 25, 2025  
**Status**: ✅ Ready for Implementation  
**Complexity**: Medium  
**Estimated Time**: 15-30 minutes  

---

## 📋 Pre-Migration Checklist

- [ ] **Backup Database**
  ```bash
  mysqldump -u root -p mendaur_db > backup_before_pk_change.sql
  ```
  - Location: Save to safe location
  - Verification: File size > 100KB (depending on data)

- [ ] **Verify no_hp Data Quality**
  ```sql
  -- Check for NULL values
  SELECT COUNT(*) FROM users WHERE no_hp IS NULL;
  -- Should return: 0
  
  -- Check for empty strings
  SELECT COUNT(*) FROM users WHERE no_hp = '';
  -- Should return: 0
  
  -- Check for duplicates
  SELECT no_hp, COUNT(*) FROM users GROUP BY no_hp HAVING COUNT(*) > 1;
  -- Should return: No rows
  
  -- Check for proper format
  SELECT DISTINCT no_hp FROM users LIMIT 10;
  -- Should show valid phone numbers like: 08123456789
  ```

- [ ] **Review Existing Data**
  - [ ] All users have phone numbers
  - [ ] Phone numbers are unique
  - [ ] Phone numbers follow consistent format
  - [ ] No NULL or empty values

- [ ] **Notify Team**
  - [ ] Database will be unavailable for ~10 seconds
  - [ ] APIs will be briefly offline
  - [ ] Prepare rollback plan if needed

---

## 🚀 Migration Execution Checklist

### **Step 1: Backup**
- [ ] Run backup command
- [ ] Verify backup file created
- [ ] Test backup can be restored (optional but recommended)

### **Step 2: Prepare Application**
- [ ] Stop all running Laravel processes
- [ ] Clear Laravel cache:
  ```bash
  php artisan config:cache
  php artisan view:cache
  ```

### **Step 3: Run Migration**
- [ ] Run migration command:
  ```bash
  php artisan migrate
  ```
- [ ] Check for errors in output
- [ ] Verify "Migrated: 2025_11_25_000000_change_users_primary_key_to_no_hp"

### **Step 4: Verify Database Changes**
- [ ] Check users table structure:
  ```bash
  php artisan tinker
  >>> DB::statement('DESCRIBE users');
  ```
  - Should show `no_hp` as PRI (Primary Key)
  - Should NOT show `id` column

- [ ] Verify foreign keys:
  ```bash
  >>> DB::statement("SHOW KEYS FROM users");
  >>> DB::statement("SHOW KEYS FROM tabung_sampah");
  ```

### **Step 5: Verify Data Integrity**
- [ ] Check user count matches before/after
  ```bash
  >>> User::count();
  ```
- [ ] Verify relationships still work:
  ```bash
  >>> $user = User::first();
  >>> $user->tabungSampahs()->count();
  >>> $user->poinTransaksis()->count();
  ```

- [ ] Test deletions (cascade):
  ```bash
  >>> $user = User::where('no_hp', 'TEST_VALUE')->first();
  >>> $user->delete();
  >>> // Check all related records are deleted
  ```

---

## 📝 Code Update Checklist

### **Controllers**
- [ ] Search for `.id` in controller directory
  ```bash
  grep -r "->id" app/Http/Controllers/
  ```
  - [ ] Update each occurrence to `->no_hp`
  - [ ] Update foreign key references

- [ ] Update AuthController
  - [ ] `login()` - check what's returned
  - [ ] `profile()` - check what's returned
  - [ ] `register()` - ensure no_hp is used

- [ ] Update UserController
  - [ ] `show($no_hp)` - parameter name
  - [ ] `update()` - foreign key handling
  - [ ] Any other user-related methods

- [ ] Update other controllers
  - [ ] Any method that references `$user->id`
  - [ ] Any method that queries by user_id

### **Routes**
- [ ] Update route model binding (if using):
  ```php
  // From: Route::get('/users/{user}', ...)
  // To: Route::get('/users/{user:no_hp}', ...)
  ```

- [ ] Update route parameters:
  ```php
  // From: route('user.show', $user->id)
  // To: route('user.show', $user->no_hp)
  ```

### **API Responses**
- [ ] Update JSON responses to include `no_hp` instead of `id`
- [ ] Update any API documentation
- [ ] Update frontend API calls if necessary

### **Database Queries**
- [ ] Update any raw SQL queries
- [ ] Update Eloquent queries with foreign keys
- [ ] Test all queries work correctly

---

## 🧪 Testing Checklist

### **Unit Tests**
- [ ] User creation with no_hp
- [ ] User retrieval by no_hp
- [ ] User relationships (tabungSampah, transaksi, etc.)
- [ ] User deletion (cascade delete)
- [ ] User update operations

### **Integration Tests**
- [ ] Login endpoint works
- [ ] Register endpoint works
- [ ] Get user profile works
- [ ] Update user profile works
- [ ] Delete user works (and cascades)

### **API Tests (Postman)**
- [ ] POST /api/register - Create user
- [ ] POST /api/login - Login user
- [ ] GET /api/profile - Get profile
- [ ] PUT /api/profile - Update profile
- [ ] All other user-related endpoints

### **Database Tests**
- [ ] Foreign key constraints work
- [ ] Cascade deletes work
- [ ] Relationships load correctly
- [ ] No orphaned records

### **Frontend Tests**
- [ ] Frontend receives correct user data
- [ ] Frontend stores no_hp instead of id
- [ ] Frontend displays user info correctly
- [ ] Frontend operations work as expected

---

## 🔄 Rollback Checklist (If Needed)

If something goes wrong:

- [ ] Stop the application
- [ ] Restore from backup:
  ```bash
  mysql -u root -p mendaur_db < backup_before_pk_change.sql
  ```

- [ ] Rollback migration:
  ```bash
  php artisan migrate:rollback
  ```

- [ ] Restart application
- [ ] Verify everything back to normal

---

## 📊 Success Verification

### **Database Level**
- ✅ `no_hp` is primary key (PRI)
- ✅ `id` column is removed
- ✅ Foreign keys point to `no_hp`
- ✅ All data intact
- ✅ Relationships work

### **Application Level**
- ✅ No errors in logs
- ✅ All endpoints work
- ✅ User queries work correctly
- ✅ Relationships load properly
- ✅ API responses correct

### **Frontend Level**
- ✅ Login works
- ✅ User display correct
- ✅ Profile operations work
- ✅ All user features functional

---

## 📋 Files Created/Modified

### **Migration File**
```
📄 database/migrations/2025_11_25_000000_change_users_primary_key_to_no_hp.php
   - Size: ~8 KB
   - Lines: 270
   - Status: ✅ Ready
```

### **Model File**
```
📝 app/Models/User.php
   - Changes: 3 properties added
   - Lines added: ~15
   - Status: ✅ Updated
```

### **Documentation Files**
```
📖 USER_PRIMARY_KEY_CHANGE_GUIDE.md (Complete guide - 300+ lines)
📖 USER_PRIMARY_KEY_CHANGE_SUMMARY.md (Quick reference - 100+ lines)
📖 USER_PRIMARY_KEY_CHANGE_CHECKLIST.md (This file)
```

---

## ⚠️ Important Notes

1. **No Automated Code Updates**
   - The migration only updates the database
   - You must manually update code to use `no_hp` instead of `id`
   - Search for `->id` and `$user->id` in your codebase

2. **API Breaking Change**
   - This is a **breaking change** for API consumers
   - Update API documentation
   - Notify frontend team before rolling out

3. **Performance Impact**
   - String primary key is slightly slower than integer
   - Impact is negligible for most applications
   - Foreign key columns are now VARCHAR (larger storage)

4. **Backup is Critical**
   - Always backup before running this migration
   - Test backup restoration
   - Have rollback plan ready

---

## 📞 Quick Reference

### **Migration Commands**
```bash
# Run migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Show migration status
php artisan migrate:status
```

### **Database Commands**
```bash
# Backup database
mysqldump -u root -p mendaur_db > backup.sql

# Restore database
mysql -u root -p mendaur_db < backup.sql

# Check primary key
mysql> SHOW KEYS FROM users WHERE Key_name = 'PRIMARY';
```

### **Test Queries**
```bash
php artisan tinker

# Find user
>>> User::find('08123456789')

# Create user
>>> User::create(['no_hp' => '08987654321', 'nama' => 'Test', ...])

# Update user
>>> $user = User::find('08123456789'); $user->update(['nama' => 'New Name']);

# Delete user (cascade)
>>> User::find('08123456789')->delete();
```

---

## ✅ Final Sign-Off

Before proceeding:
- [ ] All backup steps completed
- [ ] Data quality verified
- [ ] Team notified
- [ ] Rollback plan prepared
- [ ] Code review done
- [ ] Testing plan ready

**Ready to migrate**: ✅ YES

**Go ahead and run**: `php artisan migrate`

---

**Status**: ✅ Complete  
**Date**: November 25, 2025  
**Complexity**: Medium  
**Risk Level**: Low (with backup)  

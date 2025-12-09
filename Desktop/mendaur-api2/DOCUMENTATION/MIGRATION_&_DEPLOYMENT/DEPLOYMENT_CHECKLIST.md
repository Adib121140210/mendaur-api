# DEPLOYMENT CHECKLIST - RBAC & DUAL-NASABAH

**Version**: 1.0
**Date**: November 27, 2025
**Environment**: Staging → Production

---

## 📋 PRE-DEPLOYMENT

### Step 1: Review & Testing (1-2 days)
- [ ] Review all 13 new files
- [ ] Review all 3 modified files
- [ ] Run all unit tests locally
- [ ] Run integration tests locally
- [ ] Verify all 6 migrations execute cleanly
- [ ] Verify all 119 permissions are seeded correctly
- [ ] Verify controller integration patterns
- [ ] Get approval from team lead

### Step 2: Documentation Review
- [ ] Read 00_IMPLEMENTATION_READY.md
- [ ] Read RBAC_IMPLEMENTATION_COMPLETED.md
- [ ] Read CONTROLLER_INTEGRATION_GUIDE.md
- [ ] Read API_RESPONSE_DOCUMENTATION.md
- [ ] Ensure team understands RBAC model
- [ ] Ensure team understands dual-nasabah model
- [ ] Share documentation with team

### Step 3: Backup & Prepare
- [ ] Create backup of production database
- [ ] Verify backup is valid
- [ ] Create deployment branch: `deploy/rbac-dual-nasabah`
- [ ] Tag release: `v1.0.0-rbac-dual-nasabah`
- [ ] Document rollback procedure
- [ ] Prepare rollback scripts

---

## 🧪 STAGING DEPLOYMENT (2-3 days)

### Step 4: Deploy to Staging
```bash
# Pull latest code on staging server
git pull origin deploy/rbac-dual-nasabah

# Install dependencies if needed
composer install

# Run migrations
php artisan migrate --force

# Seed RBAC data
php artisan db:seed --class=RolePermissionSeeder

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

- [ ] Confirm all migrations executed
- [ ] Confirm all seeders completed
- [ ] Check storage/logs for errors

### Step 5: Verify Database Changes
```bash
# Login to staging database
mysql -u user -p database_name

# Verify tables created
SHOW TABLES LIKE 'roles';  # Should exist
SHOW TABLES LIKE 'role_permissions';  # Should exist
SHOW TABLES LIKE 'audit_logs';  # Should exist

# Count records
SELECT COUNT(*) FROM roles;  # Should be 3
SELECT COUNT(*) FROM role_permissions;  # Should be 119
SELECT COUNT(*) FROM audit_logs;  # Should be 0 (initially)

# Check user columns
DESCRIBE users;  # Should show role_id, tipe_nasabah, etc
```

- [ ] All tables created
- [ ] All columns added
- [ ] All records seeded
- [ ] Existing data preserved

### Step 6: Test RBAC System
```php
// Via tinker or test endpoint

// Test roles created
$roles = Role::all();  // Should be 3
$nasabah = Role::where('nama_role', 'nasabah')->first();

// Test permissions seeded
$perms = $nasabah->permissions()->count();  // Should be 17

// Test inherited permissions
$admin = Role::where('nama_role', 'admin')->first();
$adminPerms = $admin->getInheritedPermissions();  // Should be 40

// Test user role assignment
$user = User::find(1);
$user->role_id = $nasabah->id;
$user->tipe_nasabah = 'konvensional';
$user->save();

// Test hasRole method
$user->hasRole('nasabah');  // true
$user->hasRole('admin');  // false

// Test hasPermission method
$user->hasPermission('deposit_sampah');  // true
$user->hasPermission('approve_deposit');  // false

// Test dual-nasabah
$user->isNasabahKonvensional();  // true
$user->getDisplayedPoin();  // Should show total_poin
```

- [ ] Roles query correctly
- [ ] Permissions query correctly
- [ ] Role assignment works
- [ ] User methods work
- [ ] Dual-nasabah logic works

### Step 7: Test Middleware
```bash
# Setup test user
curl -X POST http://staging.example.com/api/login \
  -d 'email=test@example.com&password=password' \
  > TOKEN

# Test permission middleware (should pass)
curl -X GET http://staging.example.com/api/profile \
  -H "Authorization: Bearer $TOKEN"
# Expected: 200 OK

# Test permission middleware (should fail - missing permission)
curl -X POST http://staging.example.com/api/admin/deposits/1/approve \
  -H "Authorization: Bearer $TOKEN_NASABAH"
# Expected: 403 Forbidden
```

- [ ] Middleware blocks unauthorized requests
- [ ] Middleware allows authorized requests
- [ ] Error responses are correct
- [ ] Status codes are correct

### Step 8: Test Feature Access Controls
```bash
# Test modern nasabah cannot withdraw
curl -X POST http://staging.example.com/api/withdrawals \
  -H "Authorization: Bearer $TOKEN_MODERN" \
  -d '{"jumlah_poin": 100, ...}'
# Expected: 403 MODERN_NASABAH_BLOCKED

# Test konvensional nasabah CAN withdraw
curl -X POST http://staging.example.com/api/withdrawals \
  -H "Authorization: Bearer $TOKEN_KONV" \
  -d '{"jumlah_poin": 100, ...}'
# Expected: 200 OK (after integration)
```

- [ ] Modern nasabah blocked from withdrawal
- [ ] Modern nasabah blocked from redemption
- [ ] Konvensional nasabah can access features
- [ ] Feature access returns correct error messages
- [ ] Feature access returns correct error codes

### Step 9: Test Audit Logging (after controller integration)
```bash
# Generate an admin action
curl -X POST http://staging.example.com/api/admin/deposits/1/approve \
  -H "Authorization: Bearer $TOKEN_ADMIN" \
  -d '{"reason": "Looks good"}'

# Verify audit log created
mysql> SELECT * FROM audit_logs WHERE resource_type='TabungSampah';
# Should show entry with:
# - admin_id filled
# - action_type = 'approve'
# - resource_type = 'TabungSampah'
# - old_values populated
# - new_values populated
# - reason filled
# - ip_address populated
# - user_agent populated
# - status = 'success'
```

- [ ] Audit logs created for admin actions
- [ ] All fields populated correctly
- [ ] IP address captured
- [ ] User agent captured
- [ ] Before/after values captured

### Step 10: Integration Testing (if controllers updated)
- [ ] Test deposit flow (both nasabah types)
- [ ] Test withdrawal flow (konvensional only)
- [ ] Test redemption flow (konvensional only)
- [ ] Test admin approval flow with audit logging
- [ ] Test admin rejection flow with audit logging
- [ ] Test poin tracking (third column)
- [ ] Verify poin_info in responses
- [ ] Verify feature access messages
- [ ] Verify error messages
- [ ] Verify API response formats

### Step 11: Performance Testing
```bash
# Check slow queries
mysql> SHOW VARIABLES LIKE 'long_query_time';
# SET GLOBAL long_query_time = 2;

# Monitor query performance
mysql> SELECT SUM(timer_wait) FROM performance_schema.events_statements_summary_by_host_by_event_name;

# Check middleware overhead
curl -X GET http://staging.example.com/api/profile \
  -H "Authorization: Bearer $TOKEN" \
  -w "Total: %{time_total}s\n"  # Should be <100ms
```

- [ ] Middleware adds minimal overhead
- [ ] Permission queries are fast (<10ms)
- [ ] Audit logging doesn't block requests
- [ ] No slow queries in logs

### Step 12: Load Testing (optional)
```bash
# Using Apache Bench or similar
ab -c 100 -n 10000 \
  -H "Authorization: Bearer $TOKEN" \
  http://staging.example.com/api/profile

# Check for connection errors
# Check for timeout errors
# Monitor database connections
```

- [ ] System handles concurrent requests
- [ ] No connection pool exhaustion
- [ ] No timeout issues
- [ ] Audit logs don't lag under load

### Step 13: Staging Sign-Off
- [ ] QA team signs off
- [ ] Business signs off
- [ ] Security team reviews RBAC model
- [ ] Performance acceptable
- [ ] Error handling correct
- [ ] Rollback procedure tested

---

## 🚀 PRODUCTION DEPLOYMENT (1-2 hours)

### Step 14: Pre-Flight Checks
```bash
# On production server, verify current state
php artisan migrate:status
# Check no migrations are pending

php artisan config:cache
php artisan route:cache

# Verify backup
mysqldump -u root -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
# Verify backup file size > 0
ls -lh backup_*.sql
```

- [ ] No pending migrations
- [ ] Configuration cached
- [ ] Routes cached
- [ ] Backup verified
- [ ] All systems healthy

### Step 15: Deploy to Production
```bash
# Pull code during maintenance window (if possible)
git pull origin deploy/rbac-dual-nasabah

# Or manually upload files to production server
# Or use CI/CD pipeline

# Run migrations
php artisan migrate --force
# Expected: 6 new migrations executed

# Seed RBAC data
php artisan db:seed --class=RolePermissionSeeder
# Expected: 119 records created

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check for errors in logs
tail -f storage/logs/laravel.log
```

- [ ] Code deployed successfully
- [ ] All migrations executed
- [ ] Seeder completed
- [ ] Caches cleared
- [ ] No errors in logs

### Step 16: Verify Production
```bash
# Check database changes
mysql -u prod_user -p prod_database <<EOF
SELECT COUNT(*) as roles_count FROM roles;
SELECT COUNT(*) as permissions_count FROM role_permissions;
SELECT COUNT(*) as audit_logs_count FROM audit_logs;
SELECT * FROM roles;
EOF
```

- [ ] All tables exist
- [ ] All data seeded
- [ ] Existing data preserved
- [ ] No data corruption

### Step 17: Production Smoke Test
```bash
# Test unauthenticated access (should fail)
curl -X GET http://example.com/api/admin/users
# Expected: 401 Unauthorized

# Test authenticated access (should work)
curl -X GET http://example.com/api/profile \
  -H "Authorization: Bearer $TOKEN"
# Expected: 200 OK

# Test permission check (should work if user has permission)
curl -X POST http://example.com/api/admin/deposits/1/approve \
  -H "Authorization: Bearer $TOKEN_ADMIN"
# Expected: 200 OK or relevant status

# Monitor error logs
tail -f storage/logs/laravel.log
# No errors expected
```

- [ ] Authentication works
- [ ] Authorization works
- [ ] Feature access works
- [ ] No errors in production logs

### Step 18: Communicate to Team
- [ ] Send deployment notification
- [ ] Share documentation links
- [ ] Provide support contact info
- [ ] Explain feature access rules
- [ ] Explain new permission model
- [ ] Provide API endpoint examples

---

## 📊 POST-DEPLOYMENT MONITORING (1 week)

### Step 19: Monitor Logs
```bash
# Watch for errors
tail -f storage/logs/laravel.log | grep ERROR

# Watch for audit logs being created
mysql> SELECT COUNT(*) FROM audit_logs;  # Should increase

# Watch for permission denials
tail -f storage/logs/laravel.log | grep "403"

# Watch for authentication issues
tail -f storage/logs/laravel.log | grep "401"
```

- [ ] No unexpected errors
- [ ] Audit logs being created
- [ ] Normal 403/401 responses (expected)
- [ ] Performance acceptable

### Step 20: Monitor Performance
```bash
# Check database size growth
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables WHERE table_schema = 'database_name'
ORDER BY size_mb DESC;

# Check slow query log
mysql> SHOW ENGINE INNODB STATUS;  # Check for locks
```

- [ ] Database size growth normal
- [ ] No unusual locking patterns
- [ ] Query performance good
- [ ] No connection pool issues

### Step 21: User Feedback
- [ ] No permission errors reported
- [ ] No feature access issues
- [ ] No data corruption
- [ ] Workflows working correctly
- [ ] Response times acceptable

### Step 22: Monitoring Dashboard Setup
- [ ] Setup audit log monitoring
- [ ] Setup permission denial monitoring
- [ ] Setup error rate monitoring
- [ ] Setup response time monitoring
- [ ] Setup database size monitoring
- [ ] Setup backup verification

---

## ⚠️ ROLLBACK PROCEDURE (If Needed)

### If Critical Issue Found

```bash
# 1. Immediately stop new deployments
# 2. Alert team

# 3. Rollback to previous version
git revert deploy/rbac-dual-nasabah

# 4. Rollback database (if migrations caused issue)
php artisan migrate:rollback

# 5. Restore from backup (if data corruption)
mysql -u root -p database_name < backup_YYYYMMDD_HHMMSS.sql

# 6. Clear caches
php artisan cache:clear

# 7. Verify system is back to normal
curl -X GET http://example.com/api/health
# Should be 200 OK

# 8. Notify team
# 9. Create incident report
# 10. Plan post-mortem
```

**Rollback Commands Ready**: ✅
**Rollback Time Estimate**: 15-30 minutes

---

## 📈 SUCCESS CRITERIA

### Before Deployment
- [x] All 6 migrations created
- [x] All 13 files created successfully
- [x] All 119 permissions seeded
- [x] All models working
- [x] All middleware working
- [x] All tests passing (local)

### After Deployment
- [ ] All 6 migrations executed on production
- [ ] All 119 permissions in production database
- [ ] All tables created/enhanced correctly
- [ ] Existing data preserved
- [ ] No data corruption
- [ ] RBAC system functioning
- [ ] Feature access controls working
- [ ] Audit logging working
- [ ] No performance degradation
- [ ] No error spike
- [ ] User workflows unchanged
- [ ] User can still login
- [ ] User can still perform existing features
- [ ] Admin can approve/reject
- [ ] Superadmin can manage admins
- [ ] Modern nasabah blocked from withdrawal/redemption
- [ ] Konvensional nasabah can withdraw/redeem
- [ ] Poin tracked correctly for both types
- [ ] Badge progress works
- [ ] Leaderboard works

---

## 🎯 TIMELINE

| Phase | Duration | Status |
|-------|----------|--------|
| Pre-Deployment Review | 1-2 days | Pending |
| Staging Deployment | 1-2 days | Pending |
| Production Deployment | 1-2 hours | Pending |
| Post-Deployment Monitoring | 1 week | Pending |
| **Total** | **10-12 days** | **Ready to Start** |

---

## 📞 SUPPORT CONTACTS

- **Team Lead**: 
- **Database Admin**: 
- **Ops/Devops**: 
- **QA Lead**: 
- **Product Manager**: 

---

## ✅ FINAL SIGN-OFF

### Pre-Deployment
- [ ] Dev Lead approval
- [ ] QA Lead approval
- [ ] Ops Lead approval

### Post-Deployment
- [ ] Product Manager verification
- [ ] Business stakeholder verification
- [ ] Customer success notification

---

**Status**: 🟢 **READY FOR DEPLOYMENT**

**Next Step**: Coordinate with team for staging deployment! 🚀

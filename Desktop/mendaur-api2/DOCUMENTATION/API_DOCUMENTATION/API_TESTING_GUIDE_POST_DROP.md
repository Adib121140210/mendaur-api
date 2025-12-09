# 🚀 API TESTING GUIDE - POST DROP TABLES
**Status**: Ready to Test ✅  
**Database Verification**: ✅ PASSED  
**Next Step**: Test API Endpoints

---

## ⚡ QUICK START

### Option 1: Automatic Testing (Fastest)
```bash
# Terminal 1 - Start server
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
php artisan serve

# Terminal 2 - Run tests
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
php test_api_endpoints.php
```

### Option 2: Manual Testing with Postman
1. Import API collection (if available)
2. Start server: `php artisan serve`
3. Test endpoints listed below
4. Check responses for 200/401 status codes

### Option 3: Manual Testing with cURL
```bash
# Start server first
php artisan serve

# Test endpoints
curl -i http://localhost:8000/api/health
curl -i http://localhost:8000/api/categories
curl -i http://localhost:8000/api/products
```

---

## 📊 TEST ENDPOINTS

### Category: Basic Health Check

#### GET `/api/health`
```
Purpose: Verify server is running
Expected Status: 200 OK
Expected Response: { "status": "ok" } or similar
Command: curl -i http://localhost:8000/api/health
```

### Category: Public Endpoints (No Auth Required)

#### GET `/api/categories`
```
Purpose: Get waste categories
Expected Status: 200 OK
Expected Response: Array of categories
Command: curl -i http://localhost:8000/api/categories
```

#### GET `/api/products`
```
Purpose: Get exchange products
Expected Status: 200 OK
Expected Response: Array of products
Command: curl -i http://localhost:8000/api/products
```

#### GET `/api/badges`
```
Purpose: Get available badges
Expected Status: 200 OK
Expected Response: Array of badge definitions
Command: curl -i http://localhost:8000/api/badges
```

#### GET `/api/leaderboard` (or `/api/rankings`)
```
Purpose: Get points leaderboard
Expected Status: 200 OK or 401 (auth required)
Expected Response: Array of users ranked by points
Note: This is calculated (not table-based)
Command: curl -i http://localhost:8000/api/leaderboard
```

### Category: Authenticated Endpoints (Auth Token Required)

#### GET `/api/user/profile`
```
Purpose: Get current user profile
Expected Status: 401 Unauthorized (without token) or 200 OK (with token)
Expected Response: User data or "Unauthenticated" message
Command: curl -i http://localhost:8000/api/user/profile
Note: Add header: Authorization: Bearer TOKEN_HERE
```

#### GET `/api/user/points`
```
Purpose: Get user's current points
Expected Status: 401 or 200 (depends on auth)
Expected Response: { "points": 123 } or similar
Command: curl -i http://localhost:8000/api/user/points
```

#### GET `/api/user/badges`
```
Purpose: Get user's earned badges
Expected Status: 401 or 200
Expected Response: Array of earned badges
Command: curl -i http://localhost:8000/api/user/badges
```

#### GET `/api/user/transactions`
```
Purpose: Get user's transaction history
Expected Status: 401 or 200
Expected Response: Array of transactions
Command: curl -i http://localhost:8000/api/user/transactions
```

---

## 🔍 WHAT TO LOOK FOR

### Success Indicators ✅
- [ ] Server starts without errors: `php artisan serve`
- [ ] At least 3 public endpoints return 200 OK
- [ ] Authenticated endpoints return 401 (not 500 errors)
- [ ] No error messages about dropped tables
- [ ] No error messages about missing columns
- [ ] No database connection errors
- [ ] Response times < 1 second (normally)

### Error Indicators ❌
- [ ] 500 Internal Server Error (database issues)
- [ ] 503 Service Unavailable (connection issues)
- [ ] Error messages mentioning: "cache", "jobs", "sessions table"
- [ ] "Table not found" or "Unknown column" errors
- [ ] "SQLSTATE" database errors

---

## 📝 TESTING PROCEDURES

### Procedure 1: Quick Health Check (2 minutes)
```bash
# Terminal 1
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api
php artisan serve

# Wait for "Server running" message
# Note the URL (usually http://127.0.0.1:8000)

# Terminal 2 (while server is running)
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api

# Test 1
curl -i http://127.0.0.1:8000/api/health
# Expected: 200 OK

# Test 2
curl -i http://127.0.0.1:8000/api/categories
# Expected: 200 OK

# Test 3
curl -i http://127.0.0.1:8000/api/products
# Expected: 200 OK

# Test 4
curl -i http://127.0.0.1:8000/api/user/profile
# Expected: 401 Unauthorized (not 500 error)

echo "If all 4 above show correct status codes, API is working!"
```

### Procedure 2: Comprehensive Testing (5 minutes)
```bash
# Start server (Terminal 1)
php artisan serve

# Run all tests (Terminal 2)
php test_api_endpoints.php
# (if test_api_endpoints.php exists)

# OR manually test each endpoint
for endpoint in "health" "categories" "products" "badges" "leaderboard"
do
  echo "Testing: /api/$endpoint"
  curl -i http://127.0.0.1:8000/api/$endpoint
  echo ""
  sleep 1
done
```

### Procedure 3: Log Monitoring (During Testing)
```bash
# Terminal 3 (while server is running)
cd c:\Users\Adib\OneDrive\Desktop\mendaur-api

# Watch logs in real-time
tail -f storage/logs/laravel.log

# Look for:
# ✅ Normal requests
# ❌ Any database errors
# ❌ Any missing table errors
```

---

## 🧪 EXPECTED RESPONSES

### Successful Response (200 OK)
```
HTTP/1.1 200 OK
Content-Type: application/json
{
  "status": "success",
  "data": [...]
}
```

### Authentication Required (401)
```
HTTP/1.1 401 Unauthorized
Content-Type: application/json
{
  "message": "Unauthenticated"
}
```

### Server Error (500) - INDICATES PROBLEM ❌
```
HTTP/1.1 500 Internal Server Error
Content-Type: application/json
{
  "message": "Server Error",
  "error": "Table not found..." or similar
}
```

---

## 🔧 TROUBLESHOOTING

### Issue: Server won't start
```bash
# Check port 8000 is available
netstat -ano | findstr :8000

# If in use, stop conflicting process
taskkill /PID [PID] /F

# Or use different port
php artisan serve --port=8001
```

### Issue: 500 Errors in API responses
```bash
# Check logs
tail storage/logs/laravel.log

# Look for:
- "Table not found" → Database issue
- "SQLSTATE" → Database connection issue
- Check if critical table exists
  php verify_database_direct.php
```

### Issue: Cannot connect to database
```bash
# Verify MySQL is running
# Verify .env settings are correct
cat .env | grep DB_

# Test connection
php verify_database_direct.php
```

### Issue: 401 errors on public endpoints
```bash
# The endpoint might require authentication
# Check API documentation
# Or add auth token:
curl -i -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/endpoint
```

---

## 📊 TESTING CHECKLIST

### Pre-Testing
- [ ] Database verification passed (✅ verified earlier)
- [ ] 24 tables exist
- [ ] All critical tables present
- [ ] Backup created (if needed)

### During Testing
- [ ] Server starts without errors
- [ ] At least 3 endpoints tested
- [ ] No 500 errors encountered
- [ ] No database errors in logs
- [ ] Response times reasonable

### Post-Testing
- [ ] Document results
- [ ] Note any issues
- [ ] Check application logs
- [ ] Prepare report

---

## ✅ SUCCESS CRITERIA

Testing is successful if:
- ✅ Server starts: `php artisan serve` runs without errors
- ✅ Health endpoint returns 200: `/api/health`
- ✅ Public endpoints work: `/api/categories`, `/api/products`
- ✅ No 500 errors in responses
- ✅ No database errors in logs
- ✅ Authenticated endpoints return 401 (expected) not 500
- ✅ No error messages about dropped tables
- ✅ Response time reasonable (< 1 second)

---

## 📋 TESTING REPORT TEMPLATE

```
API TESTING REPORT - POST DROP TABLES

Date/Time: ____________
Tester: ________________

SERVER STATUS:
✅ Server started successfully: php artisan serve
✅ Port: 8000 (or ______)
✅ No startup errors

ENDPOINT TESTS:
✅ /api/health - Status: 200 OK
✅ /api/categories - Status: 200 OK
✅ /api/products - Status: 200 OK
✅ /api/badges - Status: 200 OK
✅ /api/user/profile - Status: 401 Unauthorized (expected)

DATABASE ERRORS:
✅ No database errors in logs
✅ No "table not found" errors
✅ No "unknown column" errors
✅ No connection errors

CONCLUSION:
✅ API is working correctly after drop!
✅ No issues encountered

Next: Full regression testing / Deploy to production
```

---

## 🚀 NEXT STEPS

1. **If all tests pass** ✅
   - Document results
   - Proceed with regression testing
   - Prepare for production deployment

2. **If any test fails** ❌
   - Review logs: `storage/logs/laravel.log`
   - Identify root cause
   - Either fix issue or rollback database

3. **Rollback (if needed)**
   ```bash
   # Restore from backup
   mysql -h localhost -u root mendaur_api < backup_before_drop.sql
   
   # Verify
   php verify_database_direct.php
   ```

---

**Status**: Ready to Test ✅  
**Database**: Verified ✅  
**Backup Available**: Yes ✅  
**Estimated Time**: 5 minutes

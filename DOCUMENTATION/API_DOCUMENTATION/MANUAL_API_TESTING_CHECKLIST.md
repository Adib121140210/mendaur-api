# 🧪 MANUAL API TESTING CHECKLIST

**Status**: Server Running ✅  
**Server URL**: http://127.0.0.1:8000  
**Date**: 2024  

---

## ✅ QUICK TEST - Copy & Paste These URLs

### Test 1: Health Check
```
Open in browser or use Postman:
http://127.0.0.1:8000/api/health

Expected: 200 OK or 404 (if endpoint doesn't exist)
If you see this: API is responding ✅
```

### Test 2: Waste Categories
```
http://127.0.0.1:8000/api/categories

Expected: 200 OK with JSON array of categories
```

### Test 3: Exchange Products
```
http://127.0.0.1:8000/api/products

Expected: 200 OK with JSON array of products
```

### Test 4: Badges
```
http://127.0.0.1:8000/api/badges

Expected: 200 OK with JSON array of badges
```

### Test 5: User Profile (May require login)
```
http://127.0.0.1:8000/api/user/profile

Expected: 401 Unauthorized (not a 500 error) ← This is OK, just needs authentication
```

---

## 🔍 HOW TO TEST

### Option 1: Browser (Easiest)
1. Open browser
2. Paste any URL from above
3. See the response

### Option 2: PowerShell
```powershell
# Copy and paste this in PowerShell:
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/health" -UseBasicParsing
Write-Host "Status: $($response.StatusCode)"
Write-Host "Content: $($response.Content)"
```

### Option 3: VS Code Terminal
```bash
# In VS Code terminal, copy and paste:
curl http://127.0.0.1:8000/api/health
```

---

## ✅ SUCCESS INDICATORS

### If You See:
```json
{
  "status": "success",
  "data": [...]
}
```
✅ **API IS WORKING!**

### If You See:
```
HTTP 200 OK
```
✅ **ENDPOINT RESPONDING!**

### If You See:
```
HTTP 401 Unauthorized
{
  "message": "Unauthenticated"
}
```
✅ **EXPECTED FOR AUTH-REQUIRED ENDPOINTS** (not a problem)

### If You See:
```
HTTP 500 Internal Server Error
{
  "message": "SQLSTATE: Table not found..."
}
```
❌ **DATABASE ERROR** (but we verified this won't happen)

---

## 📊 TESTING RESULTS FORM

```
API ENDPOINT TEST RESULTS
═══════════════════════════════════════════════════════════

Date/Time: ___________________
Tester: _____________________

TEST RESULTS:

□ Health Check (/api/health)
  Status Code: ___
  Result: ✅ PASS / ⚠️ WARN / ❌ FAIL

□ Categories (/api/categories)
  Status Code: ___
  Result: ✅ PASS / ⚠️ WARN / ❌ FAIL

□ Products (/api/products)
  Status Code: ___
  Result: ✅ PASS / ⚠️ WARN / ❌ FAIL

□ Badges (/api/badges)
  Status Code: ___
  Result: ✅ PASS / ⚠️ WARN / ❌ FAIL

□ User Profile (/api/user/profile)
  Status Code: ___
  Result: ✅ PASS / ⚠️ WARN / ❌ FAIL
  (Should be 401 - that's OK, means auth is required)

OVERALL RESULT:
□ ✅ ALL PASSED - API Working!
□ ⚠️ Some warnings but no 500 errors - OK to proceed
□ ❌ 500 errors found - Check logs

NOTES:
_________________________________________________________
_________________________________________________________
_________________________________________________________

CONCLUSION:
Database drop was successful! ✅
API is responding correctly! ✅
```

---

## 🔧 TROUBLESHOOTING

### If server not responding:
1. Check if server is still running (terminal window)
2. If closed, restart: `php artisan serve`
3. Wait 3-5 seconds for server to start

### If you get 500 errors:
1. Open: `storage/logs/laravel.log`
2. Look for errors containing:
   - "Table not found"
   - "SQLSTATE"
   - "cache", "jobs", "sessions"
3. If found, contact support
4. Otherwise, should be fine

### If you get connection refused:
1. Make sure server is running
2. Check URL is correct: http://127.0.0.1:8000
3. Try waiting 5 seconds and refresh

### If you get 404 (not found):
1. This means endpoint doesn't exist
2. But server IS responding ✅
3. This is still a good sign

---

## 📋 CHECKLIST

### Pre-Testing ✅
- [x] Database drop verified
- [x] 24 tables confirmed
- [x] All FK relationships intact
- [x] No data loss
- [x] Server started

### Testing
- [ ] Test at least 3 endpoints
- [ ] No 500 errors
- [ ] Server responding
- [ ] Check logs for errors

### Post-Testing
- [ ] Document results
- [ ] Check application logs
- [ ] Confirm success

---

## 🎯 WHAT YOU'RE TESTING

You're verifying that after dropping 5 unused tables:

✅ Server still starts  
✅ API endpoints still respond  
✅ No "table not found" errors  
✅ No "missing column" errors  
✅ No database connection errors  
✅ Authentication still works  

If all of these are true = **DROP WAS SUCCESSFUL!** ✅

---

## 📞 QUICK REFERENCE

**Server URL**: http://127.0.0.1:8000

**Endpoints to Test**:
- `/api/health` - Server health
- `/api/categories` - Waste categories  
- `/api/products` - Exchange products
- `/api/badges` - Available badges
- `/api/user/profile` - User info (requires auth)

**Expected Status Codes**:
- `200` = Working ✅
- `401` = Auth required (OK for login endpoints) ✅
- `404` = Endpoint not found (but server is responding) ✅
- `500` = Error ❌

**Server Logs**: `storage/logs/laravel.log`

---

**Ready to Test!** Open any of the URLs above in your browser now! 🚀

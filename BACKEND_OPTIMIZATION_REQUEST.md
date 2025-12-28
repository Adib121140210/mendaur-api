# 🚀 Backend Performance Optimization Request

**Date:** December 28, 2025  
**Priority:** HIGH  
**Status:** Blocking Production Performance  

---

## 📋 Executive Summary

Frontend telah dioptimasi dengan PWA, caching, dan lazy loading. Namun, beberapa endpoint backend mengambil **>5 detik** untuk respond, menyebabkan **timeout dan poor user experience** di production.

Kami perlu bantuan backend team untuk mengoptimasi query dan response time.

---

## 🔴 Critical Issues

### 1. Dashboard Stats - Timeout di >5 detik
**Endpoint:** `GET /api/dashboard/stats/{userId}`  
**Current:** ⏱️ >5000ms  
**Expected:** <1000ms  

**Browser Console Error:**
```
Request timeout for https://mendaur.up.railway.app/api/dashboard/stats/7
```

**User Impact:** Home dashboard tidak menampilkan stats (poin, level, badges count)

---

### 2. User Badges - Timeout di >5 detik
**Endpoint:** `GET /api/users/{userId}/badges`  
**Current:** ⏱️ >5000ms  
**Expected:** <2000ms  

**Browser Console Error:**
```
Request timeout for https://mendaur.up.railway.app/api/users/7/badges
```

**User Impact:** Badge list tidak muncul di home dashboard

---

### 3. User Activities - 404 Error
**Endpoint:** `GET /api/penukaran-produk/user/{userId}`  
**Endpoint:** `GET /api/penarikan-tunai/user/{userId}`  
**Endpoint:** `GET /api/users/{userId}/aktivitas`  
**Current:** ❌ 404 Not Found atau timeout  
**Expected:** <2000ms dengan data  

**Browser Console Error:**
```
GET https://mendaur.up.railway.app/api/penukaran-produk/user/7 404 (Not Found)
GET https://mendaur.up.railway.app/api/penarikan-tunai/user/7 404 (Not Found)
GET https://mendaur.up.railway.app/api/users/7/aktivitas 404 (Not Found)
```

**User Impact:** Recent activities tidak ditampilkan

---

## 🔍 Root Cause Analysis

Frontend sudah coba optimize:
- ✅ Added 5-second timeout dengan fallback to cache
- ✅ Parallel loading dengan Promise.allSettled
- ✅ PWA caching untuk API responses
- ✅ Lazy loading pages untuk initial load cepat

**Tapi endpoint backend masih lambat/error, berarti masalah di:**
1. ❌ Database queries tidak efisien
2. ❌ Missing database indexes
3. ❌ N+1 queries di endpoint
4. ❌ Endpoint belum implemented
5. ❌ Server resource limitation

---

## 📊 Performance Targets

| Endpoint | Current | Target | Notes |
|----------|---------|--------|-------|
| `/api/dashboard/stats/{userId}` | >5000ms | <1000ms | Critical |
| `/api/users/{userId}/badges` | >5000ms | <2000ms | High |
| `/api/penukaran-produk/user/{userId}` | 404 | <2000ms | Must implement |
| `/api/penarikan-tunai/user/{userId}` | 404 | <2000ms | Must implement |
| `/api/users/{userId}/aktivitas` | 404 | <2000ms | Must implement |
| `/api/dashboard/leaderboard` | - | <2000ms | High |
| `/api/profile` | - | <1000ms | Critical |

---

## 🛠️ Recommended Solutions

### A. For Dashboard Stats (Slow)

**Current Problem:** Likely complex JOIN queries with multiple related tables

**Solutions:**
1. **Add Database Indexes:**
   ```sql
   -- For user-specific queries
   ALTER TABLE tabung_sampah ADD INDEX idx_user_id (user_id);
   ALTER TABLE penukaran_produk ADD INDEX idx_user_id (user_id);
   ALTER TABLE penarikan_tunai ADD INDEX idx_user_id (user_id);
   ALTER TABLE log_user_activity ADD INDEX idx_user_id (user_id);
   ALTER TABLE badges ADD INDEX idx_user_id (user_id);
   ```

2. **Optimize Query:**
   - Use SELECT only needed columns, not *
   - Use eager loading (prevent N+1 queries)
   - Cache frequently accessed stats (e.g., user level)
   - Consider materialized views for aggregated stats

3. **Sample Optimized Query:**
   ```php
   // BEFORE: Slow
   $user = User::with('badges', 'activities', 'transactions')->find($userId);
   
   // AFTER: Optimized
   $stats = DB::table('users as u')
       ->select(
           'u.id',
           'u.nama',
           'u.total_poin',
           DB::raw('COUNT(DISTINCT b.badge_id) as badge_count'),
           DB::raw('COUNT(DISTINCT t.tabung_id) as tabung_count')
       )
       ->leftJoin('badges as b', 'u.id', '=', 'b.user_id')
       ->leftJoin('tabung_sampah as t', 'u.id', '=', 't.user_id')
       ->where('u.id', $userId)
       ->groupBy('u.id', 'u.nama', 'u.total_poin')
       ->first();
   ```

---

### B. For Missing Endpoints (404)

**Current Problem:** Endpoints not implemented or route not registered

**Must Implement:**
1. `GET /api/penukaran-produk/user/{userId}` - Product redemption history
2. `GET /api/penarikan-tunai/user/{userId}` - Cash withdrawal history
3. `GET /api/users/{userId}/aktivitas` - Activity log (or use as fallback)

**Sample Implementation (Laravel):**
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // User Activities
    Route::get('/users/{id}/aktivitas', [ActivityController::class, 'userActivities']);
    Route::get('/penukaran-produk/user/{userId}', [RedemptionController::class, 'userHistory']);
    Route::get('/penarikan-tunai/user/{userId}', [WithdrawalController::class, 'userHistory']);
});

// app/Http/Controllers/ActivityController.php
public function userActivities($userId)
{
    return response()->json([
        'data' => Log::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
    ]);
}
```

---

### C. For Leaderboard & Badges (Potentially Slow)

**Optimization:**
```php
// Cache leaderboard for 1 hour
Route::get('/dashboard/leaderboard', function () {
    return Cache::remember('leaderboard:monthly', 3600, function () {
        return DB::table('users')
            ->select('id', 'nama', 'total_poin', 'level')
            ->orderBy('total_poin', 'desc')
            ->limit(100)
            ->get();
    });
});

// Cache user badges
Route::get('/users/{id}/badges', function ($id) {
    return Cache::remember("user:$id:badges", 3600, function () use ($id) {
        return Badge::whereHas('users', function ($q) use ($id) {
            $q->where('users.id', $id);
        })->get();
    });
});
```

---

## 🔧 Optimization Checklist

Backend team harus memastikan:

- [ ] **Database Indexes:** All user-related columns indexed
- [ ] **Query Optimization:** No SELECT *, use eager loading
- [ ] **Caching:** Frequently accessed data cached (Redis/Memcached)
- [ ] **Connection Pooling:** Database connection pool configured
- [ ] **API Response:** Pagination for large datasets
- [ ] **Missing Endpoints:** Implement all 3 activity endpoints
- [ ] **Error Handling:** Proper 404/500 responses, not timeouts
- [ ] **Load Testing:** Test with production data volume

---

## 📈 Monitoring & Validation

After optimization, backend team should:

1. **Add Query Logging:**
   ```php
   // In local development
   DB::listen(function ($query) {
       Log::debug('Query', [
           'time' => $query->time,
           'sql' => $query->sql
       ]);
   });
   ```

2. **Monitor Response Times:**
   - Use Laravel Telescope / New Relic
   - Set up alerts for >2s responses

3. **Performance Testing:**
   ```bash
   # Test endpoint response time
   time curl https://mendaur.up.railway.app/api/dashboard/stats/7 \
     -H "Authorization: Bearer token"
   ```

4. **Load Testing:**
   ```bash
   # Test with multiple concurrent users
   ab -n 100 -c 10 https://mendaur.up.railway.app/api/dashboard/stats/7
   ```

---

## 📞 Frontend Context

**Frontend Current State:**
- ✅ Deployed to Railway: https://sedulurmendaur-production.up.railway.app
- ✅ PWA with service worker caching
- ✅ 5-second timeout with fallback
- ✅ Parallel loading dengan Promise.allSettled
- ✅ Lazy loaded pages (initial bundle: ~62KB gzipped)

**Frontend Time Budget:**
- Initial page load: <3s (gated by backend)
- Dashboard stats: <1s (current: timeout)
- Navigation between pages: <1s (local, instant with cache)

---

## 🎯 Success Criteria

After optimization, these metrics should be met:

```javascript
// Frontend monitoring (from browser console)
console.log({
  "dashboard_stats_response_time": "< 1000ms",  // ❌ Current: >5000ms
  "user_badges_response_time": "< 2000ms",      // ❌ Current: >5000ms
  "activities_response_time": "< 2000ms",       // ❌ Current: 404
  "profile_response_time": "< 1000ms",          // Need to verify
  "initial_page_load": "< 3000ms",              // Depends on stats
  "repeat_visit": "< 500ms"                     // With cache
});
```

---

## 📝 Deliverables for Backend

Please provide:
1. ✅ Estimated completion date for each optimization
2. ✅ Query execution plans showing before/after
3. ✅ Response time measurements after optimization
4. ✅ Implementation details for missing endpoints
5. ✅ Cache strategy for frequently accessed data
6. ✅ Load testing results

---

## 📋 Escalation Path

If backend optimization takes time:
- **Short term:** Frontend will increase timeout from 5s to 10s (bad UX)
- **Medium term:** Implement GraphQL for flexible queries (better than REST)
- **Long term:** Migrate heavy operations to async jobs

---

## 🔗 Related Frontend Changes

Frontend already completed:
1. **Commit 957d58f:** Fix double /api/ prefix bugs
2. **Commit c0d9f6c:** Add PWA + lazy loading
3. **Current:** Timeout protection + caching

Backend is the bottleneck. Let's fix it! 🚀

---

## 📞 Contact

**Frontend Lead:** [Your name]  
**Date:** December 28, 2025  
**Status:** Awaiting backend team response

---

**Template for backend team response:**
```
Status: [Not Started / In Progress / Completed]
ETA: [Date]
Details:
- Query optimization: [%]
- Index addition: [%]
- Cache implementation: [%]
- Endpoint implementation: [%]

Blockers: [None / List issues]
```

# 🚨 Backend Performance Issue - Response Time >4 Detik

**Priority:** HIGH  
**Date:** December 28, 2025  
**Status:** Production Performance Degradation  

---

## 📋 Executive Summary

Backend API endpoints mengalami **response time >4 detik**, menyebabkan:
- ❌ **499 errors** (Client Closed Request) di production logs
- 😞 Poor user experience (loading lama)
- ⚠️ Frontend terpaksa meningkatkan timeout dari 5s → 15s (temporary workaround)

**Idealnya:** Semua endpoint harus merespons dalam **<2 detik**

---

## 🔴 Problematic Endpoints

Berdasarkan Railway production logs:

| Endpoint | Status | Response Time | Expected |
|----------|--------|---------------|----------|
| `GET /api/users/{userId}/badge-title` | 499 | **4s** | <1s |
| `GET /api/dashboard/stats/{userId}` | 499 | **4s** | <1s |
| `GET /api/users/{userId}/badges` | 499 | **4s** | <2s |
| `GET /api/dashboard/leaderboard` | 499 | **4s** | <2s |
| `GET /api/setor-sampah/user/{userId}` | 200 | 3s | <2s |

**Status Code 499** = Client menutup koneksi karena timeout sebelum backend selesai merespons.

---

## 🔍 Root Cause Analysis (Kemungkinan)

### 1. **N+1 Query Problem**

**Contoh masalah:**
```php
// ❌ BAD: N+1 queries
$users = User::all(); // 1 query
foreach ($users as $user) {
    $badges = $user->badges; // N queries (query per user)
    $activities = $user->activities; // N queries lagi
}
// Total: 1 + 2N queries (jika 100 users = 201 queries!)

// ✅ GOOD: Eager Loading
$users = User::with('badges', 'activities')->get(); // 3 queries total
```

**Solusi:** Gunakan `with()` atau `load()` untuk eager loading.

---

### 2. **Missing Database Indexes**

**Queries yang sering dipakai tapi mungkin belum punya index:**
```sql
-- Queries yang lambat tanpa index:
SELECT * FROM badges WHERE user_id = ?;
SELECT * FROM tabung_sampah WHERE user_id = ?;
SELECT * FROM penukaran_produk WHERE user_id = ?;
SELECT * FROM penarikan_tunai WHERE user_id = ?;
SELECT * FROM log_user_activity WHERE user_id = ? ORDER BY created_at DESC;

-- Tambahkan indexes:
ALTER TABLE badges ADD INDEX idx_user_id (user_id);
ALTER TABLE tabung_sampah ADD INDEX idx_user_id (user_id);
ALTER TABLE penukaran_produk ADD INDEX idx_user_id (user_id);
ALTER TABLE penarikan_tunai ADD INDEX idx_user_id (user_id);
ALTER TABLE log_user_activity ADD INDEX idx_user_created (user_id, created_at);

-- Untuk leaderboard (sorting by points):
ALTER TABLE users ADD INDEX idx_total_poin (total_poin DESC);
```

**Cara cek slow queries:**
```sql
-- Enable slow query log di MySQL
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1; -- Log queries >1s

-- Lihat queries yang lambat
SHOW PROCESSLIST;
```

---

### 3. **Tidak Ada Caching**

**Data yang seharusnya di-cache:**
- ✅ Leaderboard (update setiap 5-10 menit saja)
- ✅ User badges (jarang berubah)
- ✅ Dashboard stats (bisa cache 1-2 menit)
- ✅ Product list (jarang berubah)

**Implementasi caching dengan Redis/Laravel Cache:**
```php
use Illuminate\Support\Facades\Cache;

// ✅ Cache leaderboard untuk 10 menit
public function getLeaderboard() {
    return Cache::remember('leaderboard:top100', 600, function () {
        return User::select('id', 'nama', 'total_poin', 'level')
            ->orderBy('total_poin', 'desc')
            ->limit(100)
            ->get();
    });
}

// ✅ Cache user stats untuk 2 menit
public function getUserStats($userId) {
    return Cache::remember("user:$userId:stats", 120, function () use ($userId) {
        return [
            'user' => User::find($userId),
            'badges' => Badge::where('user_id', $userId)->get(),
            'total_setor' => TabungSampah::where('user_id', $userId)->sum('berat_sampah'),
        ];
    });
}

// ✅ Invalidate cache saat ada update
public function updateUserPoints($userId, $points) {
    // Update points
    $user = User::find($userId);
    $user->total_poin += $points;
    $user->save();
    
    // Clear cache
    Cache::forget("user:$userId:stats");
    Cache::forget('leaderboard:top100');
}
```

---

### 4. **Query Optimization**

**❌ Hindari SELECT \***
```php
// ❌ BAD: Ambil semua kolom (boros memory & bandwidth)
$users = User::all();

// ✅ GOOD: Ambil kolom yang dibutuhkan saja
$users = User::select('id', 'nama', 'total_poin', 'level')->get();
```

**❌ Hindari COUNT() di loop**
```php
// ❌ BAD: Query dalam loop
foreach ($users as $user) {
    $badgeCount = Badge::where('user_id', $user->id)->count(); // N queries
}

// ✅ GOOD: Aggregate di 1 query
$badgeCounts = Badge::select('user_id', DB::raw('COUNT(*) as badge_count'))
    ->groupBy('user_id')
    ->pluck('badge_count', 'user_id');

foreach ($users as $user) {
    $badgeCount = $badgeCounts[$user->id] ?? 0; // No query
}
```

---

## 🛠️ Action Items for Backend Team

### **Priority 1 (This Week)** ⚡

- [ ] **Enable Laravel Query Logging**
  ```php
  // Add to AppServiceProvider or create middleware
  DB::listen(function ($query) {
      if ($query->time > 1000) { // Log queries >1s
          Log::warning('Slow Query', [
              'sql' => $query->sql,
              'time' => $query->time . 'ms',
              'bindings' => $query->bindings
          ]);
      }
  });
  ```

- [ ] **Add Database Indexes** (SQL di atas)
  - user_id pada semua tabel transaksi
  - total_poin untuk leaderboard sorting

- [ ] **Fix N+1 Queries**
  - Review endpoint `/api/dashboard/stats/{userId}`
  - Review endpoint `/api/users/{userId}/badges`
  - Review endpoint `/api/dashboard/leaderboard`
  - Gunakan `with()` eager loading

### **Priority 2 (Next Week)** 🚀

- [ ] **Implement Caching**
  - Leaderboard cache (10 minutes)
  - User stats cache (2 minutes)
  - Badge list cache (5 minutes)

- [ ] **Optimize SELECT Queries**
  - Ganti `SELECT *` dengan kolom spesifik
  - Hindari query dalam loop

- [ ] **Add Response Time Monitoring**
  ```php
  // Log all API response times
  Route::middleware(['api', 'response.timer'])->group(function () {
      // Your routes
  });
  ```

---

## 📊 Performance Targets

| Metric | Current | Target | Priority |
|--------|---------|--------|----------|
| Dashboard Stats | 4000ms | **<1000ms** | Critical 🔴 |
| User Badges | 4000ms | **<2000ms** | High 🟠 |
| Leaderboard | 4000ms | **<2000ms** | High 🟠 |
| Setor Sampah List | 3000ms | **<2000ms** | Medium 🟡 |

---

## 🔧 How to Debug

### 1. **Check Slow Queries**
```bash
# SSH ke Railway container atau check logs
php artisan telescope:list  # Jika pakai Laravel Telescope

# Atau enable query log sementara
DB::enableQueryLog();
// ... run your code
dd(DB::getQueryLog());
```

### 2. **Profile with Laravel Debugbar**
```bash
composer require barryvdh/laravel-debugbar --dev
```

### 3. **Test Response Time Locally**
```bash
# Benchmark endpoint
time curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://mendaur.up.railway.app/api/dashboard/stats/7

# Load test (jika perlu)
ab -n 100 -c 10 https://mendaur.up.railway.app/api/dashboard/stats/7
```

---

## 📈 Expected Results After Optimization

**Before:**
```
GET /api/dashboard/stats/7 - 499 (4000ms) ❌
GET /api/users/7/badges - 499 (4000ms) ❌
GET /api/dashboard/leaderboard - 499 (4000ms) ❌
```

**After:**
```
GET /api/dashboard/stats/7 - 200 (800ms) ✅
GET /api/users/7/badges - 200 (1200ms) ✅
GET /api/dashboard/leaderboard - 200 (1500ms) ✅
```

**Benefits:**
- ✅ No more 499 errors
- ✅ Better UX (faster page load)
- ✅ Lower server CPU usage
- ✅ Can handle more concurrent users

---

## 🚀 Frontend Temporary Workaround

Frontend sudah meningkatkan timeout dari 5s → 15s sebagai temporary fix:
- `homeContent.jsx` - timeout 15s
- `leaderboardHeader.jsx` - timeout 15s
- `api.js` - DEFAULT_TIMEOUT 15s
- `productApi.js` - timeout 15s

**Tapi ini bukan solusi jangka panjang!** Backend tetap harus dioptimasi ke <2s.

---

## 📞 Questions to Answer

Mohon backend team bisa provide:

1. **ETA untuk optimization?** (target: week of Dec 28-Jan 3)
2. **Sudah ada indexes di tabel users, badges, tabung_sampah?**
3. **Apakah pakai eager loading (`with()`) atau masih query manual?**
4. **Sudah implementasi caching (Redis/Memcached)?**
5. **Ada tools monitoring (Telescope, Debugbar, New Relic)?**

---

## 📝 Response Template

**Mohon isi dan reply:**

```
Status: [Not Started / In Progress / Completed]
ETA: [Date - format: Jan X, 2025]

Progress:
□ Database indexes added
□ N+1 queries fixed
□ Caching implemented
□ Response time tested (<2s)

Blockers: [None / List issues]

Questions: [Any questions for frontend team?]

Notes: [Additional context]
```

---

## 🔗 References

- **Production Logs:** Railway dashboard → mendaur-api → Observability
- **Slow Query Analysis:** [Laravel Performance Best Practices](https://laravel.com/docs/10.x/queries#debugging)
- **Database Indexing:** [MySQL Index Optimization](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- **Caching Strategies:** [Laravel Caching](https://laravel.com/docs/10.x/cache)

---

## 💬 Contact

**Frontend Team Lead:** [Your Name]  
**Date Reported:** December 28, 2025  
**Urgency:** HIGH - Affecting production user experience

**Let's collaborate to make this faster! 🚀**

---

**P.S.** Frontend sudah complete optimization di sisi kami:
- ✅ PWA with service worker caching
- ✅ Lazy loading components
- ✅ Code splitting
- ✅ Image optimization (Cloudinary)
- ✅ Timeout protection (5s → 15s)

Sekarang giliran backend untuk optimize! 💪

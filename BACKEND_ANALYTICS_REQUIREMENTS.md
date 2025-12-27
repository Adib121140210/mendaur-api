# 📊 Backend Analytics API Requirements
**Project**: Mendaur TA - Waste Management System  
**Date**: December 24, 2025  
**Frontend Repository**: [mendaur-frontend](https://github.com/Adib121140210/mendaur-frontend)

---

## 🎯 **Executive Summary**

Frontend telah mengimplementasikan 3 halaman analytics dengan API endpoints yang sudah ada, namun memerlukan optimasi backend untuk:
1. **Database Query Optimization** - SQL aggregation yang efisien
2. **Response Format Standardization** - Struktur data yang konsisten  
3. **Business Logic Implementation** - Perhitungan growth percentage dan metrics
4. **Performance Enhancement** - Caching dan indexing untuk analytics

---

## 📋 **Current Implementation Status**

### ✅ **Frontend Ready**
- **WasteAnalytics.jsx** - Memanggil `/admin/analytics/waste`
- **PointsDistribution.jsx** - Memanggil `/admin/analytics/points`
- **WasteByUserTable.jsx** - Memanggil `/admin/analytics/waste-by-user`
- **Fallback Logic** - Mock data jika API gagal
- **Error Handling** - Authentication & network errors

### 🔧 **Backend Needs Optimization**
- SQL queries untuk aggregation
- Growth percentage calculation
- Consistent API response format
- Database indexing untuk performance

---

## 🗃️ **Database Schema Reference**

### **Tables Involved**
```sql
-- Users table
users: id, nama_lengkap, email, created_at

-- Waste deposits
setoran_sampah: id, user_id, kategori_id, berat, status, tanggal, created_at

-- Waste categories  
kategori_sampah: id, nama_kategori, poin_per_kg

-- Points history
poin_histories: id, user_id, jumlah, jenis, sumber, keterangan, created_at

-- Product redemptions
penukaran_produk: id, user_id, produk_id, jumlah_poin, status, created_at
```

---

## 🔍 **API Endpoints & Requirements**

### **1. GET `/admin/analytics/waste`**

#### **Current Frontend Call:**
```javascript
adminApi.getWasteAnalytics(period, { year, month })
// period: 'daily' | 'monthly' | 'yearly'
// filters: { year: 2025, month: 12 }
```

#### **Required SQL Queries:**
```sql
-- Total Waste & Transactions
SELECT 
    SUM(berat) as total_weight,
    COUNT(*) as total_transactions,
    AVG(berat) as average_per_transaction
FROM setoran_sampah 
WHERE status = 'approved' 
    AND YEAR(tanggal) = :year
    AND (:month IS NULL OR MONTH(tanggal) = :month);

-- Waste by Category
SELECT 
    k.nama_kategori,
    SUM(s.berat) as total_berat,
    COUNT(s.id) as jumlah,
    ROUND((SUM(s.berat) / (SELECT SUM(berat) FROM setoran_sampah WHERE status = 'approved' AND YEAR(tanggal) = :year) * 100), 2) as percentage
FROM setoran_sampah s
JOIN kategori_sampah k ON s.kategori_id = k.id
WHERE s.status = 'approved' 
    AND YEAR(s.tanggal) = :year
    AND (:month IS NULL OR MONTH(s.tanggal) = :month)
GROUP BY k.id, k.nama_kategori
ORDER BY total_berat DESC;

-- Monthly Trend (for yearly view)
SELECT 
    MONTH(tanggal) as month_num,
    DATE_FORMAT(tanggal, '%b') as month,
    SUM(berat) as total
FROM setoran_sampah 
WHERE status = 'approved' AND YEAR(tanggal) = :year
GROUP BY MONTH(tanggal)
ORDER BY MONTH(tanggal);

-- Growth Percentage (compare with previous period)
SELECT 
    current_period.total as current_total,
    previous_period.total as previous_total,
    ROUND(((current_period.total - previous_period.total) / previous_period.total * 100), 2) as growth_percentage
FROM (
    SELECT SUM(berat) as total FROM setoran_sampah 
    WHERE status = 'approved' AND YEAR(tanggal) = :year AND MONTH(tanggal) = :month
) current_period,
(
    SELECT SUM(berat) as total FROM setoran_sampah 
    WHERE status = 'approved' AND YEAR(tanggal) = :prev_year AND MONTH(tanggal) = :prev_month
) previous_period;
```

#### **Required Response Format:**
```json
{
  "success": true,
  "data": {
    "totalWaste": 245.8,
    "totalTransactions": 128,
    "averagePerTransaction": 1.92,
    "growthPercentage": 12.5,
    "byCategory": [
      {
        "nama_kategori": "Plastik",
        "total_berat": 89.2,
        "jumlah": 45,
        "percentage": 36.3
      }
    ],
    "monthlyTrend": [
      {
        "month": "Jan",
        "total": 180.5
      }
    ],
    "topContributors": [
      {
        "nama": "Ahmad Rizki",
        "total_berat": 45.5,
        "percentage": 18.5
      }
    ]
  }
}
```

---

### **2. GET `/admin/analytics/points`**

#### **Current Frontend Call:**
```javascript
adminApi.getPointsAnalytics(period, { year, month })
```

#### **Required SQL Queries:**
```sql
-- Total Points Distributed
SELECT 
    SUM(CASE WHEN jenis = 'kredit' THEN jumlah ELSE 0 END) as total_distributed,
    SUM(CASE WHEN jenis = 'debit' THEN jumlah ELSE 0 END) as total_redeemed,
    COUNT(*) as total_transactions
FROM poin_histories 
WHERE YEAR(created_at) = :year
    AND (:month IS NULL OR MONTH(created_at) = :month);

-- Points by Source
SELECT 
    sumber,
    SUM(jumlah) as total_poin,
    COUNT(*) as jumlah_transaksi
FROM poin_histories 
WHERE jenis = 'kredit' 
    AND YEAR(created_at) = :year
    AND (:month IS NULL OR MONTH(created_at) = :month)
GROUP BY sumber;

-- Monthly Distribution
SELECT 
    MONTH(created_at) as month_num,
    DATE_FORMAT(created_at, '%b') as month,
    SUM(CASE WHEN jenis = 'kredit' THEN jumlah ELSE 0 END) as total
FROM poin_histories 
WHERE YEAR(created_at) = :year
GROUP BY MONTH(created_at)
ORDER BY MONTH(created_at);

-- Top Users by Points
SELECT 
    u.nama_lengkap,
    u.email,
    SUM(CASE WHEN p.jenis = 'kredit' THEN p.jumlah ELSE 0 END) as total_earned,
    SUM(CASE WHEN p.jenis = 'debit' THEN p.jumlah ELSE 0 END) as total_spent,
    (SUM(CASE WHEN p.jenis = 'kredit' THEN p.jumlah ELSE 0 END) - 
     SUM(CASE WHEN p.jenis = 'debit' THEN p.jumlah ELSE 0 END)) as current_balance
FROM users u
JOIN poin_histories p ON u.id = p.user_id
WHERE YEAR(p.created_at) = :year
GROUP BY u.id
ORDER BY total_earned DESC
LIMIT 10;
```

#### **Required Response Format:**
```json
{
  "success": true,
  "data": {
    "totalDistributed": 8450,
    "totalRedeemed": 2150,
    "totalTransactions": 385,
    "bySource": {
      "Setoran Sampah": {
        "total": 4200,
        "count": 280
      },
      "Referral": {
        "total": 2100,
        "count": 70
      },
      "Bonus": {
        "total": 1050,
        "count": 35
      }
    },
    "monthlyDistribution": [
      {
        "month": "Jan",
        "total": 1800
      }
    ],
    "topUsers": [
      {
        "nama": "Ahmad Rizki",
        "email": "ahmad@example.com",
        "total_earned": 1250,
        "total_spent": 500,
        "current_balance": 750
      }
    ]
  }
}
```

---

### **3. GET `/admin/analytics/waste-by-user`**

#### **Current Frontend Call:**
```javascript
adminApi.getWasteByUser(page, limit, filters)
// page: 1, limit: 10
// filters: { year, month, search }
```

#### **Required SQL Queries:**
```sql
-- Waste by User with Pagination
SELECT 
    u.id as user_id,
    u.nama_lengkap,
    u.email,
    COUNT(s.id) as total_deposits,
    COALESCE(SUM(s.berat), 0) as total_kg,
    COALESCE(SUM(CASE WHEN s.status = 'approved' THEN s.berat * k.poin_per_kg ELSE 0 END), 0) as total_points,
    COALESCE(SUM(CASE WHEN s.status = 'pending' THEN s.berat ELSE 0 END), 0) as pending_kg,
    COALESCE(SUM(CASE WHEN s.status = 'pending' THEN s.berat * k.poin_per_kg ELSE 0 END), 0) as pending_points,
    COALESCE(SUM(CASE WHEN s.status = 'approved' THEN s.berat ELSE 0 END), 0) as approved_kg,
    COALESCE(SUM(CASE WHEN s.status = 'approved' THEN s.berat * k.poin_per_kg ELSE 0 END), 0) as approved_points,
    ROUND(AVG(s.berat), 2) as avg_per_deposit,
    MAX(s.tanggal) as last_deposit,
    (
        SELECT ks.nama_kategori 
        FROM setoran_sampah ss 
        JOIN kategori_sampah ks ON ss.kategori_id = ks.id
        WHERE ss.user_id = u.id 
        GROUP BY ks.id 
        ORDER BY COUNT(*) DESC 
        LIMIT 1
    ) as most_common_waste
FROM users u
LEFT JOIN setoran_sampah s ON u.id = s.user_id
LEFT JOIN kategori_sampah k ON s.kategori_id = k.id
WHERE u.role = 'user'
    AND (:search IS NULL OR u.nama_lengkap LIKE CONCAT('%', :search, '%') OR u.email LIKE CONCAT('%', :search, '%'))
    AND (:year IS NULL OR YEAR(s.tanggal) = :year)
    AND (:month IS NULL OR MONTH(s.tanggal) = :month)
GROUP BY u.id, u.nama_lengkap, u.email
HAVING total_deposits > 0
ORDER BY total_kg DESC
LIMIT :limit OFFSET :offset;

-- Count for Pagination
SELECT COUNT(*) as total
FROM users u
LEFT JOIN setoran_sampah s ON u.id = s.user_id
WHERE u.role = 'user'
    AND (:search IS NULL OR u.nama_lengkap LIKE CONCAT('%', :search, '%') OR u.email LIKE CONCAT('%', :search, '%'))
    AND (:year IS NULL OR YEAR(s.tanggal) = :year)
    AND (:month IS NULL OR MONTH(s.tanggal) = :month)
GROUP BY u.id
HAVING COUNT(s.id) > 0;
```

#### **Required Response Format:**
```json
{
  "success": true,
  "data": {
    "records": [
      {
        "user_id": 1,
        "nama_lengkap": "Ahmad Rizki",
        "email": "ahmad@example.com",
        "total_deposits": 15,
        "total_kg": 45.5,
        "total_points": 455,
        "pending_kg": 5.5,
        "pending_points": 55,
        "approved_kg": 40.0,
        "approved_points": 400,
        "avg_per_deposit": 3.03,
        "last_deposit": "2025-12-20T10:30:00.000Z",
        "most_common_waste": "Plastik"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 156,
      "last_page": 16
    }
  }
}
```

---

## 🚀 **Performance Requirements**

### **Database Indexing**
```sql
-- Setoran Sampah Performance Indexes
CREATE INDEX idx_setoran_status_tanggal ON setoran_sampah(status, tanggal);
CREATE INDEX idx_setoran_user_status ON setoran_sampah(user_id, status);
CREATE INDEX idx_setoran_kategori ON setoran_sampah(kategori_id);
CREATE INDEX idx_setoran_tanggal_year_month ON setoran_sampah(YEAR(tanggal), MONTH(tanggal));

-- Poin Histories Performance Indexes  
CREATE INDEX idx_poin_jenis_created ON poin_histories(jenis, created_at);
CREATE INDEX idx_poin_user_jenis ON poin_histories(user_id, jenis);
CREATE INDEX idx_poin_sumber ON poin_histories(sumber);
CREATE INDEX idx_poin_created_year_month ON poin_histories(YEAR(created_at), MONTH(created_at));

-- Users Performance Index
CREATE INDEX idx_users_role ON users(role);
```

### **Caching Strategy**
```php
// Laravel Cache Implementation
public function getWasteAnalytics($period, $filters) {
    $cacheKey = "waste_analytics_{$period}_" . md5(json_encode($filters));
    
    return Cache::remember($cacheKey, now()->addMinutes(30), function() use ($period, $filters) {
        // Execute heavy SQL queries here
        return $this->executeWasteAnalytics($period, $filters);
    });
}

// Cache invalidation on data changes
public function invalidateAnalyticsCache() {
    Cache::forget('waste_analytics_*');
    Cache::forget('points_analytics_*');
    Cache::forget('waste_by_user_*');
}
```

---

## 📈 **Business Logic Requirements**

### **Growth Percentage Calculation**
```php
private function calculateGrowthPercentage($currentValue, $previousValue) {
    if ($previousValue == 0) {
        return $currentValue > 0 ? 100 : 0; // Handle division by zero
    }
    
    return round((($currentValue - $previousValue) / $previousValue) * 100, 2);
}

private function getPreviousPeriod($period, $year, $month = null) {
    switch($period) {
        case 'monthly':
            return $month == 1 ? [$year - 1, 12] : [$year, $month - 1];
        case 'yearly':
            return [$year - 1, null];
        default:
            return [$year, $month];
    }
}
```

### **Data Aggregation Rules**
1. **Only Approved Waste** - `status = 'approved'` untuk perhitungan final
2. **Points Calculation** - `berat * kategori.poin_per_kg`
3. **Growth Comparison** - Bandingkan dengan periode sebelumnya yang sama
4. **Percentage Distribution** - Relatif terhadap total dalam periode

---

## 🔧 **Implementation Priority**

### **Phase 1: Critical (Week 1)**
1. ✅ **Response Format Standardization** - Pastikan struktur JSON konsisten
2. ✅ **Basic SQL Optimization** - Query aggregation yang efisien
3. ✅ **Error Handling** - HTTP status codes dan error messages

### **Phase 2: Enhancement (Week 2)**
1. 📈 **Growth Percentage Logic** - Implementasi perhitungan growth
2. 🗃️ **Database Indexing** - Performance optimization
3. ⚡ **Caching Implementation** - Redis/Laravel cache

### **Phase 3: Advanced (Week 3)**
1. 📊 **Real-time Updates** - WebSocket untuk live analytics
2. 📋 **Export Features** - PDF/Excel generation
3. 🔍 **Advanced Filtering** - Date range, category filters

---

## 🧪 **Testing Requirements**

### **Unit Tests**
```php
// Test growth percentage calculation
public function testGrowthPercentageCalculation() {
    $this->assertEquals(50, $this->calculateGrowthPercentage(150, 100));
    $this->assertEquals(-25, $this->calculateGrowthPercentage(75, 100));
    $this->assertEquals(100, $this->calculateGrowthPercentage(100, 0));
}

// Test response format
public function testWasteAnalyticsResponseFormat() {
    $response = $this->get('/admin/analytics/waste?period=monthly&year=2025');
    
    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'data' => [
            'totalWaste',
            'totalTransactions', 
            'averagePerTransaction',
            'growthPercentage',
            'byCategory' => [
                '*' => ['nama_kategori', 'total_berat', 'jumlah', 'percentage']
            ],
            'monthlyTrend' => [
                '*' => ['month', 'total']
            ]
        ]
    ]);
}
```

---

## 📋 **Acceptance Criteria**

### **Performance Benchmarks**
- ⏱️ **Response Time** < 500ms untuk semua analytics endpoints
- 📊 **Data Freshness** Cache 30 menit, real-time untuk critical updates
- 🔄 **Concurrent Users** Support 50+ admin users simultaneously

### **Data Accuracy**
- ✅ **Calculation Precision** Decimal 2 places untuk weight, percentage  
- 📈 **Growth Logic** Handle edge cases (zero previous values)
- 🎯 **Filtering Accuracy** Period, category, user filters working correctly

### **API Reliability**
- 🚀 **Uptime** 99.9% availability
- 🛡️ **Error Handling** Graceful degradation, meaningful error messages
- 📝 **Logging** Comprehensive logging untuk debugging

---

## 🤝 **Collaboration Points**

### **Frontend akan handle:**
- Data mapping dari API response ke UI components
- Number formatting (thousand separators, currency)
- Fallback to mock data jika API unavailable  
- UI state management (loading, error states)

### **Backend perlu provide:**
- Optimized SQL queries sesuai dokumentasi di atas
- Consistent JSON response format
- Proper HTTP status codes
- Cache invalidation strategy

---

## 📞 **Contact & Questions**

**Frontend Developer**: Adib (Mendaur Frontend)  
**Repository**: https://github.com/Adib121140210/mendaur-frontend  
**Current Branch**: main

**Questions atau clarification:**
1. Database schema exact table names dan column names
2. Authentication middleware untuk admin endpoints
3. Rate limiting requirements untuk analytics endpoints
4. Backup strategy untuk analytics data

---

**Estimated Backend Development Time**: 2-3 weeks  
**Priority Level**: High (Analytics adalah core feature)  
**Dependencies**: Database optimization selesai sebelum caching implementation

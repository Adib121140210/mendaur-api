# 📊 ADMIN DASHBOARD - DATA TABLES GUIDE

## 🎯 Features & Required Tables

### **Feature 1: User Management**
**What Admin Needs to See:**
- All users list
- User profile details
- User activity status

**Tables Required:**
```
PRIMARY: users
├── id
├── nama
├── email
├── no_hp
├── alamat
├── foto_profil
├── total_poin
├── total_setor_sampah
├── level
├── created_at
└── updated_at
```

---

### **Feature 2: Total Sampah (Waste) Per Month/Period**
**What Admin Needs to See:**
- Total waste deposits per month
- Waste by type (Kertas, Plastik, Logam, etc.)
- Weight distribution per period
- Approved vs pending deposits

**Tables Required:**
```
PRIMARY: tabung_sampah
├── id
├── user_id          → Links to users
├── jenis_sampah     → (Kertas, Plastik, Logam, Kaca, Organik, etc.)
├── berat_kg         → Weight in kg
├── poin_didapat     → Points awarded
├── status           → pending, approved, rejected
├── created_at       → For monthly grouping
└── updated_at

SECONDARY: jenis_sampah (waste types reference)
├── id
├── nama             → Waste type name
└── kategori_id      → Links to kategori_sampah
```

**Query Example:**
```sql
-- Total waste per month by type
SELECT 
    DATE_FORMAT(tabung_sampah.created_at, '%Y-%m') as bulan,
    tabung_sampah.jenis_sampah,
    COUNT(*) as jumlah_setoran,
    SUM(berat_kg) as total_berat_kg,
    SUM(poin_didapat) as total_poin_diberi
FROM tabung_sampah
WHERE tabung_sampah.status = 'approved'
GROUP BY YEAR(tabung_sampah.created_at), 
         MONTH(tabung_sampah.created_at),
         tabung_sampah.jenis_sampah
ORDER BY tabung_sampah.created_at DESC;
```

---

### **Feature 3: Points Per Month/Period (from setor_sampah)**
**What Admin Needs to See:**
- Total points given per month
- Points given by waste type
- Points trend (daily/monthly)
- Top point contributors

**Tables Required:**
```
PRIMARY: poin_transaksis
├── id
├── user_id              → Links to users
├── tabung_sampah_id     → Links to tabung_sampah
├── jenis_sampah
├── berat_kg
├── poin_didapat         → Points given
├── sumber               → 'setor_sampah', 'tukar_poin', 'badge', 'bonus', 'manual'
├── keterangan
├── referensi_id
├── referensi_tipe
├── created_at           → For daily/monthly grouping
└── updated_at

SECONDARY: users (for linking)
├── id
├── nama
└── total_poin

TERTIARY: tabung_sampah (for details)
├── id
├── berat_kg
└── jenis_sampah
```

**Query Example:**
```sql
-- Total points given per month from waste deposits
SELECT 
    DATE_FORMAT(poin_transaksis.created_at, '%Y-%m') as bulan,
    COUNT(DISTINCT poin_transaksis.user_id) as jumlah_user,
    COUNT(*) as jumlah_transaksi,
    SUM(poin_transaksis.poin_didapat) as total_poin_diberi,
    AVG(poin_transaksis.poin_didapat) as rata_rata_poin
FROM poin_transaksis
WHERE poin_transaksis.sumber = 'setor_sampah'
GROUP BY YEAR(poin_transaksis.created_at), 
         MONTH(poin_transaksis.created_at)
ORDER BY poin_transaksis.created_at DESC;
```

---

### **Feature 4: Money/Points Given Per Month/Period**
**What Admin Needs to See:**
- Total point value per month
- Money equivalent of points (if conversion exists)
- Point distribution to users
- Point redemption tracking

**Tables Required:**
```
PRIMARY: poin_transaksis (for point records)
├── user_id
├── poin_didapat
├── sumber
└── created_at

SECONDARY: penukaran_produk (for redemptions/withdrawals)
├── id
├── user_id           → Links to users
├── produk_id
├── total_poin        → Points used
├── status
├── created_at
└── updated_at

TERTIARY: penarikan_tunai (for cash withdrawals)
├── id
├── user_id           → Links to users
├── jumlah            → Amount
├── status
└── created_at

QUATERNARY: transaksis (general transactions)
├── id
├── user_id
├── total_poin
└── created_at
```

---

### **Feature 5: Monthly/Daily Report**
**What Admin Needs to See:**
- Daily summary report
- Monthly summary report
- Export functionality (PDF/Excel)
- Charts and graphs

**Tables Required:**

**For Daily Report:**
```
SELECT 
    DATE(tabung_sampah.created_at) as tanggal,
    COUNT(tabung_sampah.id) as total_setoran,
    SUM(tabung_sampah.berat_kg) as total_sampah_kg,
    SUM(CASE WHEN tabung_sampah.status='approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN tabung_sampah.status='pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(tabung_sampah.poin_didapat) as total_poin_hari_ini,
    COUNT(DISTINCT tabung_sampah.user_id) as unique_users
FROM tabung_sampah
GROUP BY DATE(tabung_sampah.created_at)
ORDER BY tanggal DESC;
```

**For Monthly Report:**
```
SELECT 
    DATE_FORMAT(tabung_sampah.created_at, '%Y-%m') as bulan,
    COUNT(tabung_sampah.id) as total_setoran,
    SUM(tabung_sampah.berat_kg) as total_sampah_kg,
    COUNT(DISTINCT tabung_sampah.user_id) as unique_users,
    SUM(poin_transaksis.poin_didapat) as total_poin_diberikan,
    COUNT(penukaran_produk.id) as total_redemptions,
    SUM(penarikan_tunai.jumlah) as total_cash_withdrawn
FROM tabung_sampah
LEFT JOIN poin_transaksis ON poin_transaksis.tabung_sampah_id = tabung_sampah.id
LEFT JOIN penukaran_produk ON DATE_FORMAT(penukaran_produk.created_at, '%Y-%m') = DATE_FORMAT(tabung_sampah.created_at, '%Y-%m')
LEFT JOIN penarikan_tunai ON DATE_FORMAT(penarikan_tunai.created_at, '%Y-%m') = DATE_FORMAT(tabung_sampah.created_at, '%Y-%m')
GROUP BY DATE_FORMAT(tabung_sampah.created_at, '%Y-%m')
ORDER BY bulan DESC;
```

---

## 📋 COMPLETE TABLE MAPPING

| Feature | Primary Table | Secondary Tables | Key Columns |
|---------|---------------|------------------|-------------|
| **User Management** | users | - | id, nama, email, total_poin, total_setor_sampah, level |
| **Total Sampah (Monthly)** | tabung_sampah | jenis_sampah, kategori_sampah | berat_kg, jenis_sampah, status, created_at |
| **Points Per Period** | poin_transaksis | users, tabung_sampah | poin_didapat, sumber, created_at, user_id |
| **Money/Points Given** | poin_transaksis | penukaran_produk, penarikan_tunai, transaksis | total_poin, user_id, created_at |
| **Daily Report** | tabung_sampah | poin_transaksis, users | created_at, berat_kg, status |
| **Monthly Report** | tabung_sampah | poin_transaksis, penukaran_produk, penarikan_tunai | created_at (grouped by month) |

---

## 🔗 RELATIONSHIPS

```
users (1) ──────────── (M) tabung_sampah
  │                      │
  │                      └── (1:M) poin_transaksis (when sumber='setor_sampah')
  │
  ├──────────────────────── (M) poin_transaksis
  ├──────────────────────── (M) transaksis
  ├──────────────────────── (M) penukaran_produk
  └──────────────────────── (M) penarikan_tunai

tabung_sampah (1) ──────────── (M) poin_transaksis
```

---

## ✅ TABLES YOU NEED TO QUERY

**Main Tables:**
1. ✅ `users` - User info
2. ✅ `tabung_sampah` - Waste deposits
3. ✅ `poin_transaksis` - Point ledger
4. ✅ `penukaran_produk` - Product redemptions
5. ✅ `penarikan_tunai` - Cash withdrawals
6. ✅ `transaksis` - General transactions
7. ✅ `jenis_sampah` - Waste types reference
8. ✅ `kategori_sampah` - Waste categories reference

**Status:** All tables exist and are ready to query ✅

---

## 📝 NOTES FOR FRONTEND

For your Mendaur-TA frontend:

**API Endpoints You'll Need:**
```
GET /api/admin/dashboard
├── /api/admin/users           → List all users
├── /api/admin/waste           → Waste stats (monthly/daily)
├── /api/admin/points          → Point distribution stats
├── /api/admin/money           → Money/redemption stats
├── /api/admin/reports/daily   → Daily reports
├── /api/admin/reports/monthly → Monthly reports
├── /api/admin/reports/export  → Export as PDF/Excel
└── /api/admin/charts          → Chart data (prepared)
```

**Data to Request:**
- Date range for filtering
- Waste type for filtering
- Status filter (pending/approved/rejected)
- Export format (PDF/Excel/CSV)

---

## 📊 SAMPLE DATA READY IN DATABASE

**Current Database State:**
- ✅ Users: 6 records
- ✅ Badges: 10 records
- ✅ Waste deposits: Available for analysis
- ✅ Points transactions: Available for analysis
- ✅ Point ledger: Ready to query

All tables are populated and ready for dashboard queries!


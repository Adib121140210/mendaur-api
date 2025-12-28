# 🗄️ Database Schema Changes - Poin System

**Migration Date:** December 26, 2025  
**Migration File:** `2025_12_26_164856_add_display_poin_to_users_table.php`

---

## 📊 Table: `users` - Column Changes

### Before Migration (OLD)
```sql
CREATE TABLE users (
  user_id BIGINT PRIMARY KEY,
  nama VARCHAR(255),
  email VARCHAR(255),
  total_poin INT DEFAULT 0,           -- ❌ REMOVED
  total_setor_sampah INT DEFAULT 0,
  level VARCHAR(50),
  ...
);
```

### After Migration (NEW)
```sql
CREATE TABLE users (
  user_id BIGINT PRIMARY KEY,
  nama VARCHAR(255),
  email VARCHAR(255),
  display_poin INT DEFAULT 0,         -- ✅ NEW (renamed from total_poin)
  actual_poin INT DEFAULT 0,          -- ✅ NEW (calculated from transactions)
  poin_tercatat INT DEFAULT 0,        -- ✅ NEW (cumulative total)
  total_setor_sampah INT DEFAULT 0,
  level VARCHAR(50),
  ...
);
```

---

## 🔄 Column Mapping & Purpose

```
┌─────────────────┬──────────────────┬────────────────────────────────────┐
│   OLD COLUMN    │   NEW COLUMN     │            PURPOSE                 │
├─────────────────┼──────────────────┼────────────────────────────────────┤
│                 │ actual_poin      │ Saldo yang bisa digunakan          │
│                 │                  │ - Untuk withdrawal                 │
│ total_poin      │                  │ - Untuk redemption                 │
│                 │                  │ - Dikalkulasi dari poin_transaksis │
│                 ├──────────────────┼────────────────────────────────────┤
│                 │ display_poin     │ Poin untuk leaderboard             │
│                 │                  │ - Bisa di-reset oleh admin         │
│                 │                  │ - Untuk kompetisi bulanan          │
│                 ├──────────────────┼────────────────────────────────────┤
│                 │ poin_tercatat    │ Total poin tercatat                │
│                 │                  │ - Untuk badge progress             │
│                 │                  │ - Tidak pernah berkurang           │
└─────────────────┴──────────────────┴────────────────────────────────────┘
```

---

## 💡 Why 3 Separate Columns?

### Problem dengan `total_poin` Tunggal:
1. **Leaderboard Reset Issue**
   - Admin ingin reset leaderboard tiap bulan
   - Tapi user tetap harus bisa gunakan poin lama

2. **Badge Progress Tracking**
   - Badge progress harus pakai total akumulatif
   - Tidak boleh berkurang saat withdrawal

3. **Transaction Balance**
   - Perlu tracking saldo aktual untuk transaksi
   - Harus akurat untuk withdrawal/redemption

### Solution: 3-Column System

```
┌─────────────────────────────────────────────────────────────┐
│                    POIN FLOW DIAGRAM                        │
└─────────────────────────────────────────────────────────────┘

    User Setor Sampah (+1000 poin)
                │
                ├──────────────────────────────┐
                │                              │
                ▼                              ▼
    ┌───────────────────────┐      ┌──────────────────────┐
    │   actual_poin += 1000 │      │  display_poin += 1000│
    │   (for withdrawal)    │      │  (for leaderboard)   │
    └───────────────────────┘      └──────────────────────┘
                │                              │
                └──────────────┬───────────────┘
                               │
                               ▼
                   ┌──────────────────────────┐
                   │  poin_tercatat += 1000   │
                   │  (for badge progress)    │
                   └──────────────────────────┘


    User Withdraw 500 poin
                │
                ├──────────────────────────────┐
                │                              │
                ▼                              ▼
    ┌───────────────────────┐      ┌──────────────────────┐
    │   actual_poin -= 500  │      │  display_poin -= 500 │
    │   (balance reduced)   │      │  (ranking reduced)   │
    └───────────────────────┘      └──────────────────────┘
                                               │
                                               ▼
                                   ┌──────────────────────┐
                                   │  poin_tercatat       │
                                   │  (NO CHANGE)         │
                                   └──────────────────────┘


    Admin Reset Leaderboard (Bulanan)
                │
                ▼
    ┌───────────────────────┐      ┌──────────────────────┐
    │   actual_poin         │      │  display_poin = 0    │
    │   (NO CHANGE)         │      │  (reset for new      │
    │   (user keeps balance)│      │   competition)       │
    └───────────────────────┘      └──────────────────────┘
                                               │
                                               ▼
                                   ┌──────────────────────┐
                                   │  poin_tercatat       │
                                   │  (NO CHANGE)         │
                                   └──────────────────────┘
```

---

## 🎯 Use Cases per Column

### 1. `actual_poin` - Transaction Balance
**Used For:**
- ✅ Withdrawal validation: `actual_poin >= withdrawal_amount`
- ✅ Redemption validation: `actual_poin >= product_price`
- ✅ Display saldo di wallet/profile
- ✅ API endpoint: `/api/user/balance`

**Updated By:**
- Deposit approval → `actual_poin += poin_diberikan`
- Withdrawal → `actual_poin -= jumlah_poin`
- Redemption → `actual_poin -= poin_digunakan`
- Badge reward (Konvensional) → `actual_poin += reward_poin`
- Admin refund → `actual_poin += refund_amount`

**Never Reset**

---

### 2. `display_poin` - Leaderboard Points
**Used For:**
- ✅ Leaderboard ranking: `ORDER BY display_poin DESC`
- ✅ Kompetisi bulanan
- ✅ Display di public profile
- ✅ Achievement showcase

**Updated By:**
- Same as `actual_poin` untuk konsistensi
- Admin reset leaderboard → `display_poin = 0` (ALL USERS)

**Can Be Reset by Admin**

---

### 3. `poin_tercatat` - Cumulative Record
**Used For:**
- ✅ Badge progress calculation
- ✅ Lifetime achievement
- ✅ Historical tracking
- ✅ Modern nasabah reward storage (non-usable)

**Updated By:**
- Deposit approval → `poin_tercatat += poin_diberikan`
- Badge reward (Modern) → `poin_tercatat += reward_poin`
- **NOT** decreased by withdrawal/redemption

**Never Decreases**

---

## 📈 Example Scenarios

### Scenario 1: Normal User Flow
```
Initial State:
  actual_poin: 0
  display_poin: 0
  poin_tercatat: 0

1. User deposits waste, gets 1000 poin
   actual_poin: 1000
   display_poin: 1000
   poin_tercatat: 1000

2. User unlocks badge, gets 500 poin reward
   actual_poin: 1500
   display_poin: 1500
   poin_tercatat: 1500

3. User withdraws 800 poin
   actual_poin: 700
   display_poin: 700
   poin_tercatat: 1500 (unchanged)

4. Admin resets leaderboard
   actual_poin: 700 (unchanged - user keeps money)
   display_poin: 0 (reset for competition)
   poin_tercatat: 1500 (unchanged)

5. User deposits again, gets 2000 poin
   actual_poin: 2700
   display_poin: 2000 (starts fresh)
   poin_tercatat: 3500
```

---

### Scenario 2: Dual Nasabah System

#### Konvensional User (Cash-based)
```
User deposits 5kg waste → 5000 poin

Backend updates:
  actual_poin += 5000      ✅ Can use for withdrawal
  display_poin += 5000     ✅ Appears in leaderboard
  poin_tercatat += 5000    ✅ Counts toward badges

Badge reward (500 poin):
  actual_poin += 500       ✅ Can withdraw
  display_poin += 500      ✅ Counts in ranking
  poin_tercatat += 500     ✅ Badge progress
```

#### Modern User (Non-cash)
```
User deposits 5kg waste → 5000 poin

Backend updates:
  actual_poin += 0         ❌ Cannot withdraw
  display_poin += 0        ❌ Not in leaderboard
  poin_tercatat += 5000    ✅ Counts toward badges

Badge reward (500 poin):
  actual_poin += 0         ❌ Cannot use
  display_poin += 0        ❌ Not for ranking
  poin_tercatat += 500     ✅ Badge progress only
```

---

## 🔧 Migration Impact

### Data Migration
```sql
-- Migration automatically ran:
ALTER TABLE users 
  RENAME COLUMN total_poin TO display_poin;

ALTER TABLE users 
  ADD COLUMN actual_poin INT DEFAULT 0 AFTER display_poin;

ALTER TABLE users 
  ADD COLUMN poin_tercatat INT DEFAULT 0 AFTER actual_poin;

-- Initial values set by PointService::calculateActualPoin()
UPDATE users 
SET actual_poin = (
  SELECT COALESCE(SUM(poin_didapat), 0) 
  FROM poin_transaksis 
  WHERE user_id = users.user_id
);

UPDATE users 
SET poin_tercatat = display_poin;
```

---

## 🗂️ Related Tables

### Table: `poin_transaksis`
```sql
CREATE TABLE poin_transaksis (
  poin_transaksi_id BIGINT PRIMARY KEY,
  user_id BIGINT,
  poin_didapat INT,              -- Positive = earning, Negative = spending
  sumber VARCHAR(50),            -- setor_sampah, badge, withdrawal, etc
  keterangan TEXT,
  referensi_id BIGINT,
  referensi_tipe VARCHAR(50),
  created_at TIMESTAMP
);
```

**Purpose:** 
- Source of truth untuk `actual_poin`
- Backend calculates: `actual_poin = SUM(poin_didapat)`

---

### Table: `tabung_sampah`
```sql
CREATE TABLE tabung_sampah (
  tabung_sampah_id BIGINT PRIMARY KEY,
  user_id BIGINT,
  berat_kg DECIMAL(10,2),
  poin_didapat INT,              -- Points awarded after approval
  status ENUM('pending', 'approved', 'rejected'),
  ...
);
```

**Integration:**
- Admin approval → Creates `poin_transaksis` entry → Updates user poin columns

---

### Table: `user_badges`
```sql
CREATE TABLE user_badges (
  user_badge_id BIGINT PRIMARY KEY,
  user_id BIGINT,
  badge_id BIGINT,
  tanggal_dapat TIMESTAMP,
  reward_claimed BOOLEAN
);
```

**Integration:**
- Badge unlock → Creates `poin_transaksis` → Updates user poin based on nasabah type

---

## 📊 Index Recommendations

```sql
-- For leaderboard queries
CREATE INDEX idx_display_poin ON users(display_poin DESC);

-- For badge progress
CREATE INDEX idx_poin_tercatat ON users(poin_tercatat DESC);

-- For transaction lookup
CREATE INDEX idx_actual_poin ON users(actual_poin DESC);
```

---

## 🔐 Data Integrity Rules

### Rule 1: actual_poin Calculation
```
actual_poin = SUM(poin_transaksis.poin_didapat WHERE user_id = X)
```
Must always match. Use `PointService::syncActualPoin()` to fix discrepancies.

### Rule 2: display_poin Constraints
```
display_poin >= 0  (can be reset)
display_poin <= poin_tercatat  (never exceeds lifetime total)
```

### Rule 3: poin_tercatat Monotonic
```
poin_tercatat can only increase or stay same
NEVER: poin_tercatat--
```

---

## 🧪 Testing Queries

### Check User Poin Consistency
```sql
SELECT 
  u.user_id,
  u.nama,
  u.actual_poin as stored_actual,
  COALESCE(SUM(pt.poin_didapat), 0) as calculated_actual,
  u.actual_poin - COALESCE(SUM(pt.poin_didapat), 0) as difference
FROM users u
LEFT JOIN poin_transaksis pt ON u.user_id = pt.user_id
GROUP BY u.user_id
HAVING difference != 0;
```

### Find Users with Negative Balance
```sql
SELECT user_id, nama, actual_poin 
FROM users 
WHERE actual_poin < 0;
```

### Leaderboard Query
```sql
SELECT user_id, nama, display_poin, total_setor_sampah
FROM users
WHERE role_id = 3  -- nasabah only
ORDER BY display_poin DESC, total_setor_sampah DESC
LIMIT 10;
```

---

## 📞 Database Admin Commands

### Fix Poin Discrepancy
```php
php artisan app:sync-user-actual-poin
```

### Reset Leaderboard (Monthly)
```sql
UPDATE users SET display_poin = 0 WHERE role_id = 3;
```

### Recalculate Badge Progress
```php
php artisan app:recalculate-badge-progress
```

---

**Database Version:** MySQL 8.0+  
**Collation:** utf8mb4_unicode_ci  
**Engine:** InnoDB

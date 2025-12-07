# 🔗 FOREIGN KEY CONSTRAINTS EXPLAINED

**Panduan memahami CASCADE DELETE vs SET NULL vs RESTRICT**

---

## 📌 TIGA TIPE CONSTRAINT

### 1️⃣ CASCADE DELETE (Most Common)
```
LINE TYPE: ━━━━━ (Solid)
SYMBOL: ──────o→ atau ──────∘→

MEANING:
Jika parent record dihapus, semua child records ikut dihapus otomatis

VISUAL:
Parent (USERS)                Child (TABUNG_SAMPAH)
┌─────────────────┐           ┌──────────────────┐
│ id=5            │ ───DELETE──>│ id=100           │
│ name=Budi       │ 1:M CASCADE │ user_id=5        │
│ email=budi@...  │ ───DELETE──>│ id=101           │
│ role_id=1       │             │ user_id=5        │
│ total_poin=500  │ ───DELETE──>│ id=102           │
│                 │             │ user_id=5        │
│ (DELETE USERS   │             │ (All these       │
│  WHERE id=5)    │             │  deleted too!)   │
└─────────────────┘             └──────────────────┘

EXAMPLE QUERY:
DELETE FROM users WHERE id=5;
-- Automatically deletes:
--   - All tabung_sampah where user_id=5
--   - All poin_transaksis where user_id=5
--   - All penukaran_produk where user_id=5
--   - etc.

USAGE RULE:
✓ Use CASCADE DELETE when:
  - Child data is "owned by" parent
  - Child is meaningless without parent
  - Examples: USERS → TABUNG_SAMPAH, USERS → NOTIFIKASI
  
❌ DON'T use CASCADE DELETE when:
  - Child has independent value
  - You want to preserve history
  - Data is sensitive/audit-related

AFFECTED TABLES IN MENDAUR:
├─ USERS → TABUNG_SAMPAH (CASCADE)
├─ USERS → POIN_TRANSAKSIS (CASCADE)
├─ USERS → PENUKARAN_PRODUK (CASCADE)
├─ USERS → PENARIKAN_TUNAI (CASCADE)
├─ USERS → NOTIFIKASI (CASCADE)
├─ USERS → LOG_AKTIVITAS (CASCADE)
├─ USERS → USER_BADGES (CASCADE) - M:M junction
├─ USERS → BADGE_PROGRESS (CASCADE)
├─ BADGES → USER_BADGES (CASCADE)
├─ BADGES → BADGE_PROGRESS (CASCADE)
├─ PENUKARAN_PRODUK → PENUKARAN_PRODUK_DETAIL (CASCADE)
└─ WASTE_CATEGORIES → WASTE_TYPES (RESTRICT, not CASCADE)

RISK FACTORS:
⚠️  HIGH RISK: Deleting user cascades to 1000+ records
⚠️  SOLUTION: Consider soft deletes (is_deleted flag) for users
```

---

### 2️⃣ SET NULL (Moderate Common)
```
LINE TYPE: ╌╌╌╌╌ (Dashed)
SYMBOL: ──---o→ atau ──...→

MEANING:
Jika parent record dihapus, child FK field diset NULL (tidak cascade)

VISUAL:
Parent (TABUNG_SAMPAH)        Child (POIN_TRANSAKSIS)
┌──────────────────┐          ┌─────────────────────┐
│ id=100           │          │ id=501              │
│ user_id=5        │ ─DELETE──→│ user_id=5           │
│ waste_type=PLT   │ SET NULL  │ tabung_sampah_id=100│
│ berat=5kg        │          │ poin=+50            │
│                  │          │ (tabung_sampah_id   │
│ (DELETE)         │          │  becomes NULL!)     │
└──────────────────┘          └─────────────────────┘

AFTER DELETE:
Original:
  tabung_sampah.id=100
  poin_transaksis.tabung_sampah_id=100

After parent deleted:
  tabung_sampah.id=100 (DELETED)
  poin_transaksis.tabung_sampah_id=NULL (not deleted, just NULL)

EFFECT:
✓ poin_transaksis record SURVIVES
✓ But loses reference to deposit
✓ Audit trail preserved
✓ Data not deleted

USAGE RULE:
✓ Use SET NULL when:
  - Child data should survive parent deletion
  - You want to preserve history
  - Child has value independent of parent
  - Examples: POIN_TRANSAKSIS, PENUKARAN_PRODUK
  
❌ DON'T use SET NULL when:
  - You need FK to always be NOT NULL
  - Orphaned records would be confusing
  
AFFECTED TABLES IN MENDAUR:
├─ TABUNG_SAMPAH → POIN_TRANSAKSIS (SET NULL)
│  Why: We want poin history even if deposit deleted
├─ PRODUCTS → PENUKARAN_PRODUK (SET NULL)
│  Why: Redemption history should survive product deletion
├─ WASTE_TYPES → TABUNG_SAMPAH (SET NULL)
│  Why: Deposits may reference deleted waste types
├─ WASTE_CATEGORIES → TABUNG_SAMPAH (SET NULL)
│  Why: Categories may be archived/deleted
├─ ASSET_UPLOADS → PRODUCTS (SET NULL)
│  Why: Product record survives image deletion
├─ ASSET_UPLOADS → ARTIKEL (SET NULL)
│  Why: Article survives image deletion
├─ ASSET_UPLOADS → BANNERS (SET NULL)
│  Why: Banner survives image deletion
├─ BANK_ACCOUNTS → PENARIKAN_TUNAI (SET NULL)
│  Why: Withdrawal history survives bank deletion
└─ penukaran_produk_detail.product_id (RESTRICTED, NOT SET NULL)
   Why: We don't want orphaned detail records

PROS:
✓ Data preserved
✓ Audit trail intact
✓ History accessible

CONS:
✗ Orphaned records (FK=NULL)
✗ Must handle NULL in queries
✗ FK constraint not enforced when NULL
```

---

### 3️⃣ RESTRICT (Least Common, Most Strict)
```
LINE TYPE: ═════ (Thick/Bold)
SYMBOL: ──|──o→ atau ──====→

MEANING:
Jika ada child records, parent TIDAK BOLEH dihapus

VISUAL:
Parent (WASTE_CATEGORIES)     Child (WASTE_TYPES)
┌────────────────────┐        ┌──────────────┐
│ id=1               │        │ id=101       │
│ nama=Plastik       │ ←BLOCK─│ name=Botol   │
│                    │        │ cat_id=1     │
│ (CANNOT DELETE!)   │        │              │
│ Error if try:      │        └──────────────┘
│ "Cannot delete,    │        
│  WASTE_TYPES       │        ┌──────────────┐
│  still reference"  │        │ id=102       │
└────────────────────┘        │ name=Kaleng  │
                              │ cat_id=1     │
                              └──────────────┘

BEHAVIOR:
DELETE FROM waste_categories WHERE id=1;
-- Result: ERROR! Cannot delete
-- Reason: 2 waste_types still reference this category
-- Solution: Delete all waste_types first, then category

USAGE RULE:
✓ Use RESTRICT when:
  - You want to prevent accidental parent deletion
  - Parent is "lookup table"
  - Child must always reference a valid parent
  - Examples: WASTE_CATEGORIES, (maybe BADGES)
  
❌ DON'T use RESTRICT when:
  - Parent might be legitimately deleted
  - History is important
  - Should be SET NULL instead

AFFECTED TABLES IN MENDAUR:
├─ WASTE_CATEGORIES → WASTE_TYPES (RESTRICT)
│  Why: Prevent deleting category while types exist
├─ PENUKARAN_PRODUK_DETAIL → PRODUCTS (RESTRICT)
│  Why: Prevent deleting product while redemption items exist
└─ (Possibly BADGES if no archive strategy)

PROS:
✓ Prevents accidental deletion
✓ Forces cleanup
✓ Data integrity maintained

CONS:
✗ Harder to delete data
✗ Must know deletion order
✗ Can block legitimate deletions
```

---

## 🎯 COMPARISON TABLE

| Aspect | CASCADE DELETE | SET NULL | RESTRICT |
|--------|---|---|---|
| **Visual** | ━━━━ | ╌╌╌╌ | ════ |
| **On Parent Delete** | Child deleted | Child FK=NULL | DELETE fails |
| **Data Preserved** | No | Yes | N/A |
| **Child Records** | Gone | Orphaned (NULL) | Unchanged |
| **Use Case** | Owned data | History/Audit | Lookup tables |
| **Common Use** | ✅ Most | ✅ Very common | ⚠️ Less common |
| **Complexity** | Simple | Moderate | High |
| **Risk** | Data loss | Orphaned rows | Deletion blocks |

---

## 📊 PRACTICAL EXAMPLES FROM MENDAUR

### Example 1: User Deletes (CASCADE DELETE)
```
Scenario: Admin wants to delete user account completely

User: Budi (id=5)
├── NASABAH_DETAILS (1 record) ─CASCADE→ DELETED
├── TABUNG_SAMPAH (50 records) ─CASCADE→ DELETED
├── POIN_TRANSAKSIS (150 records) ─CASCADE→ DELETED
├── PENUKARAN_PRODUK (10 records) ─CASCADE→ DELETED
├── PENARIKAN_TUNAI (3 records) ─CASCADE→ DELETED
├── NOTIFIKASI (100 records) ─CASCADE→ DELETED
├── LOG_AKTIVITAS (200 records) ─CASCADE→ DELETED
├── USER_BADGES (15 records) ─CASCADE→ DELETED
└── BADGE_PROGRESS (20 records) ─CASCADE→ DELETED

Result: User completely erased (913 records deleted!)

RISK: ⚠️ HIGH - Need to be careful with user deletion
SOLUTION: Consider soft-delete (is_deleted=true) instead
```

### Example 2: Deposit Deleted, Points Preserved (SET NULL)
```
Scenario: Admin finds fraudulent deposit, deletes it

Deposit: TABUNG_SAMPAH id=100 (5kg plastik)
└── Poin Record: POIN_TRANSAKSIS (id=501)
    └── FK tabung_sampah_id: 100 ─SET NULL→ NULL

Before:
  POIN_TRANSAKSIS: tabung_sampah_id=100, poin=+50

After:
  POIN_TRANSAKSIS: tabung_sampah_id=NULL, poin=+50
                   (Record exists, but unlinked)

Result: Poin record survives as audit trail
        User's poin count already updated (separate calculation)
        Can trace: "This +50 poin from unknown source (deleted deposit)"
```

### Example 3: Cannot Delete Category While Types Exist (RESTRICT)
```
Scenario: Admin tries to delete waste category

Category: "Plastik" (id=1)
└─ WASTE_TYPES: 12 records with category_id=1

Attempt: DELETE FROM waste_categories WHERE id=1;

Result: ❌ ERROR!
  Message: "Cannot delete, foreign key constraint fails"
  
Solution: Delete waste types first
  1. DELETE FROM waste_types WHERE waste_category_id=1;
  2. DELETE FROM waste_categories WHERE id=1;
  (Then deletion succeeds)

Why: Prevents accidental deletion of lookup data
     while still in use
```

---

## 🔍 QUERYING ORPHANED RECORDS (SET NULL Results)

```sql
-- Find poin records with NULL tabung_sampah_id (orphaned)
SELECT * FROM poin_transaksis
WHERE tabung_sampah_id IS NULL
ORDER BY created_at DESC;

-- Find redemptions with NULL product_id
SELECT * FROM penukaran_produk
WHERE product_id IS NULL;

-- Find articles with NULL image
SELECT * FROM artikel
WHERE foto_cover_id IS NULL;

-- Verify referential integrity
SELECT COUNT(*) as orphaned_records
FROM poin_transaksis pt
LEFT JOIN tabung_sampah ts ON pt.tabung_sampah_id = ts.id
WHERE pt.tabung_sampah_id IS NOT NULL
  AND ts.id IS NULL;
```

---

## 🎨 HOW TO DRAW IN ERD TOOL

### In Draw.io:
```
1. Draw relationship line between tables
2. Right-click line → Edit style
3. Choose line type:
   - CASCADE DELETE: Solid line ━━━
   - SET NULL: Dashed line ╌╌╌
   - RESTRICT: Bold line ════
4. Add label: "CASCADE DELETE", "SET NULL", or "RESTRICT"
5. Add cardinality marks: 1, M
```

### In DbDesigner:
```
1. Click Edit Relationship
2. Choose delete rule dropdown:
   - CASCADE
   - SET NULL
   - RESTRICT (or NO ACTION)
3. System auto-shows line type
```

### In MySQL Workbench:
```
Right-click relationship → Edit Relationship
├─ Foreign Key Options
│  └─ ON DELETE
│     ├─ CASCADE
│     ├─ SET NULL
│     └─ RESTRICT
```

---

## 📝 CONSTRAINT CHOICE DECISION TREE

```
Question: How to handle parent deletion?
│
├─→ "Delete child too" 
│   │
│   └─→ CASCADE DELETE ✓
│        (Child is dependent on parent)
│        Example: User deletes → All notifications deleted
│
├─→ "Keep child, but unlink it"
│   │
│   └─→ SET NULL ✓
│        (Child has independent value)
│        (Need history/audit trail)
│        Example: Deposit deleted → Poin history remains
│
└─→ "Don't allow parent deletion"
    │
    └─→ RESTRICT ✓
         (Parent is critical lookup)
         (Must force manual cleanup)
         Example: Category can't delete while types exist
```

---

## ⚠️ COMMON MISTAKES

### ❌ Mistake 1: CASCADE on non-dependent data
```
WRONG:
  PRODUCTS (1:M CASCADE) → PENUKARAN_PRODUK

Problem: Delete product deletes all redemptions!
         Loses sales history

RIGHT:
  PRODUCTS (1:M SET NULL) → PENUKARAN_PRODUK
  
Reason: Product may be deleted but sale history should remain
```

### ❌ Mistake 2: SET NULL on NOT NULL field
```
WRONG:
  CREATE TABLE waste_types (
    ...
    category_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES waste_categories
      ON DELETE SET NULL  ← Can't set NULL!
  )

Problem: Contradicts NOT NULL constraint

RIGHT:
  FOREIGN KEY (category_id) REFERENCES waste_categories
    ON DELETE RESTRICT  ← Prevent deletion
```

### ❌ Mistake 3: RESTRICT on everything
```
WRONG:
  All relationships set to RESTRICT

Problem: Can't delete anything!
         Complex deletion order required
         Rigid system

RIGHT:
  Use CASCADE for owned data
  Use SET NULL for history
  Use RESTRICT only for critical lookups
```

---

## ✅ MENDAUR SYSTEM CONSTRAINT SUMMARY

```
CASCADE DELETE (11 relationships):
├─ USERS → TABUNG_SAMPAH
├─ USERS → POIN_TRANSAKSIS  
├─ USERS → PENUKARAN_PRODUK
├─ USERS → PENARIKAN_TUNAI
├─ USERS → NOTIFIKASI
├─ USERS → LOG_AKTIVITAS
├─ USERS → USER_BADGES (M:M)
├─ USERS → BADGE_PROGRESS
├─ BADGES → USER_BADGES (M:M)
├─ BADGES → BADGE_PROGRESS
└─ PENUKARAN_PRODUK → PENUKARAN_PRODUK_DETAIL

SET NULL (8+ relationships):
├─ TABUNG_SAMPAH → POIN_TRANSAKSIS
├─ PRODUCTS → PENUKARAN_PRODUK
├─ WASTE_TYPES → TABUNG_SAMPAH
├─ WASTE_CATEGORIES → TABUNG_SAMPAH
├─ ASSET_UPLOADS → PRODUCTS
├─ ASSET_UPLOADS → ARTIKEL
├─ ASSET_UPLOADS → BANNERS
└─ BANK_ACCOUNTS → PENARIKAN_TUNAI

RESTRICT (2 relationships):
├─ WASTE_CATEGORIES → WASTE_TYPES
└─ PENUKARAN_PRODUK_DETAIL → PRODUCTS
```

---

**Key Takeaway**: 
- **CASCADE DELETE** = Data is owned by parent
- **SET NULL** = Data has independent value, preserve history  
- **RESTRICT** = Data is critical lookup, must exist

Choose wisely based on business logic!

# ⚡ ERD QUICK REFERENCE - VERIFIED FROM DATABASE

**✅ UPDATED: November 30, 2025 - Database Verification Complete**  
**Verified**: 29 tables (23 business + 6 system), 22 FK relationships, All CASCADE DELETE

**Gunakan file ini sebagai cheat sheet saat membuat ERD**

---

## 🔄 CRITICAL UPDATES FROM VERIFICATION

⚠️ **Table Names Corrections**:
- Use: `JADWAL_PENYETORANS` (with 'S' at end)
- Use: `ARTIKELS` (with 'S')
- Removed: POIN_LEDGER, PENUKARAN_PRODUK_DETAIL, BANK_ACCOUNTS (not in database)

✅ **Total Tables for ERD**: 23 business tables
✅ **Total Relationships**: 22 FK (all CASCADE DELETE)
❌ **No SET NULL or RESTRICT constraints** (all are CASCADE)

---

## 🎯 5 FASE SUPER RINGKAS (UPDATED)

### **FASE 1** (5 min) - Foundation
```
USERS ← ROLES
Color: BLUE
Position: CENTER
```

### **FASE 2** (10 min) - Waste
```
KATEGORI_SAMPAH ──1:M CASCADE── JENIS_SAMPAH
     │
     └─ (No FK - independent lookup)

JADWAL_PENYETORANS (No outgoing FK)
     │
     └─ 1:M CASCADE ──→ TABUNG_SAMPAH ← USERS (M:1 CASCADE)

Color: GREEN
Position: LEFT
```

### **FASE 3** (10 min) - Points
```
USERS ──1:M CASCADE─→ TABUNG_SAMPAH
  │
  └──1:M CASCADE─→ POIN_TRANSAKSIS ← TABUNG_SAMPAH (M:1 SET NULL)

POIN_LEDGER (independent, no FK)

Color: GRAY
Position: CENTER-BOTTOM
```

### **FASE 4A** (12 min) - Products & Transactions
```
KATEGORI_TRANSAKSI ──1:M CASCADE── TRANSAKSIS ← USERS (M:1 CASCADE)
                                         │
                                         └─ PRODUKS (M:1 CASCADE)

PRODUKS ──1:M CASCADE─→ PENUKARAN_PRODUK ← USERS (M:1 CASCADE)

Color: YELLOW
Position: RIGHT-TOP

Note: PENUKARAN_PRODUK_DETAIL does NOT exist in database
      All data stays in PENUKARAN_PRODUK table
```

### **FASE 4B** (12 min) - Gamification
```
BADGES (1:M CASCADE) ──→ USER_BADGES ←M:M── BADGES
           │                │
           │                └─ USERS (M:1 CASCADE)
           │
           └─ BADGE_PROGRESS (1:M CASCADE) ← USERS (M:1 CASCADE)

Color: PURPLE
Position: FAR RIGHT
```

### **FASE 5** (8+8+5 min) - Support, Admin & Content
```
USERS (1:M CASCADE) ──→ PENARIKAN_TUNAI
                        (No bank_accounts table - data in users & penarikan_tunai)

USERS (1:M CASCADE) ──→ SESSIONS
USERS (1:M CASCADE) ──→ NOTIFIKASI
USERS (1:M CASCADE) ──→ LOG_AKTIVITAS
USERS (1:M CASCADE) ──→ AUDIT_LOGS

ROLES (1:M CASCADE) ──→ ROLE_PERMISSIONS
ROLES (1:M CASCADE) ──→ USERS

ARTIKELS (independent, no FK)

Color: YELLOW (cash), BLUE (sessions/logs), CYAN (content)
Position: VARIOUS
```

---

## 📊 ALL 22 VERIFIED RELATIONSHIPS (COPY-PASTE)

```
VERIFIED FROM DATABASE: 22 FK Relationships, 100% CASCADE DELETE

DOMAIN 1: User Management & Authentication (7 relationships)
─────────────────────────────────────────────────────────
1.  roles (1:M) ──CASCADE── role_permissions
2.  roles (1:M) ──CASCADE── users (FK: role_id)
3.  users (1:M) ──CASCADE── sessions
4.  users (1:M) ──CASCADE── notifikasi
5.  users (1:M) ──CASCADE── log_aktivitas
6.  users (1:M) ──CASCADE── audit_logs
7.  users (1:M) ──CASCADE── penarikan_tunai (both user_id and processed_by)

DOMAIN 2: Waste Management (3 relationships)
─────────────────────────────────────────────────────────
8.  kategori_sampah (1:M) ──CASCADE── jenis_sampah
9.  jadwal_penyetorans (1:M) ──CASCADE── tabung_sampah
10. users (1:M) ──CASCADE── tabung_sampah

DOMAIN 3: Points & Audit Trail (2 relationships)
─────────────────────────────────────────────────────────
11. users (1:M) ──CASCADE── poin_transaksis
12. tabung_sampah (1:M) ──CASCADE── poin_transaksis
    (POIN_LEDGER: does NOT exist)

DOMAIN 4: Products & Commerce (5 relationships)
─────────────────────────────────────────────────────────
13. kategori_transaksi (1:M) ──CASCADE── transaksis
14. produks (1:M) ──CASCADE── transaksis
15. users (1:M) ──CASCADE── transaksis
16. produks (1:M) ──CASCADE── penukaran_produk
17. users (1:M) ──CASCADE── penukaran_produk
    (PENUKARAN_PRODUK_DETAIL: does NOT exist)
    (BANK_ACCOUNTS: does NOT exist)

DOMAIN 5: Gamification (4 relationships)
─────────────────────────────────────────────────────────
18. badges (1:M) ──CASCADE── user_badges
19. users (1:M) ──CASCADE── user_badges
20. badges (1:M) ──CASCADE── badge_progress
21. users (1:M) ──CASCADE── badge_progress

DOMAIN 6: Content (0 relationships)
─────────────────────────────────────────────────────────
22. ARTIKELS - independent table, NO FK

RINGKASAN:
──────────
Total Tabel: 23 (business) + 6 (system) = 29
Total Relationships: 22
Tabel dengan FK keluar: 17
├─ CASCADE DELETE: 22 relationships (100%)
├─ SET NULL: 0 relationships (0%)
└─ RESTRICT: 0 relationships (0%)
```

---

## 🎨 WARNA-WARNA

| Warna | Grup | Tabel |
|-------|------|-------|
| 🔵 BLUE | User Management | USERS, ROLES, ROLE_PERMISSIONS, NOTIFIKASI, LOG_AKTIVITAS, AUDIT_LOGS, SESSIONS |
| 🟢 GREEN | Waste System | KATEGORI_SAMPAH, JENIS_SAMPAH, TABUNG_SAMPAH, JADWAL_PENYETORANS |
| 🟡 YELLOW | Commerce | PRODUKS, PENUKARAN_PRODUK, TRANSAKSIS, KATEGORI_TRANSAKSI, PENARIKAN_TUNAI |
| 🟣 PURPLE | Gamification | BADGES, USER_BADGES, BADGE_PROGRESS |
| ⚫ GRAY | Audit/Points | POIN_TRANSAKSIS |
| 🔵 CYAN | Content | ARTIKELS |

---

## 📍 POSISI DI GRID (UPDATED - 23 Tables)

```
TIER 1 (Master Lookups - Top Left):
┌─────────────────────────────┐
│ KATEGORI_SAMPAH  PRODUKS    │
│      │               │      │
│  JENIS_SAMPAH   KATEGORI_TX │
└─────────────────────────────┘

TIER 2 (Schedule):
┌──────────────────┐
│ JADWAL_PENYETORANS
└──────────────────┘

TIER 3 (USERS - CENTER MAIN HUB):
┌──────────────────────────────┐
│    USERS ← ROLES             │
│  (Primary reference point)   │
│  All 22 FKs connect here     │
└──────────────────────────────┘

TIER 4 (User-Related - Around USERS):
┌─────────────────────────────────────────┐
│  SESSIONS  │  NOTIFIKASI  │  LOG_AKTIVITAS  │  AUDIT_LOGS
└─────────────────────────────────────────┘

TIER 5 (Waste Transactions - Left Side):
┌──────────────────┐
│  TABUNG_SAMPAH   │
│  POIN_TRANSAKSIS │
└──────────────────┘

TIER 6 (Commerce - Right Side):
┌──────────────────────────────┐
│ TRANSAKSIS    PENUKARAN_PRODUK
│   │                 │
│   ├─→ PRODUKS ←─────┘
│   └─→ KATEGORI_TRANSAKSI
│
│ PENARIKAN_TUNAI
└──────────────────────────────┘

TIER 7 (Gamification - Far Right):
┌──────────────────────┐
│ BADGES              │
│   ├─→ USER_BADGES   │
│   └─→ BADGE_PROGRESS│
└──────────────────────┘

TIER 8 (Content - Bottom Right):
┌──────────┐
│ ARTIKELS │ (standalone, no FK)
└──────────┘

ROLE_PERMISSIONS ← ROLES (left side of USERS)

VIRTUAL FEATURES (Not Tables - Derived Data):
┌──────────────────────────────────────┐
│ LEADERBOARD  (calculated from)       │
│  ├─ BADGE_PROGRESS + USER_BADGES    │
│  └─ Ranked by total_poin in USERS   │
│                                      │
│ RIWAYAT/LOG  (stored in)            │
│  ├─ LOG_AKTIVITAS (user actions)    │
│  ├─ AUDIT_LOGS (admin actions)      │
│  └─ POIN_TRANSAKSIS (point history) │
└──────────────────────────────────────┘

Grid Summary:
- CENTER: USERS + ROLES (primary hub)
- LEFT: Waste system (TABUNG_SAMPAH, POIN_TRANSAKSIS)
- RIGHT: Commerce (TRANSAKSIS, PENUKARAN_PRODUK, BADGES)
- TOP: Master lookups (KATEGORI_SAMPAH, PRODUKS)
- BOTTOM: Content (ARTIKELS)
- SUPPORT: Sessions/Logs/Audit (around USERS)

All 23 tables positioned for minimal line crossing!

NOTE: Leaderboard & History are FEATURES, not separate tables:
  ✓ Leaderboard → Query BADGE_PROGRESS + sort by poin
  ✓ History/Log → Query LOG_AKTIVITAS, AUDIT_LOGS, POIN_TRANSAKSIS
```

---

## ✅ MINIMAL CHECKLIST

```
□ Total 23 business tables included (not 20)
□ JADWAL_PENYETORANS (with 'S' at end)
□ ARTIKELS (with 'S')
□ All 22 FK relationships shown
□ ALL constraints are CASCADE (not mixed)
□ Cardinality mark terlihat (1, M)
□ Warna coding konsisten (6 colors + 1 content)
□ Layout tidak overlap
□ High resolution (300 DPI)
□ Legend/title ada
□ System tables NOT included (CACHE, MIGRATIONS, etc)

Removed from ERD (not in database):
  ✓ POIN_LEDGER
  ✓ PENUKARAN_PRODUK_DETAIL
  ✓ BANK_ACCOUNTS
  ✓ JADWAL_PENYETORAN (old name)
```

---

## 🔍 COMMON MISTAKES (Hindari!)

❌ FK tanpa cardinality mark
❌ Line crossing everywhere
❌ Tabel terlalu besar/kecil
❌ Text tidak readable
❌ Constraint type tidak jelas
❌ Warna random/inconsistent
❌ Tabel floating (not grouped)
✅ Gunakan grid layout
✅ Group by color
✅ Logical positioning
✅ Clear labels

---

## 🎯 TOOLS REKOMENDASI

| Tool | Kelebihan | Kekurangan | Rating |
|------|----------|-----------|--------|
| **Draw.io** | Mudah, intuitif, free | Perlu manual format | ⭐⭐⭐⭐ |
| **DbDesigner** | Profesional, database-focused | Interface kompleks | ⭐⭐⭐⭐⭐ |
| **Lucidchart** | Beautiful, powerful | Berbayar | ⭐⭐⭐⭐ |
| **MySQL Workbench** | Free, powerful | Steep learning curve | ⭐⭐⭐ |

**Rekomendasi untuk pemula**: Draw.io
**Rekomendasi untuk profesional**: DbDesigner

---

## 💡 PRO TIPS

1. **Mulai dari USERS**
   - Semua tabel refer ke sini
   - Tempatkan di CENTER

2. **Independent tables ke TOP**
   - WASTE_CATEGORIES, PRODUCTS, BADGES, ASSET_UPLOADS
   - Mereka tidak punya FK keluar

3. **Grouping by Color**
   - Membuat visual lebih clean
   - Easier to understand domains

4. **Line Routing**
   - Minimize crossing
   - Prefer horizontal/vertical
   - Avoid diagonal if possible

5. **Font & Size**
   - Min 10pt font
   - PK fields lebih bold
   - FK fields berbeda warna

6. **Export Tips**
   - Save as PNG 300 DPI
   - Also save source (.drawio, .mwb)
   - PDF untuk printing

---

## 📞 QUICK HELP

**Q: Berapa lama membuat ERD?**
A: ~60-75 menit untuk semua 20 tabel, 5 fase

**Q: Perlu draw semantic attributes?**
A: No, hanya PK, FK, cardinality cukup

**Q: FK bisa di-hide untuk clarity?**
A: Boleh, tapi label CASCADE/SET NULL harus tetap

**Q: Bagaimana dengan M:M relationship?**
A: Draw junction table di tengah, dengan 2 M:1 ke masing-masing parent

**Q: Relationship yang bener gmn?**
A: Cek list di atas (25+ relationships)

**Q: Boleh di-edit setelah export?**
A: Boleh, but save source file also

---

## 🎓 UNTUK ACADEMIC REPORT

Include dalam report:
✅ Complete ERD (semua 20 tabel)
✅ Cardinality clearly marked
✅ Constraint types labeled
✅ Color-coded by domain
✅ Legend/notes present
✅ High quality (300 DPI)
✅ Brief explanation (1-2 paragraphs)

Caption example:
```
"Figure X: Complete Entity Relationship Diagram (ERD) of the MENDAUR System
showing 20 tables organized into 5 main domains (User Management, Waste
Management, Product/Commerce, Gamification, and Support), with 25+
relationships using CASCADE DELETE and SET NULL constraints for referential
integrity and audit trail capabilities."
```

---

**Print this page untuk reference saat menggambar!** 📄

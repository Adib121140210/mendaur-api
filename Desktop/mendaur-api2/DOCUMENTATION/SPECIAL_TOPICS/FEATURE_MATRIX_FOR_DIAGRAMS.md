# 🎯 FEATURE MATRIX BY USER ROLE

**Date**: November 29, 2025  
**System**: Mendaur Waste Management API  
**Purpose**: Define features for Use Case & Physical ERD Diagrams

---

## 📊 SYSTEM ACTORS & ROLES

```
┌─────────────────────────────────────────────────────────────────┐
│                     3 PRIMARY USER ROLES                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1️⃣  NASABAH (Regular User / Community Member)                 │
│      └─ Level: 1 | Access: Personal data only                 │
│                                                                 │
│  2️⃣  ADMIN (Bank Staff / Transaction Manager)                  │
│      └─ Level: 2 | Access: All user data + approvals          │
│                                                                 │
│  3️⃣  SUPERADMIN (System Administrator / Bank Manager)          │
│      └─ Level: 3 | Access: Full system control                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🟢 NASABAH (Regular User) - 18 Features

### 📋 Profile & Account Management (3 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 1 | **View Profile** | View own personal information | Self only | users |
| 2 | **Update Profile** | Modify name, address, phone, profile photo | Self only | users |
| 3 | **Change Password** | Update authentication credentials | Self only | users, password_reset_tokens |

### ♻️ Waste Management Features (5 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 4 | **View Waste Categories** | Browse available waste types | Read-only (public) | kategori_sampah, jenis_sampah |
| 5 | **Deposit Waste (Create)** | Submit waste deposit request | Create own | tabung_sampah, jenis_sampah |
| 6 | **View Deposit History** | See own waste deposits | Read own | tabung_sampah |
| 7 | **View Deposit Schedule** | Check available deposit times/locations | Read-only | jadwal_penyetoran |
| 8 | **Upload Waste Photo** | Attach photo proof for deposit | Create own | tabung_sampah (foto_sampah) |

### 💰 Points Management (5 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 9 | **View Points Balance** | Check current poin total | Read own | users (total_poin) |
| 10 | **View Points History** | See all poin transactions | Read own | poin_transaksis |
| 11 | **Filter History by Type** | Filter poin by source (deposit, reward, etc) | Read own | poin_transaksis (sumber) |
| 12 | **View Points Breakdown** | See poin by category | Read own | poin_transaksis |
| 13 | **View Leaderboard** | See ranking vs other users | Read public | users (total_poin) |

### 🏆 Badge & Achievement System (3 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 14 | **View Available Badges** | Browse all achievement badges | Read-only | badges |
| 15 | **View Badge Progress** | See % progress on each badge | Read own | badge_progress |
| 16 | **View Earned Badges** | See badges I've unlocked | Read own | user_badges, badge_progress |

### 🛍️ Product Redemption (2 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 17 | **View Product Catalog** | Browse available products for poin exchange | Read-only | produks |
| 18 | **Redeem Product** | Submit product redemption request (pending approval) | Create own | penukaran_produk, poin_transaksis |

### 💸 Cash Management (0 features at this tier)
*Note: Cash withdrawal request feature will be in v2*

---

## 🟡 ADMIN (Bank Staff / Operator) - 35 Features

### 👥 User Management (8 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 1 | **View All Users** | List all registered users with filters | Read all | users |
| 2 | **View User Details** | See complete user profile & statistics | Read all | users, poin_transaksis, tabung_sampah |
| 3 | **View User Activity Log** | See user's activity history | Read all | log_aktivitas |
| 4 | **Verify User Phone** | Verify user phone number | Read all | users |
| 5 | **View User's Poin History** | See detailed poin ledger for specific user | Read all | poin_transaksis |
| 6 | **View User's Badges** | See all badges earned by user | Read all | user_badges, badge_progress |
| 7 | **View User's Deposits** | See all waste deposits by user | Read all | tabung_sampah |
| 8 | **View User's Transactions** | See all transactions for user | Read all | transaksis, penukaran_produk |

### ♻️ Waste Deposit Approval (6 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 9 | **View Pending Deposits** | List deposits awaiting approval | Read pending | tabung_sampah (status='pending') |
| 10 | **View Deposit Details** | See weight, type, photo, location | Read all | tabung_sampah, jenis_sampah |
| 11 | **Approve Deposit** | Accept waste deposit & allocate poin | Update | tabung_sampah, poin_transaksis |
| 12 | **Reject Deposit** | Decline waste deposit with reason | Update | tabung_sampah |
| 13 | **Verify Weight** | Validate submitted weight accuracy | Read/verify | tabung_sampah |
| 14 | **Allocate Poin** | Assign points based on waste type/weight | Create | poin_transaksis |

### 🏆 Badge Management (3 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 15 | **View All Badge Definitions** | See all badge types & criteria | Read | badges |
| 16 | **Manually Award Badge** | Give badge to user (emergency/special case) | Create | user_badges, poin_transaksis |
| 17 | **View Badge Statistics** | See how many users have each badge | Read | badge_progress, user_badges |

### 🛍️ Product Redemption Management (6 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 18 | **View Pending Redemptions** | List redemptions awaiting approval | Read pending | penukaran_produk (status='pending') |
| 19 | **View Redemption Details** | See product, poin cost, user | Read all | penukaran_produk, produks |
| 20 | **Approve Redemption** | Accept product redemption | Update | penukaran_produk, poin_transaksis |
| 21 | **Reject Redemption** | Decline redemption with reason | Update | penukaran_produk |
| 22 | **Mark As Collected** | Record when user picks up product | Update | penukaran_produk |
| 23 | **View Redemption History** | See completed redemptions | Read | penukaran_produk |

### 📊 Transaction Management (4 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 24 | **View All Transactions** | List all system transactions | Read all | transaksis |
| 25 | **Filter Transactions** | Filter by date, type, category, status | Read all | transaksis, kategori_transaksi |
| 26 | **View Transaction Details** | See full transaction record | Read all | transaksis, users, produks |
| 27 | **Track Transaction Status** | Monitor transaction progress | Read | transaksis |

### 💰 Cash Withdrawal Management (4 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 28 | **View Pending Withdrawals** | List cash withdrawal requests | Read pending | penarikan_tunai (status='pending') |
| 29 | **View Withdrawal Details** | See amount, account, user info | Read all | penarikan_tunai, users |
| 30 | **Approve Withdrawal** | Authorize cash withdrawal | Update | penarikan_tunai, poin_transaksis |
| 31 | **Reject Withdrawal** | Deny withdrawal with reason | Update | penarikan_tunai |

### 📝 Admin Dashboard & Reports (3 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 32 | **View Admin Dashboard** | See key metrics (users, poin, deposits) | Read all | users, poin_transaksis, tabung_sampah |
| 33 | **Generate Daily Report** | Export deposits & poin for day | Read all | tabung_sampah, poin_transaksis |
| 34 | **View System Analytics** | See trends, statistics, graphs | Read all | Multiple (analytics views) |
| 35 | **Export Data to CSV** | Download transaction/user data | Read all | Various tables |

### 📋 Content Management (1 feature)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 36 | **View Articles/News** | Browse educational content | Read-only | artikels |

---

## 🔴 SUPERADMIN (Bank Manager / System Owner) - 28 Features

### 👑 Role & Permission Management (6 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 1 | **View All Roles** | List all system roles | Read | roles |
| 2 | **Create Role** | Define new role type | Create | roles |
| 3 | **Edit Role** | Modify role properties | Update | roles |
| 4 | **Delete Role** | Remove role (if no users) | Delete | roles |
| 5 | **Manage Permissions** | Assign/revoke permissions per role | Create/Delete | role_permissions |
| 6 | **View Permission Matrix** | See role-permission mapping | Read | roles, role_permissions |

### 👨‍💼 Admin Management (7 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 7 | **View All Admins** | List all admin users | Read all | users (role_id=2) |
| 8 | **Create Admin** | Register new admin user | Create | users, roles |
| 9 | **Edit Admin Details** | Modify admin profile | Update | users |
| 10 | **Change Admin Role** | Promote/demote admin | Update | users (role_id) |
| 11 | **Deactivate Admin** | Disable admin account | Update | users |
| 12 | **Reset Admin Password** | Force password reset | Update | users |
| 13 | **View Admin Activity Log** | See what admins did (audit trail) | Read all | audit_logs, log_aktivitas |

### 🏆 Badge Configuration (4 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 14 | **Create Badge** | Define new achievement badge | Create | badges |
| 15 | **Edit Badge Criteria** | Modify poin/weight requirements | Update | badges |
| 16 | **Edit Badge Rewards** | Change reward poin amount | Update | badges |
| 17 | **Delete Badge** | Remove badge (reset progress) | Delete | badges, badge_progress |

### 📦 Product Catalog Management (5 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 18 | **Create Product** | Add new product to catalog | Create | produks |
| 19 | **Edit Product** | Modify product details/price | Update | produks |
| 20 | **Delete Product** | Remove product from catalog | Delete | produks |
| 21 | **Manage Stock** | Update product quantities | Update | produks |
| 22 | **View Product Analytics** | See which products are popular | Read | produks, penukaran_produk |

### ♻️ Waste Category Management (3 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 23 | **Create Waste Category** | Define new waste type category | Create | kategori_sampah |
| 24 | **Edit Waste Category** | Modify category properties | Update | kategori_sampah |
| 25 | **Manage Waste Types** | Create/edit individual jenis_sampah | Create/Update | jenis_sampah |

### 📰 Content Management (2 features)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 26 | **Create Article** | Write educational content | Create | artikels |
| 27 | **Edit/Delete Article** | Modify or remove content | Update/Delete | artikels |

### 🔐 System Management & Audit (1 feature)

| # | Feature | Description | Data Access | Related Tables |
|---|---------|-------------|--------------|----------------|
| 28 | **View Complete Audit Log** | See all system actions (admin/superadmin) | Read all | audit_logs |

---

## 📊 FEATURE SUMMARY TABLE

```
┌─────────────────────────────────────────────────────────────────┐
│                    FEATURE COUNT BY ROLE                        │
├──────────────────┬──────────────┬───────────────────────────────┤
│ Role             │ Total Count  │ Permission Level              │
├──────────────────┼──────────────┼───────────────────────────────┤
│ NASABAH          │ 18           │ 🟢 Limited (Self + Public)   │
│ ADMIN            │ 36           │ 🟡 Extended (Operational)    │
│ SUPERADMIN       │ 28           │ 🔴 Full System (Strategic)   │
├──────────────────┼──────────────┼───────────────────────────────┤
│ TOTAL UNIQUE     │ 82           │ (Some features overlap)       │
│ FEATURES         │              │                               │
└──────────────────┴──────────────┴───────────────────────────────┘
```

---

## 🎯 USE CASE DIAGRAM STRUCTURE

### Primary Actors
```
┌─────────────────────────────────────────────────────────────────┐
│                    USE CASE DIAGRAM ACTORS                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  👤 NASABAH (User Actor)                                        │
│     └─ Interacts with: Waste deposits, Poin system, Products   │
│                                                                 │
│  👨‍💼 ADMIN (Operator Actor)                                     │
│     └─ Interacts with: Approvals, User management, Reports     │
│                                                                 │
│  👑 SUPERADMIN (Manager Actor)                                  │
│     └─ Interacts with: System config, Admins, Audit trail      │
│                                                                 │
│  🔄 SYSTEM (Internal Actor)                                    │
│     └─ Automatic: Badge progression, Poin calculation          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Use Case Categories (by Domain)

**1. Authentication & Account**
- Sign Up / Register
- Sign In / Login
- Sign Out / Logout
- Manage Profile
- Change Password

**2. Waste Management**
- View Waste Categories
- View Waste Types
- Submit Waste Deposit
- View Deposit Status
- View Deposit History
- Receive Deposit Approval

**3. Points System**
- View Points Balance
- View Points History
- View Points Breakdown
- Earn Points (automatic)
- Lose Points (redemption)

**4. Badges & Gamification**
- View Available Badges
- View Badge Progress
- Unlock Badge (automatic)
- View Earned Badges
- Receive Badge Reward (automatic)

**5. Products & Redemption**
- View Product Catalog
- Redeem Product
- Receive Redemption Approval
- Collect Product
- View Redemption History

**6. Admin Operations**
- View Pending Deposits
- Approve/Reject Deposits
- View Pending Redemptions
- Approve/Reject Redemptions
- View Pending Withdrawals
- Approve/Reject Withdrawals
- Manage Users

**7. System Administration**
- Manage Admins
- Manage Roles
- Manage Permissions
- Configure Badges
- Configure Products
- View Audit Logs
- Generate Reports

---

## 🗄️ PHYSICAL ERD STRUCTURE

### Entity Grouping by Domain

**TIER 1: CORE SYSTEM**
```
┌─ users (Core entity - hub)
├─ roles (Access control)
└─ role_permissions (Permission mapping)
```

**TIER 2: WASTE MANAGEMENT**
```
├─ kategori_sampah (Classification)
├─ jenis_sampah (Waste types)
├─ tabung_sampah (Waste deposits)
└─ jadwal_penyetoran (Schedules)
```

**TIER 3: POINTS SYSTEM**
```
├─ poin_transaksis (Ledger/Audit trail)
└─ log_aktivitas (Activity tracking)
```

**TIER 4: GAMIFICATION**
```
├─ badges (Achievement definitions)
├─ user_badges (User achievements)
└─ badge_progress (Progress tracking)
```

**TIER 5: PRODUCTS & TRANSACTIONS**
```
├─ produks (Product catalog)
├─ kategori_transaksi (Transaction types)
├─ transaksis (General transactions)
├─ penukaran_produk (Point redemptions)
└─ penarikan_tunai (Cash withdrawals)
```

**TIER 6: COMMUNICATION**
```
├─ notifikasi (User notifications)
└─ artikels (Educational content)
```

**TIER 7: AUDIT & LOGGING**
```
├─ audit_logs (Admin action tracking)
└─ sessions (User session management)
```

---

## 🔗 RELATIONSHIP MAPPING FOR ERD

### One-to-Many Relationships (Primary FKs)
```
users → tabung_sampah (1 user : M deposits)
users → penukaran_produk (1 user : M redemptions)
users → transaksis (1 user : M transactions)
users → penarikan_tunai (1 user : M withdrawals)
users → poin_transaksis (1 user : M point transactions)
users → notifikasi (1 user : M notifications)
users → log_aktivitas (1 user : M activity logs)
users → badge_progress (1 user : M badge progress)
users → audit_logs (1 user : M audit entries - admin actions)

kategori_sampah → jenis_sampah (1 category : M waste types)

produks → penukaran_produk (1 product : M redemptions)

kategori_transaksi → transaksis (1 category : M transactions)

badges → user_badges (1 badge : M user awards)
badges → badge_progress (1 badge : M user progress)

roles → role_permissions (1 role : M permissions)

jadwal_penyetoran → tabung_sampah (1 schedule : M deposits - optional)
```

### Many-to-Many Relationships (Junction Tables)
```
users ←→ badges (via user_badges junction table)
  └─ M users : M badges
  └─ Represents user achievements
```

### Foreign Keys with Special Rules
```
users.role_id → roles.id (Default: 1=nasabah)
penarikan_tunai.processed_by → users.id (Admin who processed, SET NULL on delete)
poin_transaksis.tabung_sampah_id → tabung_sampah.id (Nullable, SET NULL on delete)
```

---

## 📋 RECOMMENDED DIAGRAMS FOR YOUR REPORT

### 1. Use Case Diagram (High-level)
```
Shows 3 actors (Nasabah, Admin, Superadmin) interacting with:
- Authentication use cases
- Waste management use cases
- Points management use cases
- Product redemption use cases
- Admin operations use cases
- System admin use cases

Recommended: Draw in Lucidchart, Draw.io, or Visio
```

### 2. Physical ERD Diagram (Detailed)
```
Show all 20 tables with:
- Primary keys (PK)
- Foreign keys (FK)
- Relationships (1:M, M:M)
- Cascade rules
- Column data types

Organized by tiers:
Tier 1: Core System (users, roles, permissions)
Tier 2: Waste Management (kategori, jenis, tabung, jadwal)
Tier 3: Points System (poin_transaksis, log_aktivitas)
Tier 4: Gamification (badges, user_badges, badge_progress)
Tier 5: Transactions (produks, kategori_transaksi, transaksis, penukaran, penarikan)
Tier 6: Communication (notifikasi, artikels)
Tier 7: Audit (audit_logs, sessions)

Recommended: Use dbdiagram.io or Lucidchart
```

### 3. Feature Matrix (Table Format)
```
Rows: Features (82 total)
Cols: Roles (Nasabah, Admin, Superadmin)
Content: ✓ if role can access

Creates: Clear permission overview
```

### 4. Data Flow Diagram (DFD)
```
Level 0: System boundary
- External entities: Users, Admin, System
- Main processes: Deposit, Redeem, Approve, Track

Level 1: Detailed processes
- Process 1.0: User Management
- Process 2.0: Waste Management
- Process 3.0: Point Management
- Process 4.0: Redemption Management
- Process 5.0: Approval Management
- Process 6.0: System Administration
```

---

## 📝 DOCUMENT STRUCTURE FOR YOUR REPORT

**Chapter 1: System Overview**
- 3 Actors table
- System context diagram

**Chapter 2: Use Cases**
- Use case diagram (graphic)
- Use case descriptions (narrative)
- Feature list by category

**Chapter 3: Database Design**
- Physical ERD (graphic)
- Table definitions
- Relationship rules
- Cascade rules

**Chapter 4: Feature Matrix**
- Permission table (3 columns × 82 rows)
- Feature categories breakdown

**Chapter 5: Data Flow**
- DFD Level 0 (System context)
- DFD Level 1 (Process decomposition)
- Data stores (tables)

---

## 🎨 COLOR CODING SUGGESTIONS

For diagrams:
```
🟢 GREEN   = Nasabah actions (user operations)
🟡 YELLOW  = Admin actions (operational control)
🔴 RED     = Superadmin actions (system control)
🔵 BLUE    = System automatic actions (internal)
⚪ WHITE   = Data entities (no change on diagram)
```

---

## 📊 NEXT STEPS FOR YOUR REPORT

1. **Create Use Case Diagram**
   - Tool: Draw.io, Lucidchart, or Visual Paradigm
   - Include: 4 actors, 20+ use cases, relationships
   - Time: 2-3 hours

2. **Create Physical ERD**
   - Tool: dbdiagram.io or Lucidchart
   - Include: 20 tables, all columns, relationships
   - Time: 1-2 hours

3. **Create Feature Matrix Table**
   - Tool: Excel or Google Sheets
   - Include: 82 features × 3 roles
   - Time: 30 minutes

4. **Create DFD (optional but recommended)**
   - Tool: Draw.io or Lucidchart
   - Include: Level 0 and Level 1
   - Time: 1-2 hours

5. **Document Everything**
   - Add legends and descriptions
   - Add assumption notes
   - Add constraint notes

---

**Total Report Time: 5-8 hours (depending on complexity)**


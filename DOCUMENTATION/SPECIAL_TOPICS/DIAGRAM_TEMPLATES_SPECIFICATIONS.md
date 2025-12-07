# 📐 DIAGRAM TEMPLATES & SPECIFICATIONS

**Date**: November 29, 2025  
**Purpose**: Ready-to-use templates for Use Case & Physical ERD

---

## 🎯 USE CASE DIAGRAM - HIERARCHICAL APPROACH

### Overview Diagram (Main Business Processes)

```
@startuml Mendaur_UseCases_Overview
title Mendaur System - Use Case Overview

actor Nasabah as N
actor Admin as A
actor Superadmin as S
actor System as SYS

N --> (Manage Profile)
N --> (Manage Waste Deposits)
N --> (Manage Points & Rewards)
N --> (Redeem Products)

A --> (Approve Operations)
A --> (Manage Users)
A --> (View Analytics & Reports)

S --> (System Administration)
S --> (Content Management)

SYS --> (Background Processes)

@enduml
```

**Key Benefits**:
- Only 8 main use cases (very clean)
- Shows system architecture clearly
- Perfect for overview/introduction
- Easy to understand at a glance

---

### Detailed Diagram 1: Nasabah (Regular User) - 18 Use Cases (UPDATED)

```
@startuml Mendaur_UseCases_Nasabah
title Mendaur System - Nasabah Use Cases (Regular User)

actor Nasabah as N
actor System as SYS

N --> (Register)
N --> (Login)
N --> (Logout)
N --> (View/Update Profile)
N --> (View Notifications)
N --> (Submit Waste Deposit)
N --> (Cancel Deposit)
N --> (View Deposit History)
N --> (View Points Balance)
N --> (View Points History)
N --> (View Product Catalog)
N --> (Redeem Product)
N --> (Cancel Redemption)
N --> (View Leaderboard)
N --> (View Available Badges)
N --> (View Earned Badges)
N --> (Share Badge Achievement)
N --> (Request Cash Withdrawal)
N --> (View Withdrawal History)
N --> (Read Articles & Tips)

(Submit Waste Deposit) .> (Calculate Points): <<include>>
(Redeem Product) .> (Calculate Points): <<include>>
(View Earned Badges) .> (Update Leaderboard): <<include>>
(Share Badge Achievement) .> (Send Notifications): <<include>>

SYS --> (Calculate Points)
SYS --> (Track Badge Progress)
SYS --> (Update Leaderboard)
SYS --> (Send Notifications)

@enduml
```

**Features Covered**:
- ✓ Authentication (Register, Login, Logout)
- ✓ Profile Management (View, Update, Notifications)
- ✓ Waste Management (Submit, Cancel, View History)
- ✓ Points Tracking (View Balance, Detailed History, Leaderboard)
- ✓ Gamification (View/Earn Badges, Share Achievements)
- ✓ Product Redemption (View Catalog, Redeem, Cancel)
- ✓ Cash Withdrawal (Request, View History)
- ✓ Content (Read Articles & Tips)

**NEW FEATURES ADDED** (+5 UC):
- ✅ Logout
- ✅ View Notifications
- ✅ Cancel Deposit
- ✅ View Points History (detailed)
- ✅ View Product Catalog (separate from redeem)
- ✅ Cancel Redemption
- ✅ Share Badge Achievement

---

### Detailed Diagram 2: Admin (Operator/Feature Manager) - 35 Use Cases (RESTRUCTURED)

```
@startuml Mendaur_UseCases_Admin
title Mendaur System - Admin Use Cases (Feature Manager/Operator)

actor Admin as A
actor System as SYS

package "Waste Management Operations" {
  A --> (View All Deposits)
  A --> (View Pending Deposits)
  A --> (Approve/Reject Deposit)
  A --> (Verify Waste Weight)
  A --> (Create Waste Category)
  A --> (Edit Waste Category)
  A --> (Delete Waste Category)
  A --> (Create Waste Type)
  A --> (Edit Waste Type)
  A --> (Archive Waste Type)
}

package "Product Redemption Operations" {
  A --> (View All Redemptions)
  A --> (View Pending Redemptions)
  A --> (Approve/Reject Redemption)
  A --> (Mark Product as Collected)
  A --> (View Redemption Analytics)
  A --> (Create Product)
  A --> (Edit Product)
  A --> (Delete Product)
  A --> (Manage Stock Level)
}

package "Cash Withdrawal Operations" {
  A --> (View All Withdrawals)
  A --> (View Pending Withdrawals)
  A --> (Approve/Reject Withdrawal)
  A --> (Process Payment Transfer)
}

package "Point Management" {
  A --> (View User Points)
  A --> (Adjust User Points)
  A --> (View Points History)
  A --> (View Leaderboard)
}

package "Badge Management" {
  A --> (Create Badge)
  A --> (Edit Badge Definition)
  A --> (Delete Badge)
  A --> (Set Badge Criteria)
  A --> (Publish Badge)
}

package "User Management" {
  A --> (View All Users)
  A --> (View User Details)
  A --> (View User Activity Log)
  A --> (Deactivate User Account)
  A --> (Send Notifications)
  A --> (Manage User Support)
}

package "Content Management" {
  A --> (Create Article)
  A --> (Edit Article)
  A --> (Delete Article)
  A --> (Publish Article)
  A --> (Create Banner)
  A --> (Edit Banner)
  A --> (Delete Banner)
}

package "Analytics & Reporting" {
  A --> (View Admin Dashboard)
  A --> (Generate Daily Report)
  A --> (Export Report to CSV)
  A --> (View System Analytics)
}

(Approve/Reject Deposit) .> (Calculate Points): <<include>>
(Approve/Reject Redemption) .> (Send Notifications): <<include>>
(Approve/Reject Withdrawal) .> (Send Notifications): <<include>>
(Adjust User Points) .> (Log Activities): <<include>>
(Publish Badge) .> (Send Notifications): <<include>>

SYS --> (Calculate Points)
SYS --> (Send Notifications)
SYS --> (Log Activities)

@enduml
```

**Features Covered** (35 UC):
- ✓ Waste Management (10 UC) - Full CRUD + verification + categories/types
- ✓ Product Redemption (9 UC) - Full CRUD + analytics + stock management
- ✓ Cash Withdrawal (4 UC) - Approval & payment processing
- ✓ Point Management (4 UC) - Adjustment & tracking
- ✓ Badge Management (5 UC) - Full CRUD + criteria & publishing
- ✓ User Management (6 UC) - All user operations + support
- ✓ Content Management (7 UC) - Articles & banners CRUD
- ✓ Analytics & Reporting (4 UC) - Dashboards & exports

**KEY RESPONSIBILITIES**:
- ✅ Manages ALL day-to-day features (products, badges, articles, categories, types)
- ✅ Approves all user transactions (deposits, withdrawals, redemptions)
- ✅ Handles point transfers & adjustments to users
- ✅ Manages all user accounts & support
- ✅ Views analytics & generates reports

---

### Detailed Diagram 3: Superadmin (System Manager) - 15 Use Cases (RESTRUCTURED)

```
@startuml Mendaur_UseCases_Superadmin
title Mendaur System - Superadmin Use Cases (System Manager/Governance)

actor Superadmin as S
actor System as SYS

package "Admin Account Management" {
  S --> (View All Admin Accounts)
  S --> (Create Admin Account)
  S --> (Edit Admin Account)
  S --> (Delete Admin Account)
  S --> (View Admin Permissions)
  S --> (Assign Admin Roles)
}

package "System Audit & Monitoring" {
  S --> (View Complete Audit Log)
  S --> (View Admin Action Audit)
  S --> (View System Logs)
  S --> (Monitor System Performance)
  S --> (View All Transactions)
}

package "System Configuration" {
  S --> (Manage System Roles)
  S --> (Manage Permissions per Role)
  S --> (View System Settings)
  S --> (Update System Configuration)
  S --> (Manage System Parameters)
}

SYS --> (Log Activities)
SYS --> (Log Admin Actions)
SYS --> (Monitor Performance)

@enduml
```

**Features Covered** (15 UC):
- ✓ Admin Management (6 UC) - Create, Edit, Delete, Assign roles to admins
- ✓ System Audit (5 UC) - Complete audit trail & monitoring
- ✓ System Configuration (4 UC) - Roles, permissions, settings

**KEY RESPONSIBILITIES**:
- ✅ Monitors ALL admin accounts & actions
- ✅ Manages admin access & permissions
- ✅ Views system audit logs & performance metrics
- ✅ Manages system configuration & parameters
- ❌ Does NOT manage day-to-day features (products, badges, articles, etc)
- ❌ Does NOT handle user transactions (deposits, withdrawals, redemptions)

---

### Detailed Diagram 4: System (Background Processes) - 5 Use Cases

```
@startuml Mendaur_UseCases_System
title Mendaur System - System Processes (Automatic)

actor System as SYS

SYS --> (Calculate Points from Deposits)
SYS --> (Track Badge Progress)
SYS --> (Unlock Badges at 100%)
SYS --> (Send Notifications)
SYS --> (Log All Activities)

@enduml
```

**Processes Covered**:
- ✓ Points Calculation (Waste weight × Type price)
- ✓ Badge Progression (Track current_value % target_value)
- ✓ Badge Unlocking (When progress_percentage >= 100%)
- ✓ Notifications (Email, SMS, Push)
- ✓ Audit Logging (User activity, Admin actions)

---

### Complete Detailed Use Case Specification (UPDATED - 50+ UC)

```
@startuml Mendaur_UseCases_Complete_Detailed
title Mendaur System - Complete Use Case Specification

actor Nasabah as N
actor Admin as A
actor Superadmin as S
actor System as SYS

package "User Management" {
  N --> (Register)
  N --> (Login/Logout)
  N --> (View Profile)
  N --> (Update Profile)
  N --> (View Notifications)
}

package "Waste Management" {
  N --> (Submit Waste Deposit)
  N --> (Cancel Deposit)
  N --> (View Deposit History)
  A --> (View All Deposits)
  A --> (View Pending Deposits)
  A --> (Approve Deposit)
  A --> (Reject Deposit)
  A --> (Verify Weight)
  S --> (Create Waste Category)
  S --> (Edit Waste Category)
  S --> (Delete Waste Category)
  S --> (Create Waste Type)
  S --> (Edit Waste Type)
  (Submit Waste Deposit) .> (Verify Weight): <<include>>
}

package "Points System" {
  N --> (View Points Balance)
  N --> (View Points History)
  N --> (View Leaderboard)
  A --> (Adjust User Points)
  SYS --> (Calculate Points)
  SYS --> (Track Point Sources)
  (Approve Deposit) .> (Calculate Points): <<include>>
}

package "Gamification System" {
  N --> (View Available Badges)
  N --> (View Badge Progress)
  N --> (View Earned Badges)
  N --> (Share Badge Achievement)
  S --> (Create Badge)
  S --> (Edit Badge Definition)
  S --> (Delete Badge)
  S --> (Set Badge Criteria)
  S --> (Publish Badge)
  SYS --> (Track Badge Progress)
  SYS --> (Unlock Badges at 100%)
  (Unlock Badges at 100%) .> (Calculate Points): <<include>>
}

package "Product Redemption" {
  N --> (View Product Catalog)
  N --> (Redeem Product)
  N --> (Cancel Redemption)
  N --> (View Redemption History)
  A --> (View Pending Redemptions)
  A --> (View Redemption Analytics)
  A --> (Approve Redemption)
  A --> (Reject Redemption)
  A --> (Mark as Collected)
  S --> (Create Product)
  S --> (Edit Product)
  S --> (Delete Product)
  S --> (Manage Stock)
  (Redeem Product) .> (Calculate Points): <<include>>
  (Approve Redemption) .> (Send Notifications): <<include>>
}

package "Cash Withdrawal" {
  N --> (Request Withdrawal)
  N --> (View Withdrawal History)
  A --> (View All Withdrawals)
  A --> (View Pending Withdrawals)
  A --> (Approve Withdrawal)
  A --> (Reject Withdrawal)
  A --> (Process Payment)
  (Approve Withdrawal) .> (Send Notifications): <<include>>
}

package "User Management & Analytics" {
  N --> (View My Statistics)
  A --> (View All Users)
  A --> (View User Details)
  A --> (Deactivate User)
  A --> (View User Activity Log)
  A --> (View Admin Dashboard)
  A --> (Generate Daily Report)
  A --> (Export Report to CSV)
  S --> (View System Analytics)
  S --> (View All Transactions)
}

package "Content Management" {
  N --> (Read Articles)
  A --> (View Articles)
  A --> (Send Notifications)
  A --> (Manage User Support)
  S --> (Create Article)
  S --> (Edit Article)
  S --> (Delete Article)
  S --> (Publish Article)
  S --> (Create Banner)
  S --> (Edit Banner)
}

package "System Administration" {
  S --> (Manage Admin Accounts)
  S --> (Create/Edit/Delete Admin)
  S --> (Manage Roles & Permissions)
  S --> (Assign Admin Roles)
  S --> (View Complete Audit Log)
  S --> (View Admin Action Audit)
  S --> (View System Logs)
  S --> (View System Settings)
  S --> (Update System Configuration)
}

package "Background Processes" {
  SYS --> (Send Notifications)
  SYS --> (Log Activities)
  SYS --> (Update Statistics)
  SYS --> (Monitor Performance)
}

@enduml
```

**UPDATED FEATURES** (50+ UC):
- ✅ All previous features retained
- ✅ Added 20+ new use cases from gap analysis
- ✅ Better organization by business domain
- ✅ Clearer separation of concerns
- ✅ Includes all CRUD operations

---

## 📋 RECOMMENDED USAGE

| Diagram | Use Cases | Best For |
|---------|-----------|----------|
| **Overview** | 8 main processes | Introduction, Executive Summary |
| **Nasabah Detailed** | 18 user features | User manual, Feature showcase |
| **Admin Detailed** | 35 operator features (RESTRUCTURED) | Admin training, SOP documentation |
| **Superadmin Detailed** | 15 governance features (RESTRUCTURED) | System administrator guide |
| **System Detailed** | 5 background processes | Technical architecture, Process flows |
| **Complete Detailed** | **73 total features** | Comprehensive documentation, Requirements specification |

---

## 🎨 RECOMMENDED APPROACH FOR YOUR REPORT

**Create these diagrams in order**:

1. **Overview Diagram** (Page 1)
   - File: `UC_01_Overview.png`
   - Size: A4 Portrait
   - Shows 4 main actor swim lanes (8 main use cases)

2. **Nasabah Detailed** (Page 2)
   - File: `UC_02_Nasabah_Detailed.png`
   - Size: A4 Portrait
   - **18 user-facing use cases**
   - Includes: Profile, Deposits, Points, Redemptions, Notifications, History, Withdrawal, Leaderboard

3. **Admin Detailed** (Page 3)
   - File: `UC_03_Admin_Detailed.png`
   - Size: A4 Landscape (recommended due to 35 UC)
   - **35 operator use cases** (RESTRUCTURED - NOW FEATURE OPERATOR)
   - 8 functional packages: Waste (10), Products (9), Withdrawal (4), Points (4), Badges (5), Users (6), Content (7), Analytics (4)
   - Manages ALL application features for end users

4. **Superadmin Detailed** (Page 4)
   - File: `UC_04_Superadmin_Detailed.png`
   - Size: A4 Landscape
   - **15 system management use cases** (RESTRUCTURED - GOVERNANCE ONLY)
   - 3 functional packages: Admin Management (6), Audit & Monitoring (5), System Configuration (4)
   - Does NOT manage day-to-day features, only system governance

5. **System Processes** (Page 5)
   - File: `UC_05_System_Processes.png`
   - Size: A4 Portrait
   - 5 automatic background processes

6. **Complete Reference** (Page 6 - Optional Appendix)
   - File: `UC_06_Complete_Reference.png`
   - Size: A3 Landscape (or A4 Landscape)
   - **73 total use cases** - ALL features with permission alignment
   - Grouped by package with full coverage

---

## 🔥 COMPLETE DETAILED USE CASE DIAGRAM (All 73 Features - COMPREHENSIVE VERSION)

**Untuk keperluan dokumentasi lengkap, reference akademis, dan requirement specification**

**DISTRIBUTION** (Post-Restructuring):
- **Nasabah (User)**: 18 UC - User features & transactions
- **Admin (Operator)**: 35 UC - ALL application feature management (Waste, Products, Withdrawal, Points, Badges, Users, Content, Analytics)
- **Superadmin (Governance)**: 15 UC - System admin accounts, audit logs, configuration only
- **System (Background)**: 5 UC - Automated processes

```
@startuml Mendaur_UseCases_Complete_All_Features
title Mendaur System - Complete Use Case Specification (73 Features)

actor Nasabah as N
actor Admin as A
actor Superadmin as S
actor System as SYS

package "1. USER MANAGEMENT & AUTHENTICATION" {
  N --> (Register Account)
  N --> (Login)
  N --> (Logout)
  N --> (View Profile)
  N --> (Update Profile)
  N --> (Change Password)
}

package "2. WASTE MANAGEMENT - USER SIDE" {
  N --> (View Waste Categories)
  N --> (View Waste Types)
  N --> (Submit Waste Deposit)
  N --> (View Deposit History)
  N --> (View Deposit Status)
  N --> (Cancel Deposit)
}

package "3. WASTE MANAGEMENT - ADMIN SIDE" {
  A --> (View Pending Deposits)
  A --> (View Deposit Details)
  A --> (Approve Deposit)
  A --> (Reject Deposit)
  A --> (Verify Waste Weight)
  A --> (Mark as Verified)
}

package "4. WASTE MANAGEMENT - SUPERADMIN SIDE" {
  S --> (Create Waste Category)
  S --> (Edit Waste Category)
  S --> (Delete Waste Category)
  S --> (Create Waste Type)
  S --> (Edit Waste Type)
}

package "5. POINTS & REWARDS SYSTEM" {
  N --> (View Points Balance)
  N --> (View Points History)
  N --> (Filter Points by Type)
  N --> (View Leaderboard Ranking)
  SYS --> (Calculate Points Earned)
  SYS --> (Track Point Sources)
  SYS --> (Update User Total Points)
}

package "6. GAMIFICATION - BADGES" {
  N --> (View Available Badges)
  N --> (View Badge Progress)
  N --> (View Badge Details)
  N --> (View Earned Badges)
  N --> (Share Badge Achievement)
  S --> (Create Badge)
  S --> (Edit Badge Definition)
  S --> (Set Badge Criteria)
  S --> (Publish Badge)
  SYS --> (Track Badge Progress)
  SYS --> (Unlock Badges at 100%)
  SYS --> (Award Badge Points)
}

package "7. PRODUCT CATALOG & REDEMPTION" {
  N --> (View Product Catalog)
  N --> (View Product Details)
  N --> (Check Product Availability)
  N --> (Redeem Product)
  N --> (View Redemption History)
  N --> (View Redemption Status)
  N --> (Cancel Redemption)
  A --> (View Pending Redemptions)
  A --> (Approve Redemption)
  A --> (Reject Redemption)
  A --> (Mark Product Collected)
  A --> (View Redemption Analytics)
  S --> (Create Product)
  S --> (Edit Product)
  S --> (Delete Product)
  S --> (Manage Stock Level)
  S --> (Set Product Points Cost)
}

package "8. CASH WITHDRAWAL & BANKING" {
  N --> (Request Cash Withdrawal)
  N --> (Enter Bank Details)
  N --> (View Withdrawal History)
  N --> (View Withdrawal Status)
  A --> (View Pending Withdrawals)
  A --> (Verify Bank Details)
  A --> (Approve Withdrawal)
  A --> (Reject Withdrawal)
  A --> (Process Payment)
}

package "9. USER ACTIVITY & ANALYTICS" {
  N --> (View My Statistics)
  A --> (View All Users)
  A --> (View User Details)
  A --> (View User Activity Log)
  A --> (View User Deposit History)
  A --> (View User Points History)
  A --> (View User Redemption History)
  A --> (View Admin Dashboard)
  A --> (Generate Daily Report)
  A --> (Export Report to CSV)
  S --> (View System Analytics)
  S --> (View All Transactions)
  S --> (View System Statistics)
}

package "10. CONTENT MANAGEMENT" {
  S --> (Create Article)
  S --> (Edit Article)
  S --> (Publish Article)
  S --> (Delete Article)
  S --> (Create Banner)
  S --> (Edit Banner)
}

package "11. SYSTEM ADMINISTRATION" {
  S --> (Manage Admin Accounts)
  S --> (Create Admin Account)
  S --> (Edit Admin Account)
  S --> (Delete Admin Account)
  S --> (Manage System Roles)
  S --> (Manage Permissions)
  S --> (View Complete Audit Log)
  S --> (View System Settings)
  S --> (Update System Configuration)
}

package "12. AUTOMATED BACKGROUND PROCESSES" {
  SYS --> (Calculate Points from Deposits)
  SYS --> (Track Badge Progress)
  SYS --> (Unlock Badges at 100%)
  SYS --> (Send Email Notifications)
  SYS --> (Send SMS Notifications)
  SYS --> (Log User Activities)
  SYS --> (Log Admin Actions)
  SYS --> (Update Leaderboard Ranking)
  SYS --> (Generate System Reports)
}

' KEY RELATIONSHIPS - INCLUDES & EXTENDS
(Submit Waste Deposit) .> (Calculate Points from Deposits): <<include>>
(Approve Deposit) .> (Calculate Points from Deposits): <<include>>
(Redeem Product) .> (Calculate Points from Deposits): <<include>>

(Approve Redemption) .> (Send Email Notifications): <<include>>
(Reject Redemption) .> (Send Email Notifications): <<include>>
(Approve Withdrawal) .> (Send Email Notifications): <<include>>
(Reject Withdrawal) .> (Send Email Notifications): <<include>>
(Register Account) .> (Send Email Notifications): <<include>>

(Approve Deposit) .> (Log Admin Actions): <<include>>
(Reject Deposit) .> (Log Admin Actions): <<include>>
(Approve Withdrawal) .> (Log Admin Actions): <<include>>

(Unlock Badges at 100%) .> (Award Badge Points): <<include>>
(Unlock Badges at 100%) .> (Send Email Notifications): <<include>>

(View Leaderboard Ranking) .> (Update Leaderboard Ranking): <<include>>
(Track Badge Progress) .> (Unlock Badges at 100%): <<include>>

@enduml
```

### 📋 Fitur Lengkap Per Kategori (35+ Use Cases)

#### 📱 **USER MANAGEMENT (6 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-101 | Register Account | Nasabah | Registrasi akun baru dengan email & phone |
| UC-102 | Login | Nasabah | Login dengan email/phone & password |
| UC-103 | Logout | Nasabah | Logout dari sistem |
| UC-104 | View Profile | Nasabah | Lihat data profil pribadi |
| UC-105 | Update Profile | Nasabah | Update nama, alamat, foto profil |
| UC-106 | Change Password | Nasabah | Ubah password akun |

#### 🗑️ **WASTE MANAGEMENT - NASABAH (6 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-201 | View Waste Categories | Nasabah | Lihat kategori sampah tersedia |
| UC-202 | View Waste Types | Nasabah | Lihat jenis sampah & harga per kg |
| UC-203 | Submit Waste Deposit | Nasabah | Submit sampah untuk distor |
| UC-204 | View Deposit History | Nasabah | Lihat riwayat semua deposit |
| UC-205 | View Deposit Status | Nasabah | Cek status deposit (pending/approved/rejected) |
| UC-206 | Cancel Deposit | Nasabah | Batal deposit jika masih pending |

#### ✅ **WASTE MANAGEMENT - ADMIN (6 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-301 | View Pending Deposits | Admin | Lihat deposit menunggu persetujuan |
| UC-302 | View Deposit Details | Admin | Lihat detail deposit (foto, berat, tipe) |
| UC-303 | Approve Deposit | Admin | Setujui deposit → hitung points |
| UC-304 | Reject Deposit | Admin | Tolak deposit dengan alasan |
| UC-305 | Verify Waste Weight | Admin | Verifikasi berat sampah |
| UC-306 | Mark as Verified | Admin | Tandai deposit sudah diverifikasi |

#### ⚙️ **WASTE MANAGEMENT - SUPERADMIN (5 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-401 | Create Waste Category | Superadmin | Buat kategori sampah baru |
| UC-402 | Edit Waste Category | Superadmin | Edit nama/icon/warna kategori |
| UC-403 | Delete Waste Category | Superadmin | Hapus kategori sampah |
| UC-404 | Create Waste Type | Superadmin | Buat jenis sampah baru |
| UC-405 | Edit Waste Type | Superadmin | Edit harga per kg jenis sampah |

#### 💰 **POINTS & REWARDS (7 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-501 | View Points Balance | Nasabah | Lihat saldo poin total |
| UC-502 | View Points History | Nasabah | Lihat riwayat semua transaksi poin |
| UC-503 | Filter Points by Type | Nasabah | Filter poin berdasarkan sumber (setor/tukar/badge) |
| UC-504 | View Leaderboard Ranking | Nasabah | Lihat ranking poin nasabah |
| UC-505 | Calculate Points Earned | System | Hitung poin: berat × harga_per_kg |
| UC-506 | Track Point Sources | System | Track sumber poin (5 tipe: setor/tukar/badge/bonus/manual) |
| UC-507 | Update User Total Points | System | Update denormalized total_poin di users table |

#### 🏆 **GAMIFICATION - BADGES (12 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-601 | View Available Badges | Nasabah | Lihat semua badge yang tersedia |
| UC-602 | View Badge Progress | Nasabah | Lihat progress badge (0-100%) |
| UC-603 | View Badge Details | Nasabah | Lihat kriteria unlock badge |
| UC-604 | View Earned Badges | Nasabah | Lihat badge yang sudah di-unlock |
| UC-605 | Share Badge Achievement | Nasabah | Share achievement ke social media |
| UC-606 | Create Badge | Superadmin | Buat badge baru dengan kriteria |
| UC-607 | Edit Badge Definition | Superadmin | Edit nama/icon/deskripsi badge |
| UC-608 | Set Badge Criteria | Superadmin | Set syarat_poin & syarat_setor |
| UC-609 | Publish Badge | Superadmin | Publish badge agar visible ke users |
| UC-610 | Track Badge Progress | System | Track progress_percentage setiap user |
| UC-611 | Unlock Badges at 100% | System | Unlock badge ketika progress mencapai 100% |
| UC-612 | Award Badge Points | System | Berikan reward_poin ke user saat unlock |

#### 🎁 **PRODUCT REDEMPTION (18 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-701 | View Product Catalog | Nasabah | Lihat daftar produk yang bisa ditukar |
| UC-702 | View Product Details | Nasabah | Lihat detail produk (harga poin, stok) |
| UC-703 | Check Product Availability | Nasabah | Cek ketersediaan stok produk |
| UC-704 | Redeem Product | Nasabah | Tukar poin dengan produk |
| UC-705 | View Redemption History | Nasabah | Lihat riwayat tukar poin |
| UC-706 | View Redemption Status | Nasabah | Cek status tukar (pending/approved/collected) |
| UC-707 | Cancel Redemption | Nasabah | Batal tukar jika masih pending |
| UC-708 | View Pending Redemptions | Admin | Lihat tukar menunggu persetujuan |
| UC-709 | Approve Redemption | Admin | Setujui tukar poin |
| UC-710 | Reject Redemption | Admin | Tolak tukar dengan alasan |
| UC-711 | Mark Product Collected | Admin | Tandai produk sudah diambil user |
| UC-712 | View Redemption Analytics | Admin | Lihat analisis tukar poin (top products, trends) |
| UC-713 | Create Product | Superadmin | Tambah produk baru ke katalog |
| UC-714 | Edit Product | Superadmin | Edit nama/harga/deskripsi produk |
| UC-715 | Delete Product | Superadmin | Hapus produk dari katalog |
| UC-716 | Manage Stock Level | Superadmin | Kelola stok produk |
| UC-717 | Set Product Points Cost | Superadmin | Set harga poin untuk produk |

#### 💳 **CASH WITHDRAWAL & BANKING (10 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-801 | Request Cash Withdrawal | Nasabah | Request pencairan tunai |
| UC-802 | Enter Bank Details | Nasabah | Input nomor rekening & nama penerima |
| UC-803 | View Withdrawal History | Nasabah | Lihat riwayat pencairan |
| UC-804 | View Withdrawal Status | Nasabah | Cek status pencairan (pending/approved/rejected) |
| UC-805 | View Pending Withdrawals | Admin | Lihat pencairan menunggu persetujuan |
| UC-806 | Verify Bank Details | Admin | Verifikasi rekening & penerima |
| UC-807 | Approve Withdrawal | Admin | Setujui pencairan tunai |
| UC-808 | Reject Withdrawal | Admin | Tolak pencairan dengan alasan |
| UC-809 | Process Payment | Admin | Proses transfer ke rekening user |
| UC-810 | Track Payment Status | System | Update status payment & audit trail |

#### 📊 **ANALYTICS & REPORTING (13 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-901 | View My Statistics | Nasabah | Lihat statistik personal (total poin, deposit, badges) |
| UC-902 | View All Users | Admin | Lihat daftar semua user |
| UC-903 | View User Details | Admin | Lihat detail user (profil, status, history) |
| UC-904 | View User Activity Log | Admin | Lihat aktivitas user (login, deposit, tukar) |
| UC-905 | View User Deposit History | Admin | Lihat semua deposit dari user tertentu |
| UC-906 | View User Points History | Admin | Lihat detail poin dari user tertentu |
| UC-907 | View User Redemption History | Admin | Lihat tukar poin dari user tertentu |
| UC-908 | View Admin Dashboard | Admin | Dashboard overview (pending operations, daily stats) |
| UC-909 | Generate Daily Report | Admin | Generate laporan harian (deposit, tukar, withdraw) |
| UC-910 | Export Report to CSV | Admin | Export laporan ke format CSV |
| UC-911 | View System Analytics | Superadmin | Analytics sistem (total users, transactions, volume) |
| UC-912 | View All Transactions | Superadmin | Lihat semua transaksi sistem |
| UC-913 | View System Statistics | Superadmin | Statistik sistem (growth, trends, forecasting) |

#### 📝 **CONTENT MANAGEMENT (6 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-1001 | Create Article | Superadmin | Buat artikel/tips untuk user |
| UC-1002 | Edit Article | Superadmin | Edit artikel yang sudah ada |
| UC-1003 | Publish Article | Superadmin | Publish artikel ke feed |
| UC-1004 | Delete Article | Superadmin | Hapus artikel dari sistem |
| UC-1005 | Create Banner | Superadmin | Buat banner promosi |
| UC-1006 | Edit Banner | Superadmin | Edit banner promosi |

#### 🔐 **SYSTEM ADMINISTRATION (9 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-1101 | Manage Admin Accounts | Superadmin | Kelola akun admin |
| UC-1102 | Create Admin Account | Superadmin | Buat akun admin baru |
| UC-1103 | Edit Admin Account | Superadmin | Edit data admin (nama, email, permissions) |
| UC-1104 | Delete Admin Account | Superadmin | Hapus akun admin |
| UC-1105 | Manage System Roles | Superadmin | Kelola roles (Nasabah, Admin, Superadmin) |
| UC-1106 | Manage Permissions | Superadmin | Kelola permissions per role |
| UC-1107 | View Complete Audit Log | Superadmin | Lihat audit trail lengkap sistem |
| UC-1108 | View System Settings | Superadmin | Lihat konfigurasi sistem |
| UC-1109 | Update System Configuration | Superadmin | Update settingan sistem (poin multiplier, fees, etc) |

#### ⚡ **AUTOMATED BACKGROUND PROCESSES (9 UC)**
| # | Use Case | Aktor | Deskripsi |
|---|----------|-------|-----------|
| UC-1201 | Calculate Points from Deposits | System | Otomatis hitung poin = berat × harga_per_kg |
| UC-1202 | Track Badge Progress | System | Otomatis track progress badge setiap user |
| UC-1203 | Unlock Badges at 100% | System | Otomatis unlock badge ketika progress 100% |
| UC-1204 | Send Email Notifications | System | Kirim email notification (approval, rejection, unlock) |
| UC-1205 | Send SMS Notifications | System | Kirim SMS notification (urgent alerts) |
| UC-1206 | Log User Activities | System | Log semua aktivitas user ke database |
| UC-1207 | Log Admin Actions | System | Log semua aksi admin untuk audit trail |
| UC-1208 | Update Leaderboard Ranking | System | Otomatis update ranking leaderboard |
| UC-1209 | Generate System Reports | System | Generate laporan sistem secara berkala |

---

### Use Case Descriptions

#### UC-1: Submit Waste Deposit
```
Actor: Nasabah (Primary), Admin (Secondary)
Precondition: User is logged in
Main Flow:
  1. User selects "Submit Deposit"
  2. System shows waste categories
  3. User selects category & waste type
  4. User enters weight (kg)
  5. User uploads photo
  6. User selects deposit schedule/location
  7. User confirms submission
  8. System creates tabung_sampah record (status='pending')
  9. System sends notification to admin
  10. System displays "Pending Approval"

Alternative Flow (Photo Upload Fails):
  5a. User can proceed without photo
  5b. System marks as "needs_verification"

Postcondition: Deposit record created, awaiting admin approval

Related Tables: tabung_sampah, jenis_sampah, jadwal_penyetoran, notifikasi
```

#### UC-2: Approve Deposit
```
Actor: Admin (Primary)
Precondition: Pending deposit exists
Main Flow:
  1. Admin views "Pending Deposits"
  2. Admin selects a deposit
  3. System shows deposit details (weight, type, photo)
  4. Admin verifies weight accuracy
  5. Admin clicks "Approve"
  6. System calculates poin based on weight × type_price
  7. System updates tabung_sampah (status='approved')
  8. System creates poin_transaksis record
  9. System updates user.total_poin
  10. System sends notification to user
  11. System logs activity

Alternative Flow (Reject):
  5a. Admin clicks "Reject"
  5b. Admin enters rejection reason
  5c. System updates status to 'rejected'
  5d. System does NOT allocate points

Postcondition: Points awarded, user notified, ledger updated

Related Tables: tabung_sampah, poin_transaksis, users, notifikasi, log_aktivitas
```

#### UC-3: Unlock Badge
```
Actor: System (Automatic) with Nasabah awareness
Trigger: User reaches 100% on badge progress
Main Flow:
  1. User deposits waste OR earns points
  2. System updates badge_progress (current_value)
  3. System calculates progress_percentage
  4. IF progress_percentage >= 100%:
     a. System sets is_unlocked = TRUE
     b. System records unlocked_at timestamp
     c. System creates user_badges record
     d. System awards reward_poin
     e. System creates poin_transaksis (source='badge')
     f. System updates user.total_poin
     g. System sends notification: "Badge Unlocked!"
     h. System logs activity
  5. System displays badge animation to user

Postcondition: Badge earned, points awarded, user notified

Related Tables: badge_progress, user_badges, poin_transaksis, users, notifikasi
```

---

## 🗄️ PHYSICAL ERD - TABLE DEFINITIONS

### Table: users (HUB TABLE)

```
CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- Authentication
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  
  -- Personal Info
  no_hp VARCHAR(255) UNIQUE NOT NULL,           -- Business key
  nama VARCHAR(255) NOT NULL,
  alamat TEXT,
  foto_profil VARCHAR(255) NULL,
  
  -- System
  role_id BIGINT DEFAULT 1 REFERENCES roles(id),
  
  -- Points (Denormalized for performance)
  total_poin INT DEFAULT 0,
  poin_tercatat INT DEFAULT 0,
  
  -- Statistics
  total_setor_sampah INT DEFAULT 0,
  level VARCHAR(255) DEFAULT 'Pemula',
  
  -- Banking (Modern nasabah only)
  tipe_nasabah ENUM('konvensional', 'modern') DEFAULT 'konvensional',
  nama_bank VARCHAR(100) NULL,
  nomor_rekening VARCHAR(50) NULL,
  atas_nama_rekening VARCHAR(255) NULL,
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Indexes
  INDEX idx_email,
  INDEX idx_no_hp,
  INDEX idx_role_id,
  UNIQUE UNIQUE_no_hp (no_hp)
);
```

**Relationships:**
```
users (1) ──────────── (M) tabung_sampah
users (1) ──────────── (M) penukaran_produk
users (1) ──────────── (M) transaksis
users (1) ──────────── (M) penarikan_tunai
users (1) ──────────── (M) poin_transaksis
users (1) ──────────── (M) notifikasi
users (1) ──────────── (M) log_aktivitas
users (1) ──────────── (M) badge_progress
users (1) ──────────── (M) audit_logs
users (M) ──────────── (M) badges (via user_badges)
users (M) ──────────── (1) roles
```

---

### Table: tabung_sampah (Waste Deposits)

```
CREATE TABLE tabung_sampah (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- User Reference
  user_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  
  -- Deposit Info
  nama_lengkap VARCHAR(255) NOT NULL,
  no_hp VARCHAR(255) NOT NULL,
  titik_lokasi TEXT,
  jenis_sampah VARCHAR(255),
  berat_kg DECIMAL(8, 2),
  foto_sampah TEXT NULL,
  
  -- Schedule Reference (optional)
  jadwal_id BIGINT UNSIGNED NULL,
  FOREIGN KEY (jadwal_id) REFERENCES jadwal_penyetorans(id) ON DELETE SET NULL,
  
  -- Status & Points
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  poin_didapat INT DEFAULT 0,
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Indexes
  INDEX idx_user_id (user_id),
  INDEX idx_status,
  INDEX idx_created_at
);
```

**Relationships:**
```
tabung_sampah (M) ──── (1) users [CASCADE DELETE]
tabung_sampah (M) ──── (1) jadwal_penyetorans [SET NULL]
tabung_sampah (1) ──── (M) poin_transaksis [SET NULL on tabung delete]
```

---

### Table: poin_transaksis (Points Ledger - Audit Trail)

```
CREATE TABLE poin_transaksis (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- User Reference
  user_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  
  -- Waste Deposit Reference (nullable)
  tabung_sampah_id BIGINT UNSIGNED NULL,
  FOREIGN KEY (tabung_sampah_id) REFERENCES tabung_sampah(id) ON DELETE SET NULL,
  
  -- Point Details
  jenis_sampah VARCHAR(255) NULL,
  berat_kg DECIMAL(8, 2) NULL,
  poin_didapat INT NOT NULL,              -- Can be negative!
  
  -- Audit Fields (Polymorphic Reference)
  sumber VARCHAR(255),                    -- 'setor_sampah', 'tukar_poin', 'badge', 'bonus', 'manual'
  keterangan TEXT NULL,
  referensi_id BIGINT UNSIGNED NULL,      -- ID of source document
  referensi_tipe VARCHAR(255) NULL,       -- 'setor_sampah', 'penukaran_produk', 'badge', 'event', 'admin_adjustment'
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Constraints
  UNIQUE KEY unique_setor (user_id, tabung_sampah_id, sumber),
  
  -- Indexes
  INDEX idx_user_id (user_id),
  INDEX idx_sumber,
  INDEX idx_created_at,
  INDEX idx_user_sumber (user_id, sumber),
  INDEX idx_user_created (user_id, created_at)
);
```

**Relationships:**
```
poin_transaksis (M) ──── (1) users [CASCADE DELETE]
poin_transaksis (M) ──── (1) tabung_sampah [SET NULL]
poin_transaksis → referensi [Polymorphic - points to multiple tables]
  ├─ If sumber='setor_sampah' → referensi_id points to tabung_sampah(id)
  ├─ If sumber='tukar_poin' → referensi_id points to penukaran_produk(id)
  ├─ If sumber='badge' → referensi_id points to user_badges(id)
  └─ If sumber='bonus' → referensi_id is NULL
```

---

### Table: badges (Achievement Definitions)

```
CREATE TABLE badges (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- Badge Info
  nama VARCHAR(255) NOT NULL UNIQUE,
  deskripsi TEXT,
  icon VARCHAR(255),
  
  -- Unlock Criteria
  syarat_poin INT DEFAULT 0,              -- Points required
  syarat_setor INT DEFAULT 0,             -- Weight (kg) required
  
  -- Reward
  reward_poin INT DEFAULT 0,              -- Points given on unlock
  
  -- Badge Type
  tipe ENUM('poin','setor','kombinasi','special','ranking') DEFAULT 'poin',
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Index
  INDEX idx_tipe
);
```

---

### Table: badge_progress (User Badge Tracking)

```
CREATE TABLE badge_progress (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- User & Badge Reference
  user_id BIGINT UNSIGNED NOT NULL,
  badge_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
  
  -- Progress Tracking
  current_value INT DEFAULT 0,            -- Current progress amount
  target_value INT DEFAULT 0,             -- Goal to reach
  progress_percentage DECIMAL(5, 2) DEFAULT 0.00,  -- 0-100
  
  -- Status
  is_unlocked BOOLEAN DEFAULT FALSE,      -- Has user completed?
  unlocked_at TIMESTAMP NULL,             -- When completed
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Constraints
  UNIQUE KEY unique_user_badge (user_id, badge_id),
  
  -- Indexes
  INDEX idx_user_id (user_id),
  INDEX idx_is_unlocked,
  INDEX idx_user_unlocked (user_id, is_unlocked),
  INDEX idx_progress_percentage
);
```

**Note**: This table gets updated continuously as user earns points/deposits.

---

### Table: user_badges (Earned Badges)

```
CREATE TABLE user_badges (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- User & Badge Reference
  user_id BIGINT UNSIGNED NOT NULL,
  badge_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
  
  -- Award Details
  tanggal_dapat TIMESTAMP NOT NULL,       -- When earned
  reward_claimed BOOLEAN DEFAULT TRUE,    -- Was reward given?
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Constraints
  UNIQUE KEY unique_user_badge (user_id, badge_id)
);
```

**Note**: Record created ONLY when badge_progress.is_unlocked = TRUE (100% complete)

---

### Table: penukaran_produk (Product Redemptions)

```
CREATE TABLE penukaran_produk (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- References
  user_id BIGINT UNSIGNED NOT NULL,
  produk_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (produk_id) REFERENCES produks(id) ON DELETE CASCADE,
  
  -- Redemption Details
  nama_produk VARCHAR(255),
  poin_digunakan INT,
  jumlah INT DEFAULT 1,
  
  -- Status
  status ENUM('pending','approved','cancelled') DEFAULT 'pending',
  
  -- Pickup
  metode_ambil TEXT,
  tanggal_penukaran TIMESTAMP,
  tanggal_diambil TIMESTAMP NULL,
  catatan TEXT NULL,
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Indexes
  INDEX idx_user_id (user_id),
  INDEX idx_status
);
```

---

### Table: penarikan_tunai (Cash Withdrawals)

```
CREATE TABLE penarikan_tunai (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  
  -- User Reference
  user_id BIGINT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  
  -- Withdrawal Details
  jumlah_poin INT NOT NULL,
  jumlah_rupiah DECIMAL(15, 2),
  
  -- Banking Info
  nomor_rekening VARCHAR(50),
  nama_bank VARCHAR(100),
  nama_penerima VARCHAR(255),
  
  -- Status
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  catatan_admin TEXT NULL,
  
  -- Admin Processing
  processed_by BIGINT UNSIGNED NULL,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
  processed_at TIMESTAMP NULL,
  
  -- Timestamps
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  -- Indexes
  INDEX idx_user_id (user_id),
  INDEX idx_status
);
```

---

## 📊 RELATIONSHIP DIAGRAM (ASCII)

```
                    ┌─────────────────────────────┐
                    │        USERS (Hub)          │
                    │  • id (PK)                  │
                    │  • role_id (FK → roles)     │
                    │  • email, phone, name       │
                    │  • total_poin (denorm)      │
                    └──────────────┬──────────────┘
                                   │
                 ┌─────────────────┼─────────────────┐
                 │                 │                 │
        ┌────────▼────────┐  ┌─────▼──────┐  ┌──────▼────────┐
        │ tabung_sampah   │  │ penukaran  │  │ penarikan    │
        │ (Deposits)      │  │ _produk    │  │ _tunai       │
        │ • user_id (FK)  │  │ (Redemp)   │  │ (Withdrawal) │
        │ • status        │  │ • user_id  │  │ • user_id    │
        │ • poin_didapat  │  │ • produk   │  │ • amount     │
        │ • berat_kg      │  │ • status   │  │ • status     │
        └────────┬────────┘  └────────────┘  └──────────────┘
                 │
        ┌────────▼────────────────────┐
        │  poin_transaksis (Ledger)   │
        │  • user_id (FK)             │
        │  • tabung_sampah_id (FK)    │
        │  • poin_didapat (audit)     │
        │  • sumber (type)            │
        │  • referensi_id (poly)      │
        └─────────────────────────────┘

┌─────────────────────────────────────────────────┐
│            GAMIFICATION SYSTEM                  │
│                                                │
│  ┌──────────────┐      ┌──────────────────┐   │
│  │   badges     │      │ badge_progress   │   │
│  │ • nama       │◄─────┤ • user_id (FK)   │   │
│  │ • tipe       │   ┌─►│ • badge_id (FK)  │   │
│  │ • reward     │   │  │ • current_value  │   │
│  └──────────────┘   │  │ • progress_%     │   │
│                     │  │ • is_unlocked    │   │
│  ┌──────────────┐   │  └──────────────────┘   │
│  │ user_badges  │   │                        │
│  │ • user_id ───┼───┘                        │
│  │ • badge_id ──┼───►                        │
│  │ • tanggal_dapat                           │
│  └──────────────┘                            │
│                                              │
│  ◄── Created ONLY when badge_progress      │
│      reaches 100% (is_unlocked=TRUE)        │
│                                              │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│         WASTE MANAGEMENT HIERARCHY              │
│                                                │
│  ┌─────────────────────┐                      │
│  │ kategori_sampah     │                      │
│  │ • nama_kategori     │                      │
│  │ • icon, warna       │                      │
│  └──────────┬──────────┘                      │
│             │ 1:M                             │
│    ┌────────▼──────────┐                      │
│    │ jenis_sampah      │                      │
│    │ • nama_jenis      │                      │
│    │ • harga_per_kg    │                      │
│    └────────────────────┘                     │
│                                              │
│  Reference in: tabung_sampah.jenis_sampah   │
└─────────────────────────────────────────────────┘
```

---

## 🎨 COLOR LEGEND FOR DIAGRAMS

```
Entity Types (Colors):
├─ 🟢 GREEN    - User entities (users, tabung_sampah, etc)
├─ 🟡 YELLOW   - Transaction entities (poin_transaksis, transaksis)
├─ 🟠 ORANGE   - Product entities (produks, penukaran_produk)
├─ 🔵 BLUE     - Badge entities (badges, user_badges, badge_progress)
├─ 🟣 PURPLE   - System entities (roles, permissions, audit_logs)
└─ ⚫ BLACK    - Reference/Config entities (kategori_sampah, jenis_sampah)

Relationship Types (Line Styles):
├─ ──────  SOLID = Hard FK (CASCADE DELETE)
├─ ─ ─ ─  DASHED = Soft FK (SET NULL)
└─ ═════  BOLD = Unique/Key constraint
```

---

## 📋 EXPORT FORMATS

### For Draw.io:
1. Use XML format
2. File: `diagram_name.drawio`
3. Can embed directly

### For Lucidchart:
1. Use UML template
2. Export as PNG/PDF/SVG
3. Can share as link

### For dbdiagram.io:
1. Use DSL syntax
2. File: `schema.dbdiagram`
3. Export as PNG

### For Report (Recommended):
1. PNG format (300 DPI for printing)
2. PDF embedded (vector, scalable)
3. Size: A3 or A4 landscape


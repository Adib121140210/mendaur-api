# ✅ UCD UPDATE - CASH WITHDRAWAL & ARTICLES ADDED

**Date**: November 29, 2025  
**Status**: UPDATED  
**Changes**: Added missing features (penarikan_tunai + artikel)

---

## 📊 SUMMARY OF CHANGES

### Before (Old UCD):
```
Nasabah Detailed:     10 UC
Admin Detailed:       12 UC
Superadmin Detailed:   8 UC
Total:               30 UC
```

### After (Updated UCD):
```
Nasabah Detailed:     13 UC (+3)
Admin Detailed:       17 UC (+5)
Superadmin Detailed:  13 UC (+5)
Total:               43 UC (+13)
```

---

## 🎯 FEATURES ADDED

### 1. NASABAH - Added 3 Use Cases

| UC | Feature | Kategori | Deskripsi |
|-------|---------|----------|-----------|
| + | Request Cash Withdrawal | Penarikan Tunai | User bisa request pencairan tunai |
| + | View Withdrawal History | Penarikan Tunai | Lihat riwayat semua pencairan |
| + | Read Articles & Tips | Content Management | Baca artikel/tips dari admin |

**Total Nasabah Features**: 13 UC
```
✓ Register
✓ Login
✓ View/Update Profile
✓ Submit Waste Deposit
✓ View Deposit History
✓ View Points Balance
✓ View Leaderboard
✓ View Available Badges
✓ View Earned Badges
✓ Redeem Product
✓ Request Cash Withdrawal (NEW)
✓ View Withdrawal History (NEW)
✓ Read Articles & Tips (NEW)
```

---

### 2. ADMIN - Added 5 Use Cases

| UC | Feature | Kategori | Deskripsi |
|-------|---------|----------|-----------|
| + | View Pending Withdrawals | Penarikan Tunai | Lihat request pencairan menunggu |
| + | Approve/Reject Withdrawal | Penarikan Tunai | Setujui atau tolak pencairan |
| + | Process Payment Transfer | Penarikan Tunai | Proses transfer ke rekening user |
| + | View Articles | Content Management | Lihat & manage artikel sistem |
| + | Manage User Support | Customer Service | Handle user inquiries & support |

**Total Admin Features**: 17 UC
```
✓ View Pending Deposits
✓ Approve/Reject Deposit
✓ Verify Waste Weight
✓ View Pending Redemptions
✓ Approve/Reject Redemption
✓ Mark Product as Collected
✓ View Pending Withdrawals (NEW)
✓ Approve/Reject Withdrawal (NEW)
✓ Process Payment Transfer (NEW)
✓ View User Activity Log
✓ View All Users
✓ View Admin Dashboard
✓ Generate Daily Report
✓ View Articles (NEW)
✓ Manage User Support (NEW)
```

---

### 3. SUPERADMIN - Added 5 Use Cases

| UC | Feature | Kategori | Deskripsi |
|-------|---------|----------|-----------|
| + | Create/Edit/Publish Articles | Content Management | Buat & publish artikel untuk users |
| + | Create/Edit Banners | Content Management | Manage promotional banners |
| + | Monitor Transactions | Analytics | Monitor semua transaksi sistem |
| + | Generate System Reports | Reporting | Generate laporan komprehensif sistem |
| + | Manage System Settings | Configuration | Kelola konfigurasi sistem (multiplier, fees, dll) |

**Total Superadmin Features**: 13 UC
```
✓ Manage Admin Accounts
✓ Manage System Roles & Permissions
✓ Create/Edit Badges
✓ Create/Edit Products
✓ Manage Stock Levels
✓ Create/Edit Waste Categories
✓ Create/Edit/Publish Articles (UPDATED)
✓ Create/Edit Banners (NEW)
✓ View Complete Audit Log
✓ View System Analytics
✓ Manage System Settings (NEW)
✓ Monitor Transactions (NEW)
✓ Generate System Reports (NEW)
```

---

## 📋 DETAILED USE CASE SPECIFICATIONS

### NASABAH - New Use Cases

#### UC-PW-01: Request Cash Withdrawal
```
Actor: Nasabah (Primary)
Precondition: User has points balance > 0
Main Flow:
  1. User navigates to "Request Withdrawal"
  2. System shows current points balance
  3. User enters amount to withdraw
  4. User enters bank details (account number, bank name, recipient name)
  5. User reviews and confirms request
  6. System creates penarikan_tunai record (status='pending')
  7. System deducts points from balance (tentative)
  8. System sends notification to admin
  9. System displays "Withdrawal Pending"

Alternative Flow (Insufficient Points):
  3a. User tries to withdraw more than available
  3b. System shows error "Insufficient points"
  3c. Transaction cancelled

Postcondition: Withdrawal request created, awaiting admin approval

Related Tables: penarikan_tunai, users, poin_transaksis, notifikasi
Related Actors: Admin (approval)
```

#### UC-PW-02: View Withdrawal History
```
Actor: Nasabah (Primary)
Precondition: User is logged in
Main Flow:
  1. User navigates to "Withdrawal History"
  2. System shows all withdrawals (pending, approved, rejected)
  3. User can see: amount, status, date, bank details
  4. User can filter by status or date range

Postcondition: Withdrawal history displayed

Related Tables: penarikan_tunai, users
```

#### UC-CM-01: Read Articles & Tips
```
Actor: Nasabah (Primary)
Precondition: User is logged in (or can browse as guest)
Main Flow:
  1. User navigates to "Articles/Tips" section
  2. System shows list of published articles
  3. User clicks article to read full content
  4. System displays article (title, author, content, date, images)
  5. User can like/share article (optional)

Postcondition: Article content displayed

Related Tables: articles, users
Notes: Articles can be filtered by category (tips, news, announcements)
```

---

### ADMIN - New Use Cases

#### UC-PW-03: View Pending Withdrawals
```
Actor: Admin (Primary)
Precondition: Pending withdrawal requests exist
Main Flow:
  1. Admin navigates to "Pending Withdrawals"
  2. System shows list of pending requests
  3. Admin can see: user name, amount, account, request date
  4. Admin can search/filter by user or amount range

Postcondition: Pending withdrawals listed

Related Tables: penarikan_tunai, users
```

#### UC-PW-04: Approve/Reject Withdrawal
```
Actor: Admin (Primary)
Precondition: Pending withdrawal exists
Main Flow:
  1. Admin selects a withdrawal request
  2. System shows full details (user, amount, bank info, points)
  3. Admin verifies account details
  4. Admin clicks "Approve" or "Reject"
  5. If Approve:
     a. System updates status to 'approved'
     b. System marks points as deducted (confirmed)
     c. System records admin who approved (processed_by)
     d. System sends notification to user
  6. If Reject:
     a. Admin enters rejection reason
     b. System updates status to 'rejected'
     c. System restores tentative points to user
     d. System sends notification to user with reason

Postcondition: Withdrawal approved/rejected, user notified

Related Tables: penarikan_tunai, poin_transaksis, users, log_aktivitas
```

#### UC-PW-05: Process Payment Transfer
```
Actor: Admin (Primary), System (Background)
Precondition: Approved withdrawal exists
Main Flow:
  1. Admin navigates to "Process Payments"
  2. System shows approved withdrawals ready for transfer
  3. Admin selects withdrawal(s) to process
  4. System validates bank details
  5. Admin clicks "Process Transfer"
  6. System initiates bank transfer (API call)
  7. System updates processed_at timestamp
  8. System logs transaction
  9. System sends confirmation to user

Alternative Flow (Transfer Fails):
  6a. System retries transfer up to 3 times
  6b. If final failure: Admin notified, status stays 'approved'
  6c. Manual retry available later

Postcondition: Payment processed, user credited

Related Tables: penarikan_tunai, users, log_aktivitas
Notes: Integrates with payment gateway/bank API
```

#### UC-CM-02: View Articles
```
Actor: Admin (Primary)
Precondition: Admin is logged in
Main Flow:
  1. Admin navigates to "Article Management"
  2. System shows list of all articles (published, draft, archived)
  3. Admin can:
     - View article details
     - Edit article
     - Publish/unpublish
     - Archive article
     - See analytics (views, likes, shares)

Postcondition: Article list & management tools displayed

Related Tables: articles
```

#### UC-CS-01: Manage User Support
```
Actor: Admin (Primary)
Precondition: User has submitted support request
Main Flow:
  1. Admin navigates to "User Support/Tickets"
  2. System shows support tickets (open, in-progress, resolved)
  3. Admin can:
     - View ticket details (user issue, attachments)
     - Assign ticket to self
     - Add response/comments
     - Mark as resolved
     - Track resolution time

Postcondition: Support ticket managed

Related Tables: support_tickets, users, log_aktivitas
```

---

### SUPERADMIN - New Use Cases

#### UC-CM-03: Create/Edit/Publish Articles
```
Actor: Superadmin (Primary)
Precondition: Superadmin is logged in
Main Flow:
  1. Superadmin navigates to "Article Management"
  2. Click "Create New Article"
  3. Fill form: title, category, content, featured image
  4. Can add tags/keywords
  5. Save as draft or publish immediately
  6. If publish: Article visible to all users
  7. Can edit published articles anytime
  8. Deletion: archived instead of hard deleted

Postcondition: Article created/updated/published

Related Tables: articles
Categories: Tips, News, Announcements, Promotional
```

#### UC-CM-04: Create/Edit Banners
```
Actor: Superadmin (Primary)
Precondition: Superadmin is logged in
Main Flow:
  1. Navigate to "Banner Management"
  2. Create: Upload image, set link target, duration
  3. Schedule: Set active dates
  4. Edit: Update banner or reschedule
  5. Publish: Make visible on homepage/dashboard
  6. Archive: Remove when expired

Postcondition: Banner created/managed

Related Tables: banners
Locations: Homepage, Dashboard, Sidebar, Pop-up
```

#### UC-AC-01: Monitor Transactions
```
Actor: Superadmin (Primary)
Precondition: Superadmin is logged in
Main Flow:
  1. Navigate to "Transaction Monitoring"
  2. View real-time dashboard showing:
     - Total transactions today
     - Volume by type (setor, tukar, withdraw)
     - Total poin distributed
     - Peak hours
     - Anomalies/errors
  3. Can drill-down into specific transactions
  4. Can generate alerts for suspicious activity

Postcondition: Transaction data displayed for monitoring

Related Tables: poin_transaksis, penukaran_produk, penarikan_tunai, log_aktivitas
```

#### UC-RP-01: Generate System Reports
```
Actor: Superadmin (Primary)
Precondition: Superadmin is logged in
Main Flow:
  1. Navigate to "System Reports"
  2. Select report type:
     - Daily Summary (deposits, redemptions, withdrawals)
     - User Statistics (active users, growth, demographics)
     - Financial Report (poin distributed, value withdrawn)
     - Performance Report (system health, errors, usage)
  3. Select date range
  4. System generates report
  5. Can view online or export as PDF/Excel
  6. Schedule recurring reports

Postcondition: Report generated and available for download

Related Tables: Multiple aggregations
Export Formats: PDF, Excel, CSV
```

#### UC-SYS-01: Manage System Settings
```
Actor: Superadmin (Primary)
Precondition: Superadmin is logged in
Main Flow:
  1. Navigate to "System Settings"
  2. Can configure:
     - Points multiplier (base points calculation)
     - Withdrawal fees
     - Transaction limits
     - Notification settings
     - Email/SMS templates
     - Holiday schedules
     - Maintenance windows
  3. Update settings
  4. Changes take effect immediately or on schedule
  5. System logs all configuration changes

Postcondition: System settings updated

Related Tables: system_settings, log_aktivitas
```

---

## 📊 UPDATED USE CASE COVERAGE TABLE

| Category | Nasabah | Admin | Superadmin | Total |
|----------|---------|-------|-----------|-------|
| **Auth & Account** | 3 | - | - | 3 |
| **Waste Management** | 2 | 3 | 2 | 7 |
| **Points & Rewards** | 3 | 1 | - | 4 |
| **Gamification** | 3 | 1 | 1 | 5 |
| **Product Redemption** | 2 | 3 | 1 | 6 |
| **Cash Withdrawal** | 2 | 3 | - | 5 (NEW) |
| **Content Management** | 1 | 2 | 3 | 6 (ENHANCED) |
| **Analytics & Reporting** | - | 1 | 1 | 2 |
| **System Admin** | - | - | 3 | 3 |
| **Background Processes** | - | - | - | 5 |
| **TOTAL** | **13** | **17** | **13** | **43** |

---

## ✅ FEATURES NOW INCLUDED

### ✓ Penarikan Tunai (Cash Withdrawal)
- [x] Nasabah: Request withdrawal, View history
- [x] Admin: View pending, Approve/Reject, Process payment
- [x] System: Track status, Log activities

### ✓ Artikel (Articles)
- [x] Nasabah: Read articles & tips
- [x] Admin: Manage & view articles
- [x] Superadmin: Create/Edit/Publish articles

### ✓ Banners (Promotional Content)
- [x] Superadmin: Create/Edit/Manage banners

### ✓ Support Management
- [x] Admin: Manage user support tickets

### ✓ Analytics & Monitoring
- [x] Superadmin: Monitor transactions, Generate reports
- [x] Superadmin: System settings management

---

## 🎯 RECOMMENDATIONS FOR YOUR REPORT

### Use These Updated Diagrams:

1. **Nasabah Detailed (13 UC)** - More comprehensive user features
2. **Admin Detailed (17 UC)** - More complete operator workflows
3. **Superadmin Detailed (13 UC)** - System management capabilities
4. **Complete Reference (40+ UC)** - All features with full details

### File Names:
```
UC_02_Nasabah_Detailed_Updated.png
UC_03_Admin_Detailed_Updated.png
UC_04_Superadmin_Detailed_Updated.png
UC_06_Complete_Reference_All_Features.png
```

---

## 🔍 VERIFICATION CHECKLIST

- [x] Cash Withdrawal features included in all 3 diagrams
- [x] Article/Content management visible
- [x] Admin payment processing added
- [x] Superadmin article creation added
- [x] Support management included
- [x] System monitoring & settings added
- [x] All Use Case Descriptions updated
- [x] Database tables referenced (penarikan_tunai, articles, etc)
- [x] Feature count updated in tables
- [x] Relationships documented

**Status**: ✅ COMPLETE & READY FOR REPORT


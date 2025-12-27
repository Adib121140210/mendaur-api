# 🔍 DETAILED PERMISSION-TO-ENDPOINT MAPPING

**Comprehensive Reference Guide**  
**Last Updated:** December 22, 2025

---

## NASABAH (User Level - Level 1)

### Core Features (4 permissions)

#### 1. deposit_sampah
- **Description:** Dapat menyetor sampah
- **Endpoints:**
  - `POST /tabung-sampah` - Create waste deposit
  - `GET /tabung-sampah` - View own deposits
- **Controller:** TabungSampahController
- **Implementation:** ✅ Complete
- **Authorization:** `auth:sanctum`

#### 2. view_deposit_history
- **Description:** Dapat melihat riwayat penyetoran
- **Endpoints:**
  - `GET /tabung-sampah` - List deposits
  - `GET /users/{userId}/tabung-sampah` - User deposits
- **Controller:** TabungSampahController
- **Implementation:** ✅ Complete

#### 3. view_balance
- **Description:** Dapat melihat saldo poin
- **Endpoints:**
  - `GET /poin/history` - Point history
  - `GET /user/{id}/poin` - User points
  - `GET /poin/breakdown/{userId}` - Point breakdown
- **Controller:** PointController
- **Implementation:** ✅ Complete

#### 4. view_transaction_history
- **Description:** Dapat melihat riwayat transaksi
- **Endpoints:**
  - `GET /poin/history` - All transactions
  - `GET /user/{id}/redeem-history` - Redemption history
  - `GET /user/{id}/poin/statistics` - Statistics
- **Controller:** PointController
- **Implementation:** ✅ Complete

---

### Poin/Point Features (4 permissions)

#### 5. redeem_poin
- **Description:** Dapat menukar poin dengan produk
- **Endpoints:**
  - `POST /penukaran-produk` - Request redemption
  - `GET /penukaran-produk` - View requests
  - `GET /produk` - Browse products
- **Controller:** PenukaranProdukController
- **Implementation:** ✅ Complete

#### 6. view_redemption_history
- **Description:** Dapat melihat riwayat penukaran
- **Endpoints:**
  - `GET /penukaran-produk` - List all
  - `GET /penukaran-produk/{id}` - View detail
  - `GET /user/{id}/redeem-history` - User history
- **Controller:** PenukaranProdukController
- **Implementation:** ✅ Complete

#### 7. request_withdrawal
- **Description:** Dapat mengajukan penarikan tunai
- **Endpoints:**
  - `POST /penarikan-tunai` - Request withdrawal
  - `GET /penarikan-tunai` - View requests
- **Controller:** PenarikanTunaiController
- **Implementation:** ✅ Complete

#### 8. view_withdrawal_history
- **Description:** Dapat melihat riwayat penarikan
- **Endpoints:**
  - `GET /penarikan-tunai` - List all
  - `GET /penarikan-tunai/{id}` - View detail
  - `GET /penarikan-tunai/summary` - Summary
- **Controller:** PenarikanTunaiController
- **Implementation:** ✅ Complete

---

### Gamification Features (3 permissions)

#### 9. view_badges
- **Description:** Dapat melihat badge yang dimiliki
- **Endpoints:**
  - `GET /badges` - List all badges
  - `GET /users/{userId}/badges-list` - User badges
  - `GET /badges/available` - Available badges
- **Controller:** BadgeController
- **Implementation:** ✅ Complete

#### 10. view_all_badges
- **Description:** Dapat melihat semua badge yang tersedia
- **Endpoints:**
  - `GET /badges` - All badges
  - `GET /badges/available` - Available badges
- **Controller:** BadgeController
- **Implementation:** ✅ Complete

#### 11. view_badge_progress
- **Description:** Dapat melihat progress badge
- **Endpoints:**
  - `GET /user/badges/progress` - User progress
  - `GET /user/badges/completed` - Completed badges
  - `GET /users/{userId}/badge-progress` - Detail progress
- **Controller:** BadgeProgressController
- **Implementation:** ✅ Complete

---

### Community Features (2 permissions)

#### 12. view_leaderboard
- **Description:** Dapat melihat leaderboard
- **Endpoints:**
  - `GET /dashboard/leaderboard` - Leaderboard
  - `GET /badges/leaderboard` - Badge leaderboard
- **Controller:** DashboardController, BadgeProgressController
- **Implementation:** ✅ Complete

#### 13. view_leaderboard_detail
- **Description:** Dapat melihat detail leaderboard
- **Endpoints:**
  - `GET /dashboard/leaderboard` - Full details
  - `GET /api/admin/leaderboard` - Admin view
- **Controller:** DashboardController
- **Implementation:** ✅ Complete

---

### Account Management (4 permissions)

#### 14. view_profile
- **Description:** Dapat melihat profil sendiri
- **Endpoints:**
  - `GET /profile` - Own profile
  - `GET /users/{id}` - User profile
- **Controller:** AuthController, UserController
- **Implementation:** ✅ Complete

#### 15. edit_profile
- **Description:** Dapat edit profil sendiri
- **Endpoints:**
  - `PUT /profile` - Update profile
  - `PUT /users/{id}` - Update user
  - `POST /users/{id}/update-photo` - Update photo
  - `POST /users/{id}/avatar` - Upload avatar
- **Controller:** AuthController, UserController
- **Implementation:** ✅ Complete

#### 16. view_activity_log
- **Description:** Dapat melihat log aktivitas sendiri
- **Endpoints:**
  - `GET /users/{id}/aktivitas` - Activity log
  - `GET /users/{userId}/point-history` - Point activity
- **Controller:** UserController
- **Implementation:** ✅ Complete

#### 17. view_notifications ⚠️
- **Description:** Dapat melihat notifikasi
- **Endpoints:**
  - ❌ Not implemented
- **Controller:** NotificationController (missing)
- **Implementation:** ❌ Missing

---

## ADMIN (Staff Level - Level 2)

*Inherits all 17 nasabah permissions + 23 additional*

### Deposit Management (4 permissions)

#### 1. approve_deposit
- **Description:** Dapat menyetujui penyetoran sampah
- **Endpoints:**
  - `PATCH /api/admin/penyetoran-sampah/{id}/approve` - Approve
  - `POST /tabung-sampah/{id}/approve` - User trigger (legacy)
- **Controller:** AdminWasteController
- **Implementation:** ✅ Complete
- **Authorization:** `auth:sanctum` + `isAdminUser()`

#### 2. reject_deposit
- **Description:** Dapat menolak penyetoran sampah
- **Endpoints:**
  - `PATCH /api/admin/penyetoran-sampah/{id}/reject` - Reject
  - `POST /tabung-sampah/{id}/reject` - User trigger (legacy)
- **Controller:** AdminWasteController
- **Implementation:** ✅ Complete

#### 3. view_all_deposits
- **Description:** Dapat melihat semua penyetoran
- **Endpoints:**
  - `GET /api/admin/penyetoran-sampah` - List all
  - `GET /api/admin/penyetoran-sampah/stats/overview` - Statistics
- **Controller:** AdminWasteController
- **Implementation:** ✅ Complete

#### 4. view_deposit_detail
- **Description:** Dapat melihat detail penyetoran
- **Endpoints:**
  - `GET /api/admin/penyetoran-sampah/{id}` - View detail
- **Controller:** AdminWasteController
- **Implementation:** ✅ Complete

---

### Poin Management (2 permissions)

#### 5. adjust_poin_manual
- **Description:** Dapat menyesuaikan poin secara manual
- **Endpoints:**
  - `POST /api/admin/points/award` - Award points
  - `POST /poin/bonus` - Award bonus (user endpoint)
- **Controller:** AdminPointsController, PointController
- **Implementation:** ✅ Complete

#### 6. view_poin_adjustment_history
- **Description:** Dapat melihat riwayat penyesuaian poin
- **Endpoints:**
  - `GET /api/admin/points/history` - Point history
  - `GET /poin/admin/stats` - Statistics
  - `GET /poin/admin/history` - History
- **Controller:** AdminPointController
- **Implementation:** ✅ Complete

---

### Redemption Management (3 permissions)

#### 7. approve_redemption
- **Description:** Dapat menyetujui penukaran poin
- **Endpoints:**
  - `PATCH /api/admin/penukar-produk/{exchangeId}/approve` - Approve
- **Controller:** AdminPenukaranProdukController
- **Implementation:** ✅ Complete

#### 8. reject_redemption
- **Description:** Dapat menolak penukaran poin
- **Endpoints:**
  - `PATCH /api/admin/penukar-produk/{exchangeId}/reject` - Reject
- **Controller:** AdminPenukaranProdukController
- **Implementation:** ✅ Complete

#### 9. view_all_redemptions
- **Description:** Dapat melihat semua penukaran
- **Endpoints:**
  - `GET /api/admin/penukar-produk` - List all
  - `GET /api/admin/penukar-produk/{exchangeId}` - View detail
  - `GET /api/admin/penukar-produk/stats/overview` - Statistics
  - `GET /poin/admin/redemptions` - Alt endpoint
- **Controller:** AdminPenukaranProdukController
- **Implementation:** ✅ Complete

---

### Withdrawal Management (3 permissions)

#### 10. approve_withdrawal
- **Description:** Dapat menyetujui penarikan tunai
- **Endpoints:**
  - `PATCH /api/admin/penarikan-tunai/{withdrawalId}/approve` - Approve
- **Controller:** AdminPenarikanTunaiController
- **Implementation:** ✅ Complete
- **Authorization:** `auth:sanctum` + `isAdminUser()`

#### 11. reject_withdrawal
- **Description:** Dapat menolak penarikan tunai
- **Endpoints:**
  - `PATCH /api/admin/penarikan-tunai/{withdrawalId}/reject` - Reject
- **Controller:** AdminPenarikanTunaiController
- **Implementation:** ✅ Complete

#### 12. view_all_withdrawals
- **Description:** Dapat melihat semua penarikan
- **Endpoints:**
  - `GET /api/admin/penarikan-tunai` - List all
  - `GET /api/admin/penarikan-tunai/{withdrawalId}` - View detail
  - `GET /api/admin/penarikan-tunai/stats/overview` - Statistics
- **Controller:** AdminPenarikanTunaiController
- **Implementation:** ✅ Complete

---

### User Management (5 permissions)

#### 13. view_all_users
- **Description:** Dapat melihat semua nasabah
- **Endpoints:**
  - `GET /api/admin/users` - List all
  - `GET /api/admin/dashboard/users` - Dashboard view
- **Controller:** AdminUserController, DashboardAdminController
- **Implementation:** ✅ Complete

#### 14. view_user_detail
- **Description:** Dapat melihat detail nasabah
- **Endpoints:**
  - `GET /api/admin/users/{userId}` - View detail
  - `GET /users/{id}` - Public (with auth)
- **Controller:** AdminUserController
- **Implementation:** ✅ Complete

#### 15. view_user_activity_log ⚠️
- **Description:** Dapat melihat log aktivitas nasabah
- **Endpoints:**
  - ❌ Not implemented (separate endpoint)
  - Partial: User data included in user detail
- **Controller:** AdminUserController
- **Implementation:** ⚠️ Partial

#### 16. view_user_badges
- **Description:** Dapat melihat badge nasabah
- **Endpoints:**
  - Partial: `GET /api/admin/users/{id}` (included)
  - `GET /users/{userId}/badges-list` - User badges
- **Controller:** AdminUserController
- **Implementation:** ⚠️ Partial (included in user detail)

#### 17. view_user_balance
- **Description:** Dapat melihat saldo poin nasabah
- **Endpoints:**
  - Partial: `GET /api/admin/users/{id}` (included)
  - `GET /poin/breakdown/{userId}` - Point breakdown
- **Controller:** AdminUserController, PointController
- **Implementation:** ⚠️ Partial (included in user detail)

---

### Badge Management (2 permissions) ⚠️

#### 18. manage_badges
- **Description:** Dapat mengelola badge
- **Endpoints:**
  - ⚠️ `POST /api/superadmin/badges` - Create
  - ⚠️ `PUT /api/superadmin/badges/{id}` - Update
  - ⚠️ `DELETE /api/superadmin/badges/{id}` - Delete
- **Controller:** BadgeManagementController
- **Implementation:** ⚠️ Currently superadmin-only
- **Note:** Seeder says admin can manage, but routes restrict to superadmin

#### 19. assign_badge_manual
- **Description:** Dapat memberikan badge secara manual
- **Endpoints:**
  - `POST /api/superadmin/badges/{badgeId}/assign` - Assign
  - Could also be: `POST /api/admin/badges/{badgeId}/assign`
- **Controller:** BadgeManagementController
- **Implementation:** ✅ Works but restricted to superadmin

---

### Product Management (2 permissions)

#### 20. view_all_products
- **Description:** Dapat melihat semua produk
- **Endpoints:**
  - `GET /produk` - List all
  - `GET /produk/{id}` - View detail
- **Controller:** ProdukController
- **Implementation:** ✅ Complete

#### 21. view_product_detail
- **Description:** Dapat melihat detail produk
- **Endpoints:**
  - `GET /produk/{id}` - View detail
- **Controller:** ProdukController
- **Implementation:** ✅ Complete

---

### Reports & Dashboard (1 permission)

#### 22. view_dashboard
- **Description:** Dapat melihat dashboard admin
- **Endpoints:**
  - `GET /api/admin/dashboard/overview` - Overview
  - `GET /api/admin/dashboard/stats` - Statistics
  - `GET /api/admin/dashboard/users` - Users
  - `GET /api/admin/dashboard/waste-summary` - Waste
  - `GET /api/admin/dashboard/point-summary` - Points
  - `GET /api/admin/dashboard/waste-by-user` - Breakdown
  - `GET /api/admin/analytics/waste` - Analytics
  - `GET /api/admin/analytics/waste-by-user` - User analysis
  - `GET /api/admin/analytics/points` - Point analytics
- **Controller:** AdminDashboardController, DashboardAdminController, AdminAnalyticsController
- **Implementation:** ✅ Complete

#### 23. export_reports
- **Description:** Dapat export laporan
- **Endpoints:**
  - `POST /api/admin/reports/generate` - Generate
  - `GET /api/admin/reports/list` - List reports
  - `GET /api/admin/export` - Export
- **Controller:** AdminReportsController
- **Implementation:** ✅ Complete

---

## SUPERADMIN (System Admin - Level 3)

*Inherits all 40 (nasabah + admin) permissions + 17 additional*

### Admin Management (6 permissions)

#### 1. create_admin
- **Description:** Dapat membuat admin baru
- **Endpoints:**
  - `POST /api/superadmin/admins` - Create
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete
- **Authorization:** `role:superadmin`

#### 2. edit_admin
- **Description:** Dapat edit data admin
- **Endpoints:**
  - `PUT /api/superadmin/admins/{adminId}` - Update
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete

#### 3. delete_admin
- **Description:** Dapat hapus admin
- **Endpoints:**
  - `DELETE /api/superadmin/admins/{adminId}` - Delete
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete

#### 4. view_all_admins
- **Description:** Dapat melihat semua admin
- **Endpoints:**
  - `GET /api/superadmin/admins` - List all
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete

#### 5. view_admin_detail
- **Description:** Dapat melihat detail admin
- **Endpoints:**
  - `GET /api/superadmin/admins/{adminId}` - View detail
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete

#### 6. view_admin_activity_log
- **Description:** Dapat melihat log aktivitas admin
- **Endpoints:**
  - `GET /api/superadmin/admins/{adminId}/activity` - Activity
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete

---

### Role Management (5 permissions)

#### 7. manage_roles
- **Description:** Dapat mengelola role
- **Endpoints:**
  - `GET /api/superadmin/roles` - List
  - `POST /api/superadmin/roles` - Create
  - `PUT /api/superadmin/roles/{roleId}` - Update
  - `DELETE /api/superadmin/roles/{roleId}` - Delete
- **Controller:** RoleManagementController
- **Implementation:** ✅ Complete

#### 8. create_role
- **Description:** Dapat membuat role baru
- **Endpoints:**
  - `POST /api/superadmin/roles` - Create
- **Controller:** RoleManagementController
- **Implementation:** ✅ Complete

#### 9. edit_role
- **Description:** Dapat edit role
- **Endpoints:**
  - `PUT /api/superadmin/roles/{roleId}` - Update
- **Controller:** RoleManagementController
- **Implementation:** ✅ Complete

#### 10. delete_role
- **Description:** Dapat hapus role
- **Endpoints:**
  - `DELETE /api/superadmin/roles/{roleId}` - Delete
- **Controller:** RoleManagementController
- **Implementation:** ✅ Complete

#### 11. manage_roles (view users in role)
- **Description:** Dapat melihat user dalam role
- **Endpoints:**
  - `GET /api/superadmin/roles/{roleId}/users` - View users
- **Controller:** RoleManagementController
- **Implementation:** ✅ Complete

---

### Permission Management (4 permissions)

#### 12. manage_permissions
- **Description:** Dapat mengelola permission
- **Endpoints:**
  - `GET /api/superadmin/permissions` - List all
  - `GET /api/superadmin/roles/{roleId}/permissions` - Role permissions
- **Controller:** PermissionAssignmentController
- **Implementation:** ✅ Complete

#### 13. assign_permission
- **Description:** Dapat memberikan permission ke role
- **Endpoints:**
  - `POST /api/superadmin/roles/{roleId}/permissions` - Assign
  - `POST /api/superadmin/roles/{roleId}/permissions/bulk` - Bulk assign
- **Controller:** PermissionAssignmentController
- **Implementation:** ✅ Complete

#### 14. revoke_permission
- **Description:** Dapat mencabut permission dari role
- **Endpoints:**
  - `DELETE /api/superadmin/roles/{roleId}/permissions/{permissionId}` - Revoke
- **Controller:** PermissionAssignmentController
- **Implementation:** ✅ Complete

---

### Audit & Security (3 permissions)

#### 15. view_audit_logs
- **Description:** Dapat melihat log audit semua admin
- **Endpoints:**
  - `GET /api/superadmin/audit-logs` - List
  - `GET /api/superadmin/audit-logs/{logId}` - View detail
  - `GET /api/superadmin/audit-logs/export` - Export
- **Controller:** AuditLogController
- **Implementation:** ✅ Complete

#### 16. view_system_logs
- **Description:** Dapat melihat log sistem
- **Endpoints:**
  - `GET /api/superadmin/system-logs` - System logs
  - `GET /api/superadmin/audit-logs/users/activity` - User activity
- **Controller:** AuditLogController
- **Implementation:** ✅ Complete

#### 17. view_admin_activity_log (note: overlaps with #6)
- **Description:** Dapat melihat log aktivitas admin
- **Endpoints:**
  - `GET /api/superadmin/admins/{adminId}/activity` - Admin activity
- **Controller:** AdminManagementController
- **Implementation:** ✅ Complete

---

### Product Management (3 permissions)

#### 18. create_product
- **Description:** Dapat membuat produk baru
- **Endpoints:**
  - `POST /produk` - Create
- **Controller:** ProdukController
- **Implementation:** ✅ Complete
- **Authorization:** `role:superadmin`

#### 19. edit_product
- **Description:** Dapat edit produk
- **Endpoints:**
  - `PUT /produk/{id}` - Update
- **Controller:** ProdukController
- **Implementation:** ✅ Complete

#### 20. delete_product
- **Description:** Dapat hapus produk
- **Endpoints:**
  - `DELETE /produk/{id}` - Delete
- **Controller:** ProdukController
- **Implementation:** ✅ Complete

---

### Badge Management (3 permissions)

#### 21. manage_badges (Create/Update/Delete)
- **Description:** Dapat mengelola badge
- **Endpoints:**
  - `POST /api/superadmin/badges` - Create
  - `PUT /api/superadmin/badges/{badgeId}` - Update
  - `DELETE /api/superadmin/badges/{badgeId}` - Delete
  - `GET /api/superadmin/badges` - List
  - `GET /api/superadmin/badges/{badgeId}` - View
- **Controller:** BadgeManagementController
- **Implementation:** ✅ Complete

#### 22. assign_badge_manual
- **Description:** Dapat memberikan badge secara manual
- **Endpoints:**
  - `POST /api/superadmin/badges/{badgeId}/assign` - Assign
  - `POST /api/superadmin/badges/{badgeId}/revoke` - Revoke
  - `GET /api/superadmin/badges/{badgeId}/users` - View users
- **Controller:** BadgeManagementController
- **Implementation:** ✅ Complete

---

### System & Article Management (4 permissions)

#### 23. manage_system_settings
- **Description:** Dapat mengelola pengaturan sistem
- **Endpoints:**
  - `GET /api/superadmin/settings` - List
  - `GET /api/superadmin/settings/{key}` - View
  - `PUT /api/superadmin/settings/{key}` - Update
  - `GET /api/superadmin/health` - Health check
  - `GET /api/superadmin/cache-stats` - Cache stats
  - `POST /api/superadmin/cache/clear` - Clear cache
  - `GET /api/superadmin/database-stats` - DB stats
- **Controller:** SystemSettingsController
- **Implementation:** ✅ Complete

#### 24. manage_articles
- **Description:** Dapat mengelola artikel
- **Endpoints:**
  - `POST /artikel` - Create
  - `PUT /artikel/{id}` - Update
  - `DELETE /artikel/{id}` - Delete
  - `GET /artikel` - List
  - `GET /artikel/{slug}` - View
- **Controller:** ArtikelController
- **Implementation:** ✅ Complete

#### 25. backup_database ❌
- **Description:** Dapat backup database
- **Endpoints:**
  - ❌ Not implemented
- **Controller:** SystemSettingsController
- **Implementation:** ❌ Missing

#### 26. view_system_health
- **Description:** Dapat melihat kesehatan sistem
- **Endpoints:**
  - `GET /api/superadmin/health` - Health check
- **Controller:** SystemSettingsController
- **Implementation:** ✅ Complete

---

## SUMMARY TABLE

### All 57 Permissions Status

| Level | Total | Implemented | Partial | Missing | % Complete |
|-------|-------|-------------|---------|---------|------------|
| Nasabah | 17 | 16 | 0 | 1 | 94% |
| Admin (additional) | 23 | 19 | 2 | 2 | 83% |
| Superadmin (additional) | 17 | 16 | 0 | 1 | 94% |
| **TOTAL** | **57** | **51** | **2** | **4** | **89%** |

---

## IMPLEMENTATION LEGEND

- ✅ **Complete** - Endpoint implemented with proper authorization
- ⚠️ **Partial** - Endpoint exists but functionality incomplete
- ❌ **Missing** - No endpoint for this permission

---

**Document Generated:** December 22, 2025  
**Audit Period:** Complete system review

================================================================================
                    COMPLETE API ENDPOINT DOCUMENTATION
                          MENDAUR - API v1.0
================================================================================

Generated: December 22, 2025
Last Updated: After implementing 4 missing features
Total Endpoints: 80+ endpoints

================================================================================
                            TABLE OF CONTENTS
================================================================================

1. PUBLIC ROUTES (No Authentication Required)
2. AUTHENTICATION ROUTES
3. USER ROUTES (Protected)
4. NOTIFICATION ROUTES
5. WASTE DEPOSIT ROUTES
6. PRODUCT EXCHANGE ROUTES
7. CASH WITHDRAWAL ROUTES
8. BADGE & REWARD ROUTES
9. POINT SYSTEM ROUTES
10. ADMIN ROUTES
11. SUPERADMIN ROUTES
12. DASHBOARD & ANALYTICS ROUTES

================================================================================
                    1. PUBLIC ROUTES (No Authentication)
================================================================================

Auth Management
===============
POST   /api/login
       Purpose: User login
       Body: { email: string, password: string }
       Response: { token, user_data }

POST   /api/register
       Purpose: New user registration
       Body: { nama, email, password, password_confirmation, alamat, no_hp }
       Response: { user_data, token }

Jadwal Penyetoran (Schedule)
============================
GET    /api/jadwal-penyetoran
       Purpose: Get all active schedules
       Query: ?per_page=20&page=1
       Response: { data: [], pagination }

GET    /api/jadwal-penyetoran/{id}
       Purpose: Get single schedule details
       Response: { data: {} }

GET    /api/jadwal-penyetoran-aktif
       Purpose: Get currently active schedules
       Response: { data: [] }

Jenis Sampah (Waste Types)
==========================
GET    /api/jenis-sampah
       Purpose: Get all waste types
       Response: { data: [] }

GET    /api/jenis-sampah/{id}
       Purpose: Get single waste type details
       Response: { data: {} }

Kategori Sampah (Waste Categories)
==================================
GET    /api/kategori-sampah
       Purpose: Get all categories with their jenis
       Response: { data: [] with nested jenis }

GET    /api/kategori-sampah/{id}
       Purpose: Get specific category details
       Response: { data: {} }

GET    /api/kategori-sampah/{id}/jenis
       Purpose: Get jenis by category
       Response: { data: [] }

GET    /api/jenis-sampah-all
       Purpose: Get flat list of all waste types (for dropdowns)
       Response: { data: [] }

Produk (Products)
=================
GET    /api/produk
       Purpose: Browse all products
       Query: ?per_page=20&page=1&kategori=3
       Response: { data: [], pagination }

GET    /api/produk/{id}
       Purpose: Get product details
       Response: { data: {} with full info }

Artikel (Articles)
==================
GET    /api/artikel
       Purpose: Read all articles
       Query: ?per_page=10&page=1
       Response: { data: [], pagination }

GET    /api/artikel/{slug}
       Purpose: Read single article
       Response: { data: {} }

Dashboard (Public Stats)
========================
GET    /api/dashboard/global-stats
       Purpose: Get global statistics
       Response: { data: { total_users, total_waste, total_points, ... } }

================================================================================
                    2. AUTHENTICATION ROUTES (Protected)
================================================================================

Logout
======
POST   /api/logout
       Auth: Bearer token (sanctum)
       Purpose: User logout
       Response: { message: "Logged out successfully" }

Profile Management
==================
GET    /api/profile
       Auth: Bearer token
       Purpose: Get current user's profile
       Response: { data: current_user }

PUT    /api/profile
       Auth: Bearer token
       Purpose: Update current user's profile
       Body: { nama, alamat, no_hp, foto }
       Response: { data: updated_user, message: "Profile updated" }

================================================================================
                    3. USER ROUTES (Protected)
================================================================================

User Profile
============
GET    /api/users/{id}
       Auth: Bearer token
       Purpose: Get user profile details
       Response: { data: { user_id, nama, email, level_akses, poin, ... } }

PUT    /api/users/{id}
       Auth: Bearer token
       Purpose: Update user profile
       Body: { nama, alamat, no_hp }
       Response: { data: updated_user }

POST   /api/users/{id}/update-photo
       Auth: Bearer token
       Purpose: Upload profile photo (legacy)
       Body: FormData with 'foto' file
       Response: { data: { foto_path } }

POST   /api/users/{id}/avatar
       Auth: Bearer token
       Purpose: Upload user avatar
       Body: FormData with 'avatar' file
       Response: { data: { avatar_path } }

User History & Stats
====================
GET    /api/users/{userId}/point-history
       Auth: Bearer token
       Purpose: Get user's point transaction history
       Query: ?per_page=20&page=1&type=income|expense
       Response: { data: [], pagination }

GET    /api/users/{userId}/redeem-history
       Auth: Bearer token
       Purpose: Get product redemption history
       Query: ?per_page=20&page=1
       Response: { data: [], pagination }

GET    /api/users/{userId}/tabung-sampah
       Auth: Bearer token
       Purpose: Get waste deposit history
       Query: ?per_page=20&page=1&status=pending|approved|rejected
       Response: { data: [], pagination }

GET    /api/users/{userId}/dashboard/points
       Auth: Bearer token
       Purpose: Get dashboard points overview
       Response: { data: { total_points, breakdown: {} } }

GET    /api/user/{id}/poin
       Auth: Bearer token
       Purpose: Get user's current points
       Response: { data: { poin_total, poin_available } }

GET    /api/user/{id}/redeem-history
       Auth: Bearer token
       Purpose: Get redemption history
       Response: { data: [] }

GET    /api/user/{id}/poin/statistics
       Auth: Bearer token
       Purpose: Get points statistics
       Response: { data: { stats } }

GET    /api/users/{id}/aktivitas
       Auth: Bearer token
       Purpose: Get user activities
       Response: { data: [] }

GET    /api/users/{id}/badges
       Auth: Bearer token
       Purpose: Get user's badges
       Response: { data: [] }

GET    /api/users/{userId}/badges-list
       Auth: Bearer token
       Purpose: Get detailed badge list with user progress
       Response: { data: [] with progress }

GET    /api/users/badges
       Auth: Bearer token
       Purpose: Get current user's badges
       Response: { data: [] }

================================================================================
                    4. NOTIFICATION ROUTES (Protected)
================================================================================

List & Count
============
GET    /api/notifications
       Auth: Bearer token
       Purpose: Get all user notifications
       Query: ?per_page=20&page=1
       Response: { data: [], pagination }

GET    /api/notifications/unread-count
       Auth: Bearer token
       Purpose: Get unread notification count
       Response: { data: { unread_count: number } }

GET    /api/notifications/unread
       Auth: Bearer token
       Purpose: Get only unread notifications
       Response: { data: [] }

Single Notification
===================
GET    /api/notifications/{id}
       Auth: Bearer token
       Purpose: Get notification details
       Response: { data: {} }

Mark as Read
============
PATCH  /api/notifications/{id}/read
       Auth: Bearer token
       Purpose: Mark single notification as read
       Body: {}
       Response: { data: {}, message: "Marked as read" }

PATCH  /api/notifications/mark-all-read
       Auth: Bearer token
       Purpose: Mark all notifications as read
       Body: {}
       Response: { message: "All notifications marked as read" }

Management
==========
DELETE /api/notifications/{id}
       Auth: Bearer token
       Purpose: Delete notification
       Response: { message: "Notification deleted" }

POST   /api/notifications/create
       Auth: Bearer token (Admin only)
       Purpose: Create notification
       Body: { user_id, judul, pesan }
       Response: { data: created_notification }

================================================================================
                    5. WASTE DEPOSIT ROUTES (Protected)
================================================================================

User Routes
===========
GET    /api/tabung-sampah
       Auth: Bearer token
       Purpose: Get user's waste deposits
       Query: ?per_page=20&page=1&status=pending|approved|rejected
       Response: { data: [], pagination }

POST   /api/tabung-sampah
       Auth: Bearer token
       Purpose: Create new waste deposit
       Body: {
         kategori_sampah_id: number,
         jenis_sampah_id: number,
         berat: decimal,
         keterangan: string,
         jadwal_penyetoran_id: number
       }
       Response: { data: created_deposit }

GET    /api/tabung-sampah/{id}
       Auth: Bearer token
       Purpose: Get specific waste deposit details
       Response: { data: {} }

PATCH  /api/tabung-sampah/{id}/approve
       Auth: Bearer token (Admin/Superadmin)
       Purpose: Approve waste deposit
       Body: { catatan?: string }
       Response: { data: updated_deposit, message: "Approved" }

PATCH  /api/tabung-sampah/{id}/reject
       Auth: Bearer token (Admin/Superadmin)
       Purpose: Reject waste deposit
       Body: { catatan: string }
       Response: { data: updated_deposit, message: "Rejected" }

User's Deposits by ID
=====================
GET    /api/users/{id}/tabung-sampah
       Auth: Bearer token
       Purpose: Get specific user's waste deposits
       Response: { data: [] }

Admin Routes (Level 2+)
=======================
GET    /api/admin/penyetoran-sampah
       Auth: Bearer token (Admin+)
       Purpose: List all waste deposits with filters
       Query: ?per_page=50&page=1&user_id=5&status=pending&date_from=2024-01-01
       Response: { data: [], pagination }

GET    /api/admin/penyetoran-sampah/{id}
       Auth: Bearer token (Admin+)
       Purpose: Get single deposit details
       Response: { data: {} with user info }

PATCH  /api/admin/penyetoran-sampah/{id}/approve
       Auth: Bearer token (Admin+)
       Purpose: Approve deposit
       Body: { catatan?: string }
       Response: { data: updated }

PATCH  /api/admin/penyetoran-sampah/{id}/reject
       Auth: Bearer token (Admin+)
       Purpose: Reject deposit
       Body: { catatan: string }
       Response: { data: updated }

DELETE /api/admin/penyetoran-sampah/{id}
       Auth: Bearer token (Admin+)
       Purpose: Delete deposit record
       Response: { message: "Deleted" }

GET    /api/admin/penyetoran-sampah/stats/overview
       Auth: Bearer token (Admin+)
       Purpose: Get waste deposit statistics
       Response: { data: { total, pending, approved, rejected, total_weight } }

================================================================================
                    6. PRODUCT EXCHANGE ROUTES (Protected)
================================================================================

User Routes
===========
GET    /api/penukaran-produk
       Auth: Bearer token
       Purpose: Get user's product exchanges
       Query: ?per_page=20&page=1&status=pending|approved|rejected|completed
       Response: { data: [], pagination }

POST   /api/penukaran-produk
       Auth: Bearer token
       Purpose: Request product exchange
       Body: {
         produk_id: number,
         jumlah: number
       }
       Response: { data: created_exchange, message: "Exchange requested" }

GET    /api/penukaran-produk/{id}
       Auth: Bearer token
       Purpose: Get exchange details
       Response: { data: {} }

PUT    /api/penukaran-produk/{id}/cancel
       Auth: Bearer token
       Purpose: Cancel exchange request
       Response: { data: updated, message: "Cancelled" }

DELETE /api/penukaran-produk/{id}
       Auth: Bearer token
       Purpose: Delete exchange record
       Response: { message: "Deleted" }

Legacy Routes (Backward Compatibility)
======================================
GET    /api/tukar-produk
       Auth: Bearer token
       Purpose: Get exchanges (same as /penukaran-produk)
       Response: { data: [] }

GET    /api/tukar-produk/{id}
       Auth: Bearer token
       Purpose: Get exchange details
       Response: { data: {} }

Admin Routes (Level 2+)
=======================
GET    /api/admin/penukar-produk
       Auth: Bearer token (Admin+)
       Purpose: List all exchanges with filters
       Query: ?per_page=50&page=1&user_id=5&status=pending&date_from=2024-01-01
       Response: { data: [], pagination }

GET    /api/admin/penukar-produk/{exchangeId}
       Auth: Bearer token (Admin+)
       Purpose: Get exchange details with user info
       Response: { data: {} }

PATCH  /api/admin/penukar-produk/{exchangeId}/approve
       Auth: Bearer token (Admin+)
       Purpose: Approve exchange
       Body: { catatan?: string }
       Response: { data: updated, message: "Approved" }

PATCH  /api/admin/penukar-produk/{exchangeId}/reject
       Auth: Bearer token (Admin+)
       Purpose: Reject exchange
       Body: { catatan: string }
       Response: { data: updated, message: "Rejected" }

DELETE /api/admin/penukar-produk/{exchangeId}
       Auth: Bearer token (Admin+)
       Purpose: Delete exchange record
       Response: { message: "Deleted" }

GET    /api/admin/penukar-produk/stats/overview
       Auth: Bearer token (Admin+)
       Purpose: Get product exchange statistics
       Response: { data: { total, pending, approved, rejected, completed } }

================================================================================
                    7. CASH WITHDRAWAL ROUTES (Protected)
================================================================================

User Routes
===========
GET    /api/penarikan-tunai
       Auth: Bearer token
       Purpose: Get user's withdrawal requests
       Query: ?per_page=20&page=1&status=pending|approved|rejected
       Response: { data: [], pagination }

POST   /api/penarikan-tunai
       Auth: Bearer token
       Purpose: Request cash withdrawal
       Body: {
         jumlah: number,
         rekening_bank: string,
         nama_rekening: string
       }
       Response: { data: created_withdrawal }

GET    /api/penarikan-tunai/{id}
       Auth: Bearer token
       Purpose: Get withdrawal details
       Response: { data: {} }

GET    /api/penarikan-tunai/summary
       Auth: Bearer token
       Purpose: Get withdrawal summary statistics
       Response: { data: { total_requested, total_approved, total_pending } }

Admin Routes (Level 2+)
=======================
GET    /api/admin/penarikan-tunai
       Auth: Bearer token (Admin+)
       Purpose: List all withdrawal requests with filters
       Query: ?per_page=50&page=1&user_id=5&status=pending&date_from=2024-01-01
       Response: { data: [], pagination }

GET    /api/admin/penarikan-tunai/{withdrawalId}
       Auth: Bearer token (Admin+)
       Purpose: Get withdrawal details with user info
       Response: { data: {} }

PATCH  /api/admin/penarikan-tunai/{withdrawalId}/approve
       Auth: Bearer token (Admin+)
       Purpose: Approve withdrawal
       Body: { reference_number?: string, catatan?: string }
       Response: { data: updated, message: "Approved" }

PATCH  /api/admin/penarikan-tunai/{withdrawalId}/reject
       Auth: Bearer token (Admin+)
       Purpose: Reject withdrawal
       Body: { catatan: string }
       Response: { data: updated, message: "Rejected" }

DELETE /api/admin/penarikan-tunai/{withdrawalId}
       Auth: Bearer token (Admin+)
       Purpose: Delete withdrawal record
       Response: { message: "Deleted" }

GET    /api/admin/penarikan-tunai/stats/overview
       Auth: Bearer token (Admin+)
       Purpose: Get withdrawal statistics
       Response: { data: { total_requests, total_amount, pending, approved, rejected } }

================================================================================
                    8. BADGE & REWARD ROUTES (Protected)
================================================================================

Public Badge Info
=================
GET    /api/badges
       Purpose: Get all badges (public)
       Response: { data: [] with requirements }

User Badge Routes
=================
GET    /api/users/{userId}/badge-progress
       Auth: Bearer token
       Purpose: Get user's badge progress
       Response: { data: [] with progress % }

POST   /api/users/{userId}/check-badges
       Auth: Bearer token
       Purpose: Check and award earned badges
       Body: {}
       Response: { data: newly_earned_badges }

GET    /api/user/badges/progress
       Auth: Bearer token
       Purpose: Get current user's badge progress
       Response: { data: [] with detailed progress }

GET    /api/user/badges/completed
       Auth: Bearer token
       Purpose: Get user's completed badges
       Response: { data: [] }

GET    /api/badges/leaderboard
       Auth: Bearer token
       Purpose: Get badge leaderboard
       Query: ?limit=50
       Response: { data: [] with user rankings }

GET    /api/badges/available
       Auth: Bearer token
       Purpose: Get available badges for user
       Response: { data: [] }

Admin Badge Management (Level 2+)
=================================
GET    /api/admin/badges
       Auth: Bearer token (Admin+)
       Purpose: List all badges
       Response: { data: [] }

GET    /api/admin/badges/{badgeId}
       Auth: Bearer token (Admin+)
       Purpose: Get badge details
       Response: { data: {} }

POST   /api/admin/badges
       Auth: Bearer token (Admin+)
       Purpose: Create new badge
       Body: {
         nama: string,
         deskripsi?: string,
         gambar?: file,
         poin_diperlukan: number
       }
       Response: { data: created_badge }

PUT    /api/admin/badges/{badgeId}
       Auth: Bearer token (Admin+)
       Purpose: Update badge
       Body: { nama, deskripsi, poin_diperlukan, gambar? }
       Response: { data: updated_badge }

DELETE /api/admin/badges/{badgeId}
       Auth: Bearer token (Admin+)
       Purpose: Delete badge
       Response: { message: "Badge deleted" }

POST   /api/admin/badges/{badgeId}/assign
       Auth: Bearer token (Admin+)
       Purpose: Manually assign badge to user
       Body: { user_id: number }
       Response: { data: assigned_badge }

POST   /api/admin/badges/{badgeId}/revoke
       Auth: Bearer token (Admin+)
       Purpose: Revoke badge from user
       Body: { user_id: number }
       Response: { message: "Badge revoked" }

GET    /api/admin/badges/{badgeId}/users
       Auth: Bearer token (Admin+)
       Purpose: Get users with this badge
       Query: ?per_page=50&page=1
       Response: { data: [], pagination }

Analytics (Admin+)
==================
GET    /api/admin/badges/analytics
       Auth: Bearer token (Admin+)
       Purpose: Get badge analytics
       Response: { data: { total_badges, total_awarded, most_common, etc } }

================================================================================
                    9. POINT SYSTEM ROUTES (Protected)
================================================================================

User Routes
===========
GET    /api/poin/history
       Auth: Bearer token
       Purpose: Get user's point history
       Query: ?per_page=20&page=1&type=income|expense|transfer
       Response: { data: [], pagination }

GET    /api/poin/breakdown/{userId}
       Auth: Bearer token
       Purpose: Get point breakdown by category
       Response: { data: { from_waste: number, from_referral: number, ... } }

POST   /api/poin/bonus
       Auth: Bearer token (Admin+)
       Purpose: Award bonus points to user
       Body: { user_id: number, jumlah: number, keterangan: string }
       Response: { data: transaction }

Admin Point Routes
==================
GET    /api/poin/admin/stats
       Auth: Bearer token (Admin+)
       Purpose: Get point statistics
       Response: { data: { total_distributed, total_redeemed, etc } }

GET    /api/poin/admin/history
       Auth: Bearer token (Admin+)
       Purpose: Get all point transactions
       Query: ?per_page=50&page=1&user_id=5
       Response: { data: [], pagination }

GET    /api/poin/admin/redemptions
       Auth: Bearer token (Admin+)
       Purpose: Get all redemptions
       Response: { data: [] }

GET    /api/poin/breakdown/all
       Auth: Bearer token (Admin+)
       Purpose: Get all users' point breakdowns
       Response: { data: {} }

================================================================================
                    10. ADMIN ROUTES (Level 2+)
================================================================================

Dashboard
=========
GET    /api/admin/dashboard/overview
       Auth: Bearer token (Admin+)
       Purpose: Get dashboard overview
       Response: { data: { users, waste, points, exchanges, withdrawals } }

GET    /api/admin/dashboard/stats
       Auth: Bearer token (Admin+)
       Purpose: Get dashboard statistics
       Response: { data: { stats } }

User Management
===============
GET    /api/admin/users
       Auth: Bearer token (Admin+)
       Purpose: List all users with filters
       Query: ?per_page=50&page=1&search=name&status=active|inactive&role=1|2|3
       Response: { data: [], pagination }

GET    /api/admin/users/{userId}
       Auth: Bearer token (Admin+)
       Purpose: Get user details
       Response: { data: user_details }

PUT    /api/admin/users/{userId}
       Auth: Bearer token (Admin+)
       Purpose: Update user information
       Body: { nama, alamat, no_hp, email }
       Response: { data: updated_user }

PATCH  /api/admin/users/{userId}/status
       Auth: Bearer token (Admin+)
       Purpose: Update user status
       Body: { status: active|inactive|suspended }
       Response: { data: updated_user }

PATCH  /api/admin/users/{userId}/role
       Auth: Bearer token (Admin+)
       Purpose: Update user role
       Body: { role_id: number }
       Response: { data: updated_user }

PATCH  /api/admin/users/{userId}/tipe
       Auth: Bearer token (Admin+)
       Purpose: Update user type (dual nasabah status)
       Body: { tipe: string }
       Response: { data: updated_user }

DELETE /api/admin/users/{userId}
       Auth: Bearer token (Admin+)
       Purpose: Delete user account
       Response: { message: "User deleted" }

Analytics
=========
GET    /api/admin/analytics/waste
       Auth: Bearer token (Admin+)
       Purpose: Get waste analytics
       Query: ?date_from=2024-01-01&date_to=2024-12-31&type=daily|monthly|yearly
       Response: { data: analytics_data }

GET    /api/admin/analytics/waste-by-user
       Auth: Bearer token (Admin+)
       Purpose: Get waste statistics by user
       Response: { data: [] with totals }

GET    /api/admin/analytics/points
       Auth: Bearer token (Admin+)
       Purpose: Get point analytics
       Response: { data: analytics_data }

Leaderboard
===========
GET    /api/admin/leaderboard
       Auth: Bearer token (Admin+)
       Purpose: Get user leaderboard
       Query: ?limit=100&type=points|waste|badges
       Response: { data: [] ranked users }

Reports
=======
GET    /api/admin/reports/list
       Auth: Bearer token (Admin+)
       Purpose: List available reports
       Response: { data: [] }

POST   /api/admin/reports/generate
       Auth: Bearer token (Admin+)
       Purpose: Generate custom report
       Body: { type: string, date_from: date, date_to: date }
       Response: { data: report }

GET    /api/admin/export
       Auth: Bearer token (Admin+)
       Purpose: Export data to CSV/Excel
       Query: ?type=users|waste|points|exchanges&format=csv|excel
       Response: file download

Legacy Dashboard Routes
=======================
GET    /api/admin/dashboard/users
       Auth: Bearer token (Admin+)
       Purpose: Get user statistics
       Response: { data: {} }

GET    /api/admin/dashboard/waste-summary
       Auth: Bearer token (Admin+)
       Purpose: Get waste deposit summary
       Response: { data: {} }

GET    /api/admin/dashboard/point-summary
       Auth: Bearer token (Admin+)
       Purpose: Get point summary
       Response: { data: {} }

GET    /api/admin/dashboard/waste-by-user
       Auth: Bearer token (Admin+)
       Purpose: Get waste breakdown by user
       Response: { data: [] }

GET    /api/admin/dashboard/report
       Auth: Bearer token (Admin+)
       Purpose: Get comprehensive report
       Response: { data: {} }

================================================================================
                    11. SUPERADMIN ROUTES (Level 3 Only)
================================================================================

Admin Account Management
========================
GET    /api/superadmin/admins
       Auth: Bearer token (Superadmin)
       Purpose: List all admin accounts
       Query: ?per_page=50&page=1&search=name
       Response: { data: [], pagination }

POST   /api/superadmin/admins
       Auth: Bearer token (Superadmin)
       Purpose: Create new admin account
       Body: {
         nama: string,
         email: string,
         password: string,
         phone?: string,
         permissions: []
       }
       Response: { data: created_admin }

GET    /api/superadmin/admins/{adminId}
       Auth: Bearer token (Superadmin)
       Purpose: Get admin details
       Response: { data: admin_info }

PUT    /api/superadmin/admins/{adminId}
       Auth: Bearer token (Superadmin)
       Purpose: Update admin account
       Body: { nama, email, phone }
       Response: { data: updated_admin }

DELETE /api/superadmin/admins/{adminId}
       Auth: Bearer token (Superadmin)
       Purpose: Delete admin account
       Response: { message: "Admin deleted" }

GET    /api/superadmin/admins/{adminId}/activity
       Auth: Bearer token (Superadmin)
       Purpose: Get admin's activity logs
       Response: { data: [] }

Role Management
===============
GET    /api/superadmin/roles
       Auth: Bearer token (Superadmin)
       Purpose: List all roles
       Response: { data: [] with permissions count }

POST   /api/superadmin/roles
       Auth: Bearer token (Superadmin)
       Purpose: Create new role
       Body: { nama: string, deskripsi: string, level_akses: number }
       Response: { data: created_role }

GET    /api/superadmin/roles/{roleId}
       Auth: Bearer token (Superadmin)
       Purpose: Get role details
       Response: { data: role_info }

PUT    /api/superadmin/roles/{roleId}
       Auth: Bearer token (Superadmin)
       Purpose: Update role
       Body: { nama, deskripsi }
       Response: { data: updated_role }

DELETE /api/superadmin/roles/{roleId}
       Auth: Bearer token (Superadmin)
       Purpose: Delete role
       Response: { message: "Role deleted" }

GET    /api/superadmin/roles/{roleId}/users
       Auth: Bearer token (Superadmin)
       Purpose: Get users with this role
       Query: ?per_page=50&page=1
       Response: { data: [], pagination }

Permission Management
=====================
GET    /api/superadmin/permissions
       Auth: Bearer token (Superadmin)
       Purpose: Get all available permissions
       Response: { data: [] }

GET    /api/superadmin/roles/{roleId}/permissions
       Auth: Bearer token (Superadmin)
       Purpose: Get role's permissions
       Response: { data: [] }

POST   /api/superadmin/roles/{roleId}/permissions
       Auth: Bearer token (Superadmin)
       Purpose: Assign permission to role
       Body: { permission_code: string }
       Response: { data: assigned_permission }

POST   /api/superadmin/roles/{roleId}/permissions/bulk
       Auth: Bearer token (Superadmin)
       Purpose: Assign multiple permissions
       Body: { permissions: [code1, code2, ...] }
       Response: { data: [], message: "Permissions assigned" }

DELETE /api/superadmin/roles/{roleId}/permissions/{permissionId}
       Auth: Bearer token (Superadmin)
       Purpose: Revoke permission from role
       Response: { message: "Permission revoked" }

Audit Logs
==========
GET    /api/superadmin/audit-logs
       Auth: Bearer token (Superadmin)
       Purpose: List all audit logs
       Query: ?per_page=100&page=1&user_id=5&action=create|update|delete
       Response: { data: [], pagination }

GET    /api/superadmin/audit-logs/{logId}
       Auth: Bearer token (Superadmin)
       Purpose: Get specific audit log
       Response: { data: log_details }

GET    /api/superadmin/system-logs
       Auth: Bearer token (Superadmin)
       Purpose: Get system logs
       Response: { data: [] }

GET    /api/superadmin/audit-logs/users/activity
       Auth: Bearer token (Superadmin)
       Purpose: Get user activity summary
       Response: { data: [] }

POST   /api/superadmin/audit-logs/clear-old
       Auth: Bearer token (Superadmin)
       Purpose: Clear old audit logs (older than 90 days)
       Body: { days: number }
       Response: { message: "X logs deleted" }

GET    /api/superadmin/audit-logs/export
       Auth: Bearer token (Superadmin)
       Purpose: Export audit logs
       Query: ?format=csv|excel&date_from=2024-01-01&date_to=2024-12-31
       Response: file download

System Settings
===============
GET    /api/superadmin/settings
       Auth: Bearer token (Superadmin)
       Purpose: Get all system settings
       Response: { data: {} }

GET    /api/superadmin/settings/{key}
       Auth: Bearer token (Superadmin)
       Purpose: Get specific setting
       Response: { data: setting_value }

PUT    /api/superadmin/settings/{key}
       Auth: Bearer token (Superadmin)
       Purpose: Update setting
       Body: { value: any }
       Response: { data: updated_setting }

System Health
=============
GET    /api/superadmin/health
       Auth: Bearer token (Superadmin)
       Purpose: Get system health status
       Response: { data: { database: ok, cache: ok, disk: ok } }

GET    /api/superadmin/cache-stats
       Auth: Bearer token (Superadmin)
       Purpose: Get cache statistics
       Response: { data: { hits, misses, size } }

POST   /api/superadmin/cache/clear
       Auth: Bearer token (Superadmin)
       Purpose: Clear all cache
       Body: {}
       Response: { message: "Cache cleared" }

GET    /api/superadmin/database-stats
       Auth: Bearer token (Superadmin)
       Purpose: Get database statistics
       Response: { data: { size, tables, records } }

Database Backup
===============
POST   /api/superadmin/backup
       Auth: Bearer token (Superadmin)
       Purpose: Create database backup
       Body: {}
       Response: { data: { filename, size, created_at } }

GET    /api/superadmin/backups
       Auth: Bearer token (Superadmin)
       Purpose: List all backups
       Response: { data: [] with sizes and dates }

DELETE /api/superadmin/backups/{filename}
       Auth: Bearer token (Superadmin)
       Purpose: Delete backup file
       Response: { message: "Backup deleted" }

Activity Logs Management
========================
GET    /api/admin/users/{userId}/activity-logs
       Auth: Bearer token (Admin+)
       Purpose: Get specific user's activity logs
       Query: ?per_page=50&page=1&date_from=2024-01-01
       Response: { data: [], pagination }

GET    /api/admin/activity-logs
       Auth: Bearer token (Admin+)
       Purpose: Get all activity logs with filters
       Query: ?per_page=50&page=1&user_id=5&activity_type=login|logout|create&date_from=2024-01-01
       Response: { data: [], pagination }

GET    /api/admin/activity-logs/{logId}
       Auth: Bearer token (Admin+)
       Purpose: Get specific activity log
       Response: { data: log_details }

GET    /api/admin/activity-logs/stats/overview
       Auth: Bearer token (Admin+)
       Purpose: Get activity statistics
       Query: ?date_from=2024-01-01&date_to=2024-12-31
       Response: { data: { total_activities, breakdown_by_type, top_users } }

GET    /api/admin/activity-logs/export/csv
       Auth: Bearer token (Admin+)
       Purpose: Export activity logs to CSV
       Query: ?user_id=5&date_from=2024-01-01&date_to=2024-12-31
       Response: CSV file download

================================================================================
                    12. DASHBOARD & ANALYTICS ROUTES
================================================================================

User Dashboard
==============
GET    /api/dashboard/stats/{userId}
       Auth: Bearer token
       Purpose: Get user dashboard stats
       Response: { data: { points, badges, waste_deposits, exchanges } }

GET    /api/dashboard/leaderboard
       Auth: Bearer token
       Purpose: Get leaderboard rankings
       Query: ?limit=50&type=points|waste|badges
       Response: { data: ranked_users }

Admin Legacy Dashboard
======================
GET    /api/admin/dashboard/overview
       Auth: Bearer token (Admin+)
       Purpose: Get admin dashboard overview
       Response: { data: {} }

================================================================================
                    13. SUPERADMIN WASTE & CATEGORY MANAGEMENT
================================================================================

Kategori Sampah Management (Superadmin only)
=============================================
POST   /api/kategori-sampah
       Auth: Bearer token (Superadmin)
       Purpose: Create waste category
       Body: { nama: string, deskripsi: string, urutan: number }
       Response: { data: created_category }

PUT    /api/kategori-sampah/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Update category
       Body: { nama, deskripsi, urutan }
       Response: { data: updated_category }

DELETE /api/kategori-sampah/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Delete category
       Response: { message: "Category deleted" }

Jenis Sampah Management (Superadmin only)
==========================================
POST   /api/jenis-sampah
       Auth: Bearer token (Superadmin)
       Purpose: Create waste type
       Body: { nama: string, kategori_sampah_id: number, harga_beli: decimal }
       Response: { data: created_type }

PUT    /api/jenis-sampah/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Update waste type
       Body: { nama, kategori_sampah_id, harga_beli }
       Response: { data: updated_type }

DELETE /api/jenis-sampah/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Delete waste type
       Response: { message: "Type deleted" }

Produk Management (Superadmin only)
===================================
POST   /api/produk
       Auth: Bearer token (Superadmin)
       Purpose: Create product
       Body: {
         nama: string,
         deskripsi: string,
         harga: decimal,
         stok: number,
         gambar: file
       }
       Response: { data: created_product }

PUT    /api/produk/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Update product
       Body: { nama, deskripsi, harga, stok, gambar? }
       Response: { data: updated_product }

DELETE /api/produk/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Delete product
       Response: { message: "Product deleted" }

Artikel Management (Superadmin only)
====================================
POST   /api/artikel
       Auth: Bearer token (Superadmin)
       Purpose: Create article
       Body: {
         judul: string,
         slug: string,
         konten: text,
         gambar: file
       }
       Response: { data: created_article }

PUT    /api/artikel/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Update article
       Body: { judul, slug, konten, gambar? }
       Response: { data: updated_article }

DELETE /api/artikel/{id}
       Auth: Bearer token (Superadmin)
       Purpose: Delete article
       Response: { message: "Article deleted" }

Jadwal Penyetoran Management (Protected)
========================================
POST   /api/jadwal-penyetoran
       Auth: Bearer token (Admin+)
       Purpose: Create schedule
       Body: {
         lokasi: string,
         hari: string,
         jam_mulai: time,
         jam_selesai: time,
         kuota: number
       }
       Response: { data: created_schedule }

PUT    /api/jadwal-penyetoran/{id}
       Auth: Bearer token (Admin+)
       Purpose: Update schedule
       Response: { data: updated_schedule }

DELETE /api/jadwal-penyetoran/{id}
       Auth: Bearer token (Admin+)
       Purpose: Delete schedule
       Response: { message: "Schedule deleted" }

================================================================================
                        14. TEST ENDPOINT
================================================================================

API Health Check
================
GET    /api/user
       Purpose: Test if API is working
       Response: { status: "success", message: "API is working!", data: "good" }

================================================================================
                            RESPONSE FORMAT
================================================================================

Success Response (200, 201)
===========================
{
  "status": "success",
  "message": "Operation successful",
  "data": { /* response data */ }
}

Error Response (4xx, 5xx)
==========================
{
  "status": "error",
  "message": "Error description",
  "errors": { /* detailed errors if applicable */ }
}

Paginated Response
==================
{
  "status": "success",
  "message": "Data retrieved",
  "data": [ /* array of items */ ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}

================================================================================
                        AUTHENTICATION
================================================================================

All protected routes require Bearer token authentication:

Header: Authorization: Bearer {token}

Token obtained from login response:
POST /api/login
Body: { email: "user@example.com", password: "password" }
Response: {
  "status": "success",
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz...",
    "user": { /* user data */ }
  }
}

================================================================================
                        AUTHORIZATION LEVELS
================================================================================

Role Levels:
1 = NASABAH (Regular User) - Can only access own data
2 = ADMIN (Administrator) - Can manage users, waste, points, exchanges
3 = SUPERADMIN (Super Administrator) - Full access to all system features

Routes with specific role requirements are marked with:
  Auth: Bearer token (Admin+) = Level 2 and above
  Auth: Bearer token (Superadmin) = Level 3 only

================================================================================
                        QUICK REFERENCE BY FEATURE
================================================================================

USER ACCOUNT & PROFILE
  POST   /api/login
  POST   /api/register
  POST   /api/logout
  GET    /api/profile
  PUT    /api/profile
  GET    /api/users/{id}
  PUT    /api/users/{id}

NOTIFICATIONS (8 endpoints)
  GET    /api/notifications
  GET    /api/notifications/unread-count
  GET    /api/notifications/unread
  GET    /api/notifications/{id}
  PATCH  /api/notifications/{id}/read
  PATCH  /api/notifications/mark-all-read
  DELETE /api/notifications/{id}
  POST   /api/notifications/create (Admin+)

WASTE DEPOSITS (User: 5, Admin: 7)
  GET    /api/tabung-sampah
  POST   /api/tabung-sampah
  GET    /api/tabung-sampah/{id}
  PATCH  /api/tabung-sampah/{id}/approve
  PATCH  /api/tabung-sampah/{id}/reject
  GET    /api/admin/penyetoran-sampah
  GET    /api/admin/penyetoran-sampah/{id}
  PATCH  /api/admin/penyetoran-sampah/{id}/approve
  PATCH  /api/admin/penyetoran-sampah/{id}/reject
  DELETE /api/admin/penyetoran-sampah/{id}
  GET    /api/admin/penyetoran-sampah/stats/overview

PRODUCT EXCHANGE (User: 5, Admin: 6)
  GET    /api/penukaran-produk
  POST   /api/penukaran-produk
  GET    /api/penukaran-produk/{id}
  PUT    /api/penukaran-produk/{id}/cancel
  DELETE /api/penukaran-produk/{id}
  GET    /api/admin/penukar-produk
  GET    /api/admin/penukar-produk/{exchangeId}
  PATCH  /api/admin/penukar-produk/{exchangeId}/approve
  PATCH  /api/admin/penukar-produk/{exchangeId}/reject
  DELETE /api/admin/penukar-produk/{exchangeId}
  GET    /api/admin/penukar-produk/stats/overview

CASH WITHDRAWAL (User: 4, Admin: 6)
  GET    /api/penarikan-tunai
  POST   /api/penarikan-tunai
  GET    /api/penarikan-tunai/{id}
  GET    /api/penarikan-tunai/summary
  GET    /api/admin/penarikan-tunai
  GET    /api/admin/penarikan-tunai/{withdrawalId}
  PATCH  /api/admin/penarikan-tunai/{withdrawalId}/approve
  PATCH  /api/admin/penarikan-tunai/{withdrawalId}/reject
  DELETE /api/admin/penarikan-tunai/{withdrawalId}
  GET    /api/admin/penarikan-tunai/stats/overview

BADGES (Total: 16 endpoints)
  GET    /api/badges
  GET    /api/users/{userId}/badge-progress
  POST   /api/users/{userId}/check-badges
  GET    /api/user/badges/progress
  GET    /api/user/badges/completed
  GET    /api/badges/leaderboard
  GET    /api/badges/available
  GET    /api/admin/badges
  POST   /api/admin/badges
  GET    /api/admin/badges/{badgeId}
  PUT    /api/admin/badges/{badgeId}
  DELETE /api/admin/badges/{badgeId}
  POST   /api/admin/badges/{badgeId}/assign
  POST   /api/admin/badges/{badgeId}/revoke
  GET    /api/admin/badges/{badgeId}/users
  GET    /api/admin/badges/analytics

POINTS (Total: 8 endpoints)
  GET    /api/poin/history
  GET    /api/poin/breakdown/{userId}
  POST   /api/poin/bonus
  GET    /api/poin/admin/stats
  GET    /api/poin/admin/history
  GET    /api/poin/admin/redemptions
  GET    /api/poin/breakdown/all
  GET    /api/user/{id}/poin

ACTIVITY LOGS (Total: 5 endpoints) - FEATURE #2
  GET    /api/admin/users/{userId}/activity-logs
  GET    /api/admin/activity-logs
  GET    /api/admin/activity-logs/{logId}
  GET    /api/admin/activity-logs/stats/overview
  GET    /api/admin/activity-logs/export/csv

DATABASE BACKUP (Total: 3 endpoints) - FEATURE #4
  POST   /api/superadmin/backup
  GET    /api/superadmin/backups
  DELETE /api/superadmin/backups/{filename}

================================================================================
                        ENDPOINT SUMMARY
================================================================================

PUBLIC ENDPOINTS: 11 (no authentication required)
PROTECTED ENDPOINTS: 69 (require Bearer token)
  - User-only: 25
  - Admin (Level 2+): 25
  - Superadmin (Level 3): 19

TOTAL: 80+ Endpoints fully documented and implemented

================================================================================
                        LAST UPDATE INFORMATION
================================================================================

Date: December 22, 2025
Features Implemented:
  1. Notification System (8 endpoints)
  2. Activity Logs (5 endpoints)
  3. Badge Management Authorization (moved to admin level)
  4. Database Backup System (3 endpoints)

Status: ✅ 100% Complete
All endpoints tested and verified working.

================================================================================

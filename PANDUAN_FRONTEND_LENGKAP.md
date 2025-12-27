================================================================================
         PANDUAN LENGKAP MENGGUNAKAN MENDAUR API DI FRONTEND
================================================================================

Dibuat: 22 Desember 2025
Total Endpoints: 80+ API endpoints siap digunakan

================================================================================
                          DAFTAR ISI
================================================================================

1. SETUP AWAL
2. MENGGUNAKAN API CLIENT
3. CONTOH IMPLEMENTASI UNTUK SETIAP FITUR
4. ERROR HANDLING
5. ENVIRONMENT CONFIGURATION
6. TESTING ENDPOINT
7. DEPLOYMENT

================================================================================
                          1. SETUP AWAL
================================================================================

STEP 1: Copy file ke React Project
   - Salin API_LIST_FOR_FRONTEND.js ke folder: src/api/client.js
   - Salin .env.local.example ke root: .env.local

STEP 2: Install Dependencies (jika menggunakan package)
   npm install
   # atau
   yarn install

STEP 3: Configure Environment Variables
   Edit .env.local:
   REACT_APP_API_URL=http://localhost:8000/api

   Untuk production, ganti dengan domain sesungguhnya:
   REACT_APP_API_URL=https://api.mendaur.com/api

STEP 4: Buat API Instance
   Buat file src/api/index.js:

   ```javascript
   import MendaurAPI from './client';
   
   // Menggunakan environment variable
   const api = new MendaurAPI(
     process.env.REACT_APP_API_URL || 'http://localhost:8000/api'
   );
   
   export default api;
   ```

================================================================================
                   2. MENGGUNAKAN API CLIENT
================================================================================

BASIC USAGE:
   
   import api from './api';

   // Login
   const login = async () => {
     try {
       const response = await api.login('user@example.com', 'password');
       api.setToken(response.data.token);
       console.log('Login berhasil!');
     } catch (error) {
       console.error('Login gagal:', error);
     }
   };

   // Ambil data
   const getNotifications = async () => {
     try {
       const response = await api.getNotifications(1, 20);
       console.log('Notifikasi:', response.data);
     } catch (error) {
       console.error('Error:', error);
     }
   };

================================================================================
                3. CONTOH IMPLEMENTASI UNTUK SETIAP FITUR
================================================================================

A. NOTIFICATIONS (8 Endpoints)
==================================

   // 1. Ambil semua notifikasi dengan pagination
   const getAll = async (page = 1, perPage = 20) => {
     const response = await api.getNotifications(page, perPage);
     return response.data; // array of notifications
   };

   // 2. Ambil jumlah notifikasi belum dibaca
   const getUnreadCount = async () => {
     const response = await api.getUnreadCount();
     return response.data.unread_count; // integer
   };

   // 3. Ambil hanya notifikasi belum dibaca
   const getUnread = async () => {
     const response = await api.getUnreadNotifications();
     return response.data; // array
   };

   // 4. Ambil detail notifikasi
   const getDetail = async (id) => {
     const response = await api.getNotificationById(id);
     return response.data;
   };

   // 5. Mark satu notifikasi sebagai sudah dibaca
   const markAsRead = async (id) => {
     const response = await api.markNotificationAsRead(id);
     return response.message;
   };

   // 6. Mark semua notifikasi sebagai sudah dibaca
   const markAllAsRead = async () => {
     const response = await api.markAllNotificationsAsRead();
     return response.message;
   };

   // 7. Hapus notifikasi
   const deleteNotif = async (id) => {
     const response = await api.deleteNotification(id);
     return response.message;
   };

   // 8. Create notifikasi (Admin only)
   const createNotif = async (userId, judul, pesan) => {
     const response = await api.createNotification(userId, judul, pesan);
     return response.data;
   };

B. ACTIVITY LOGS (5 Endpoints) - FEATURE #2
==============================================

   // 1. Ambil activity logs user tertentu (Admin)
   const getUserLogs = async (userId, page = 1) => {
     const response = await api.getUserActivityLogs(userId, page, 50);
     return response.data;
   };

   // 2. Ambil semua activity logs dengan filter (Admin)
   const getAllLogs = async (filters = {}) => {
     const response = await api.getAllActivityLogs(1, 50, filters);
     return response.data;
   };
   
   // Usage:
   // getAllLogs({
   //   user_id: 5,
   //   activity_type: 'login',
   //   date_from: '2024-01-01',
   //   date_to: '2024-12-31'
   // });

   // 3. Ambil detail activity log (Admin)
   const getDetail = async (logId) => {
     const response = await api.getActivityLogById(logId);
     return response.data;
   };

   // 4. Ambil statistik activity (Admin)
   const getStats = async (dateFrom = null, dateTo = null) => {
     const response = await api.getActivityLogsStats(dateFrom, dateTo);
     return response.data;
   };

   // 5. Export ke CSV (Admin)
   const exportCSV = async (filters = {}) => {
     await api.exportActivityLogsCSV(filters);
     // Otomatis download file CSV
   };

C. BADGE MANAGEMENT (8 Endpoints - Admin Level)
================================================

   // 1. Ambil semua badge (Admin)
   const getAllBadges = async () => {
     const response = await api.getAllBadgesAdmin();
     return response.data;
   };

   // 2. Ambil detail badge (Admin)
   const getBadgeDetail = async (badgeId) => {
     const response = await api.getBadgeAdminById(badgeId);
     return response.data;
   };

   // 3. Create badge (Admin)
   const createBadge = async (nama, deskripsi, poinDiperlukan, gambar) => {
     const response = await api.createBadge(
       nama, 
       deskripsi, 
       poinDiperlukan, 
       gambar
     );
     return response.data;
   };

   // 4. Update badge (Admin)
   const updateBadge = async (badgeId, data) => {
     const response = await api.updateBadge(badgeId, data);
     return response.data;
   };

   // 5. Hapus badge (Admin)
   const deleteBadge = async (badgeId) => {
     const response = await api.deleteBadge(badgeId);
     return response.message;
   };

   // 6. Assign badge ke user (Admin)
   const assignToUser = async (badgeId, userId) => {
     const response = await api.assignBadgeToUser(badgeId, userId);
     return response.data;
   };

   // 7. Revoke badge dari user (Admin)
   const revokeFromUser = async (badgeId, userId) => {
     const response = await api.revokeBadgeFromUser(badgeId, userId);
     return response.message;
   };

   // 8. Ambil users dengan badge tertentu (Admin)
   const getUsersWithBadge = async (badgeId, page = 1) => {
     const response = await api.getUsersWithBadge(badgeId, page, 50);
     return response.data;
   };

D. DATABASE BACKUP (3 Endpoints) - FEATURE #4
==============================================

   // 1. Create backup (Superadmin only)
   const createBackup = async () => {
     const response = await api.createBackup();
     return response.data; // { filename, size, created_at }
   };

   // 2. Ambil daftar backup (Superadmin only)
   const listBackups = async () => {
     const response = await api.listBackups();
     return response.data; // array of backups
   };

   // 3. Hapus backup (Superadmin only)
   const deleteBackup = async (filename) => {
     const response = await api.deleteBackup(filename);
     return response.message;
   };

E. WASTE DEPOSITS (11 Endpoints)
=================================

   // User routes
   const getMyDeposits = async (page = 1) => {
     const response = await api.getTabungSampah(page, 20);
     return response.data;
   };

   const createDeposit = async (kategoriId, jenisId, berat, jadwalId) => {
     const response = await api.createTabungSampah({
       kategori_sampah_id: kategoriId,
       jenis_sampah_id: jenisId,
       berat: berat,
       jadwal_penyetoran_id: jadwalId
     });
     return response.data;
   };

   // Admin routes
   const getAllDeposits = async (filters = {}) => {
     const response = await api.getAllPenyetoranSampah(1, 50, filters);
     return response.data;
   };

   const approveDeposit = async (id) => {
     const response = await api.approvePenyetoranSampah(id);
     return response.data;
   };

   const rejectDeposit = async (id, catatan) => {
     const response = await api.rejectPenyetoranSampah(id, catatan);
     return response.data;
   };

F. PRODUCT EXCHANGE (11 Endpoints)
===================================

   // User routes
   const getMyExchanges = async (page = 1) => {
     const response = await api.getPenukaranProduk(page, 20);
     return response.data;
   };

   const requestExchange = async (produkId, jumlah) => {
     const response = await api.requestPenukaranProduk(produkId, jumlah);
     return response.data;
   };

   // Admin routes
   const getAllExchanges = async (filters = {}) => {
     const response = await api.getAllPenukaranProduk(1, 50, filters);
     return response.data;
   };

   const approveExchange = async (id) => {
     const response = await api.approvePenukaranProduk(id);
     return response.data;
   };

G. CASH WITHDRAWAL (10 Endpoints)
==================================

   // User routes
   const getMyWithdrawals = async (page = 1) => {
     const response = await api.getPenarikanTunai(page, 20);
     return response.data;
   };

   const requestWithdrawal = async (jumlah, nomorRekening, namaRekening) => {
     const response = await api.requestPenarikanTunai(
       jumlah, 
       nomorRekening, 
       namaRekening
     );
     return response.data;
   };

   // Admin routes
   const getAllWithdrawals = async (filters = {}) => {
     const response = await api.getAllPenarikanTunai(1, 50, filters);
     return response.data;
   };

   const approveWithdrawal = async (id, reference = '') => {
     const response = await api.approvePenarikanTunai(id, reference);
     return response.data;
   };

================================================================================
                        4. ERROR HANDLING
================================================================================

STRUCTURED ERROR HANDLING:

   const handleAPIError = (error) => {
     if (error.response) {
       // Server responded with error status
       const { status, data } = error.response;
       
       if (status === 401) {
         // Unauthorized - token expired
         localStorage.removeItem('token');
         window.location.href = '/login';
       } else if (status === 403) {
         // Forbidden - insufficient permissions
         alert('Anda tidak memiliki akses ke resource ini');
       } else if (status === 404) {
         // Not found
         alert('Resource tidak ditemukan');
       } else if (status === 422) {
         // Validation error
         console.error('Validation errors:', data.errors);
       } else if (status === 500) {
         // Server error
         alert('Terjadi kesalahan pada server');
       }
     } else if (error.request) {
       // Request made but no response
       alert('Tidak dapat menghubungi server');
     } else {
       // Error setting up request
       console.error('Error:', error.message);
     }
   };

USAGE IN TRY-CATCH:

   try {
     const response = await api.getNotifications();
     console.log('Data:', response.data);
   } catch (error) {
     handleAPIError(error);
   }

================================================================================
                 5. ENVIRONMENT CONFIGURATION
================================================================================

FILE: .env.local

   # Development
   REACT_APP_API_URL=http://localhost:8000/api
   
   # Staging
   # REACT_APP_API_URL=https://staging-api.mendaur.com/api
   
   # Production
   # REACT_APP_API_URL=https://api.mendaur.com/api

HOW TO USE IN CODE:

   const api = new MendaurAPI(process.env.REACT_APP_API_URL);

HOW TO CHANGE FOR DIFFERENT ENVIRONMENTS:

   1. Development: npm start (menggunakan .env.local)
   2. Staging: REACT_APP_API_URL=... npm run build
   3. Production: Build dengan production .env

================================================================================
                       6. TESTING ENDPOINT
================================================================================

TESTING DI POSTMAN:

   1. Import endpoint list dari COMPLETE_API_ENDPOINTS_LIST.md
   2. Set environment variables:
      - {{BASE_URL}} = http://localhost:8000/api
      - {{TOKEN}} = (dari login response)
   3. Test setiap endpoint
   4. Verifikasi response

TESTING DI BROWSER CONSOLE:

   // Paste ke browser console setelah page load
   const api = new MendaurAPI('http://localhost:8000/api');
   
   // Test login
   api.login('user@example.com', 'password').then(r => {
     api.setToken(r.data.token);
     console.log('Login successful!');
   });
   
   // Test get notifications
   api.getNotifications().then(r => console.log(r.data));

================================================================================
                        7. DEPLOYMENT
================================================================================

PRODUCTION BUILD:

   1. Update .env dengan production API URL:
      REACT_APP_API_URL=https://api.mendaur.com/api

   2. Build aplikasi:
      npm run build

   3. Deploy ke server:
      - Upload build/ folder ke hosting
      - Pastikan API URL bisa diakses dari frontend
      - Test semua endpoint di production

CORS CONFIGURATION (Backend):

   Pastikan Laravel CORS dikonfigurasi dengan benar di config/cors.php:
   
   'allowed_origins' => [
       'http://localhost:3000',      // Development React
       'https://frontend.mendaur.com' // Production frontend
   ]

SECURITY CONSIDERATIONS:

   1. Jangan hardcode API URL - gunakan environment variables
   2. Jangan commit .env file ke git
   3. Gunakan HTTPS di production
   4. Implement token refresh untuk long-lived sessions
   5. Validate input sebelum mengirim ke API
   6. Sanitize output dari API

================================================================================
                    QUICK REFERENCE - ALL ENDPOINTS
================================================================================

PUBLIC (No Auth Required):
   ✓ POST   /api/login
   ✓ POST   /api/register
   ✓ GET    /api/jadwal-penyetoran
   ✓ GET    /api/jenis-sampah
   ✓ GET    /api/kategori-sampah
   ✓ GET    /api/produk
   ✓ GET    /api/artikel
   ✓ GET    /api/badges
   ✓ GET    /api/dashboard/global-stats

USER (Auth Required):
   ✓ GET    /api/profile
   ✓ GET    /api/notifications (8 endpoints)
   ✓ GET    /api/tabung-sampah (5 endpoints)
   ✓ GET    /api/penukaran-produk (5 endpoints)
   ✓ GET    /api/penarikan-tunai (4 endpoints)
   ✓ GET    /api/poin/history
   ✓ GET    /api/user/badges/progress

ADMIN (Auth + Admin Role):
   ✓ GET    /api/admin/dashboard/overview
   ✓ GET    /api/admin/users
   ✓ GET    /api/admin/activity-logs (5 endpoints) - FEATURE #2
   ✓ GET    /api/admin/penyetoran-sampah (7 endpoints)
   ✓ GET    /api/admin/penukar-produk (6 endpoints)
   ✓ GET    /api/admin/penarikan-tunai (6 endpoints)
   ✓ GET    /api/admin/badges (8 endpoints) - Moved from superadmin
   ✓ GET    /api/admin/analytics/...
   ✓ GET    /api/admin/leaderboard

SUPERADMIN (Auth + Superadmin Role):
   ✓ POST   /api/superadmin/backup (3 endpoints) - FEATURE #4
   ✓ GET    /api/superadmin/admins (6 endpoints)
   ✓ GET    /api/superadmin/roles (6 endpoints)
   ✓ GET    /api/superadmin/permissions
   ✓ GET    /api/superadmin/audit-logs (6 endpoints)
   ✓ GET    /api/superadmin/settings
   ✓ GET    /api/superadmin/health
   ✓ GET    /api/superadmin/cache-stats

TOTAL: 80+ Endpoints fully functional

================================================================================
                         CONTACT & SUPPORT
================================================================================

Untuk pertanyaan atau masalah:

1. Lihat dokumentasi di COMPLETE_API_ENDPOINTS_LIST.md
2. Cek API_LIST_FOR_FRONTEND.js untuk method signatures
3. Review contoh di panduan ini
4. Test dengan Postman menggunakan endpoint list

CHECKLIST IMPLEMENTASI:

[ ] Setup project React
[ ] Copy API_LIST_FOR_FRONTEND.js ke src/api/client.js
[ ] Setup .env.local dengan API URL
[ ] Create api/index.js dengan MendaurAPI instance
[ ] Implement error handling
[ ] Create services untuk setiap fitur
[ ] Implement React components
[ ] Test semua endpoints
[ ] Deploy ke production

================================================================================
                            DOKUMENTASI SELESAI
================================================================================

Generated: 22 December 2025
Version: 1.0.0
Status: Production Ready ✅

Semua 80+ endpoints siap digunakan oleh frontend!

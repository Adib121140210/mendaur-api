/**
 * ============================================================================
 * MENDAUR API - COMPLETE ENDPOINT LIST FOR FRONTEND
 * ============================================================================
 * 
 * File ini berisi semua endpoint API yang siap digunakan oleh frontend
 * dengan environment variable BASE_URL (tidak hardcode localhost)
 * 
 * Usage:
 *   const api = new MendaurAPI(process.env.REACT_APP_API_URL);
 *   api.login(email, password);
 * 
 * ============================================================================
 */

// ============================================================================
// API CLIENT CLASS - MENGGUNAKAN ENVIRONMENT VARIABLE
// ============================================================================

class MendaurAPI {
  constructor(baseURL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api') {
    this.baseURL = baseURL;
    this.token = localStorage.getItem('token') || null;
  }

  /**
   * Set token dari login
   */
  setToken(token) {
    this.token = token;
    localStorage.setItem('token', token);
  }

  /**
   * Get headers dengan authorization
   */
  getHeaders() {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };
    
    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }
    
    return headers;
  }

  /**
   * Generic request method
   */
  async request(method, endpoint, body = null) {
    const url = `${this.baseURL}${endpoint}`;
    const config = {
      method,
      headers: this.getHeaders()
    };

    if (body) {
      config.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(url, config);

      if (response.status === 401) {
        localStorage.removeItem('token');
        window.location.href = '/login';
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  // ========================================================================
  // 1. AUTHENTICATION ENDPOINTS
  // ========================================================================

  /**
   * POST /api/login
   * Login user dan dapatkan token
   */
  async login(email, password) {
    return this.request('POST', '/login', { email, password });
  }

  /**
   * POST /api/register
   * Register user baru
   */
  async register(data) {
    return this.request('POST', '/register', data);
  }

  /**
   * POST /api/logout
   * Logout user
   */
  async logout() {
    return this.request('POST', '/logout');
  }

  /**
   * GET /api/profile
   * Ambil profil user yang login
   */
  async getProfile() {
    return this.request('GET', '/profile');
  }

  /**
   * PUT /api/profile
   * Update profil user
   */
  async updateProfile(data) {
    return this.request('PUT', '/profile', data);
  }

  // ========================================================================
  // 2. PUBLIC ENDPOINTS (Tidak perlu auth)
  // ========================================================================

  /**
   * GET /api/jadwal-penyetoran
   * Ambil semua jadwal penyetoran
   */
  async getJadwalPenyetoran(page = 1, perPage = 20) {
    return this.request('GET', `/jadwal-penyetoran?page=${page}&per_page=${perPage}`);
  }

  /**
   * GET /api/jadwal-penyetoran/{id}
   * Ambil detail jadwal penyetoran
   */
  async getJadwalPenyetoranById(id) {
    return this.request('GET', `/jadwal-penyetoran/${id}`);
  }

  /**
   * GET /api/jadwal-penyetoran-aktif
   * Ambil jadwal penyetoran yang sedang aktif
   */
  async getJadwalPenyetoranAktif() {
    return this.request('GET', '/jadwal-penyetoran-aktif');
  }

  /**
   * GET /api/jenis-sampah
   * Ambil semua jenis sampah
   */
  async getJenisSampah() {
    return this.request('GET', '/jenis-sampah');
  }

  /**
   * GET /api/jenis-sampah/{id}
   * Ambil detail jenis sampah
   */
  async getJenisSampahById(id) {
    return this.request('GET', `/jenis-sampah/${id}`);
  }

  /**
   * GET /api/kategori-sampah
   * Ambil semua kategori sampah
   */
  async getKategoriSampah() {
    return this.request('GET', '/kategori-sampah');
  }

  /**
   * GET /api/kategori-sampah/{id}
   * Ambil detail kategori sampah
   */
  async getKategoriSampahById(id) {
    return this.request('GET', `/kategori-sampah/${id}`);
  }

  /**
   * GET /api/kategori-sampah/{id}/jenis
   * Ambil jenis sampah berdasarkan kategori
   */
  async getJenisByKategori(kategoriId) {
    return this.request('GET', `/kategori-sampah/${kategoriId}/jenis`);
  }

  /**
   * GET /api/jenis-sampah-all
   * Ambil flat list semua jenis sampah
   */
  async getAllJenisSampah() {
    return this.request('GET', '/jenis-sampah-all');
  }

  /**
   * GET /api/produk
   * Ambil semua produk
   */
  async getProduk(page = 1, perPage = 20, kategori = null) {
    let url = `/produk?page=${page}&per_page=${perPage}`;
    if (kategori) url += `&kategori=${kategori}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/produk/{id}
   * Ambil detail produk
   */
  async getProdukById(id) {
    return this.request('GET', `/produk/${id}`);
  }

  /**
   * GET /api/artikel
   * Ambil semua artikel
   */
  async getArtikel(page = 1, perPage = 10) {
    return this.request('GET', `/artikel?page=${page}&per_page=${perPage}`);
  }

  /**
   * GET /api/artikel/{slug}
   * Ambil detail artikel
   */
  async getArtikelBySlug(slug) {
    return this.request('GET', `/artikel/${slug}`);
  }

  /**
   * GET /api/dashboard/global-stats
   * Ambil statistik global
   */
  async getGlobalStats() {
    return this.request('GET', '/dashboard/global-stats');
  }

  /**
   * GET /api/badges
   * Ambil semua badge (public)
   */
  async getAllBadges() {
    return this.request('GET', '/badges');
  }

  // ========================================================================
  // 3. NOTIFICATION ENDPOINTS (Require Auth)
  // ========================================================================

  /**
   * GET /api/notifications
   * Ambil notifikasi user
   */
  async getNotifications(page = 1, perPage = 20) {
    return this.request('GET', `/notifications?page=${page}&per_page=${perPage}`);
  }

  /**
   * GET /api/notifications/unread-count
   * Ambil jumlah notifikasi belum dibaca
   */
  async getUnreadCount() {
    return this.request('GET', '/notifications/unread-count');
  }

  /**
   * GET /api/notifications/unread
   * Ambil notifikasi yang belum dibaca
   */
  async getUnreadNotifications() {
    return this.request('GET', '/notifications/unread');
  }

  /**
   * GET /api/notifications/{id}
   * Ambil detail notifikasi
   */
  async getNotificationById(id) {
    return this.request('GET', `/notifications/${id}`);
  }

  /**
   * PATCH /api/notifications/{id}/read
   * Mark notifikasi sebagai sudah dibaca
   */
  async markNotificationAsRead(id) {
    return this.request('PATCH', `/notifications/${id}/read`, {});
  }

  /**
   * PATCH /api/notifications/mark-all-read
   * Mark semua notifikasi sebagai sudah dibaca
   */
  async markAllNotificationsAsRead() {
    return this.request('PATCH', '/notifications/mark-all-read', {});
  }

  /**
   * DELETE /api/notifications/{id}
   * Hapus notifikasi
   */
  async deleteNotification(id) {
    return this.request('DELETE', `/notifications/${id}`);
  }

  /**
   * POST /api/notifications/create (Admin only)
   * Create notifikasi untuk user
   */
  async createNotification(userId, judul, pesan) {
    return this.request('POST', '/notifications/create', {
      user_id: userId,
      judul,
      pesan
    });
  }

  // ========================================================================
  // 4. WASTE DEPOSIT ENDPOINTS (Tabung Sampah)
  // ========================================================================

  /**
   * GET /api/tabung-sampah
   * Ambil tabung sampah user
   */
  async getTabungSampah(page = 1, perPage = 20, status = null) {
    let url = `/tabung-sampah?page=${page}&per_page=${perPage}`;
    if (status) url += `&status=${status}`;
    return this.request('GET', url);
  }

  /**
   * POST /api/tabung-sampah
   * Create tabung sampah baru
   */
  async createTabungSampah(data) {
    return this.request('POST', '/tabung-sampah', data);
  }

  /**
   * GET /api/tabung-sampah/{id}
   * Ambil detail tabung sampah
   */
  async getTabungSampahById(id) {
    return this.request('GET', `/tabung-sampah/${id}`);
  }

  /**
   * PATCH /api/tabung-sampah/{id}/approve
   * Approve tabung sampah (Admin)
   */
  async approveTabungSampah(id, catatan = '') {
    return this.request('PATCH', `/tabung-sampah/${id}/approve`, { catatan });
  }

  /**
   * PATCH /api/tabung-sampah/{id}/reject
   * Reject tabung sampah (Admin)
   */
  async rejectTabungSampah(id, catatan) {
    return this.request('PATCH', `/tabung-sampah/${id}/reject`, { catatan });
  }

  /**
   * PUT /api/tabung-sampah/{id}
   * Update tabung sampah
   */
  async updateTabungSampah(id, data) {
    return this.request('PUT', `/tabung-sampah/${id}`, data);
  }

  /**
   * DELETE /api/tabung-sampah/{id}
   * Hapus tabung sampah
   */
  async deleteTabungSampah(id) {
    return this.request('DELETE', `/tabung-sampah/${id}`);
  }

  /**
   * GET /api/users/{id}/tabung-sampah
   * Ambil tabung sampah user tertentu
   */
  async getUserTabungSampah(userId) {
    return this.request('GET', `/users/${userId}/tabung-sampah`);
  }

  /**
   * GET /api/admin/penyetoran-sampah
   * List semua penyetoran (Admin)
   */
  async getAllPenyetoranSampah(page = 1, perPage = 50, filters = {}) {
    let url = `/admin/penyetoran-sampah?page=${page}&per_page=${perPage}`;
    if (filters.user_id) url += `&user_id=${filters.user_id}`;
    if (filters.status) url += `&status=${filters.status}`;
    if (filters.date_from) url += `&date_from=${filters.date_from}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/penyetoran-sampah/{id}
   * Ambil detail penyetoran (Admin)
   */
  async getPenyetoranSampahById(id) {
    return this.request('GET', `/admin/penyetoran-sampah/${id}`);
  }

  /**
   * PATCH /api/admin/penyetoran-sampah/{id}/approve
   * Approve penyetoran (Admin)
   */
  async approvePenyetoranSampah(id, catatan = '') {
    return this.request('PATCH', `/admin/penyetoran-sampah/${id}/approve`, { catatan });
  }

  /**
   * PATCH /api/admin/penyetoran-sampah/{id}/reject
   * Reject penyetoran (Admin)
   */
  async rejectPenyetoranSampah(id, catatan) {
    return this.request('PATCH', `/admin/penyetoran-sampah/${id}/reject`, { catatan });
  }

  /**
   * DELETE /api/admin/penyetoran-sampah/{id}
   * Hapus penyetoran (Admin)
   */
  async deletePenyetoranSampah(id) {
    return this.request('DELETE', `/admin/penyetoran-sampah/${id}`);
  }

  /**
   * GET /api/admin/penyetoran-sampah/stats/overview
   * Ambil statistik penyetoran (Admin)
   */
  async getPenyetoranStats() {
    return this.request('GET', '/admin/penyetoran-sampah/stats/overview');
  }

  // ========================================================================
  // 5. PRODUCT EXCHANGE ENDPOINTS (Penukaran Produk)
  // ========================================================================

  /**
   * GET /api/penukaran-produk
   * Ambil penukaran produk user
   */
  async getPenukaranProduk(page = 1, perPage = 20, status = null) {
    let url = `/penukaran-produk?page=${page}&per_page=${perPage}`;
    if (status) url += `&status=${status}`;
    return this.request('GET', url);
  }

  /**
   * POST /api/penukaran-produk
   * Request penukaran produk
   */
  async requestPenukaranProduk(produkId, jumlah) {
    return this.request('POST', '/penukaran-produk', {
      produk_id: produkId,
      jumlah
    });
  }

  /**
   * GET /api/penukaran-produk/{id}
   * Ambil detail penukaran produk
   */
  async getPenukaranProdukById(id) {
    return this.request('GET', `/penukaran-produk/${id}`);
  }

  /**
   * PUT /api/penukaran-produk/{id}/cancel
   * Cancel penukaran produk
   */
  async cancelPenukaranProduk(id) {
    return this.request('PUT', `/penukaran-produk/${id}/cancel`, {});
  }

  /**
   * DELETE /api/penukaran-produk/{id}
   * Hapus penukaran produk
   */
  async deletePenukaranProduk(id) {
    return this.request('DELETE', `/penukaran-produk/${id}`);
  }

  /**
   * GET /api/admin/penukar-produk
   * List semua penukaran (Admin)
   */
  async getAllPenukaranProduk(page = 1, perPage = 50, filters = {}) {
    let url = `/admin/penukar-produk?page=${page}&per_page=${perPage}`;
    if (filters.user_id) url += `&user_id=${filters.user_id}`;
    if (filters.status) url += `&status=${filters.status}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/penukar-produk/{exchangeId}
   * Ambil detail penukaran (Admin)
   */
  async getPenukaranProdukAdminById(id) {
    return this.request('GET', `/admin/penukar-produk/${id}`);
  }

  /**
   * PATCH /api/admin/penukar-produk/{exchangeId}/approve
   * Approve penukaran (Admin)
   */
  async approvePenukaranProduk(id, catatan = '') {
    return this.request('PATCH', `/admin/penukar-produk/${id}/approve`, { catatan });
  }

  /**
   * PATCH /api/admin/penukar-produk/{exchangeId}/reject
   * Reject penukaran (Admin)
   */
  async rejectPenukaranProduk(id, catatan) {
    return this.request('PATCH', `/admin/penukar-produk/${id}/reject`, { catatan });
  }

  /**
   * DELETE /api/admin/penukar-produk/{exchangeId}
   * Hapus penukaran (Admin)
   */
  async deletePenukaranProdukAdmin(id) {
    return this.request('DELETE', `/admin/penukar-produk/${id}`);
  }

  /**
   * GET /api/admin/penukar-produk/stats/overview
   * Ambil statistik penukaran (Admin)
   */
  async getPenukaranStats() {
    return this.request('GET', '/admin/penukar-produk/stats/overview');
  }

  // ========================================================================
  // 6. CASH WITHDRAWAL ENDPOINTS (Penarikan Tunai)
  // ========================================================================

  /**
   * GET /api/penarikan-tunai
   * Ambil penarikan tunai user
   */
  async getPenarikanTunai(page = 1, perPage = 20, status = null) {
    let url = `/penarikan-tunai?page=${page}&per_page=${perPage}`;
    if (status) url += `&status=${status}`;
    return this.request('GET', url);
  }

  /**
   * POST /api/penarikan-tunai
   * Request penarikan tunai
   */
  async requestPenarikanTunai(jumlah, rekening_bank, nama_rekening) {
    return this.request('POST', '/penarikan-tunai', {
      jumlah,
      rekening_bank,
      nama_rekening
    });
  }

  /**
   * GET /api/penarikan-tunai/{id}
   * Ambil detail penarikan tunai
   */
  async getPenarikanTunaiById(id) {
    return this.request('GET', `/penarikan-tunai/${id}`);
  }

  /**
   * GET /api/penarikan-tunai/summary
   * Ambil summary penarikan tunai
   */
  async getPenarikanTunaiSummary() {
    return this.request('GET', '/penarikan-tunai/summary');
  }

  /**
   * GET /api/admin/penarikan-tunai
   * List semua penarikan tunai (Admin)
   */
  async getAllPenarikanTunai(page = 1, perPage = 50, filters = {}) {
    let url = `/admin/penarikan-tunai?page=${page}&per_page=${perPage}`;
    if (filters.user_id) url += `&user_id=${filters.user_id}`;
    if (filters.status) url += `&status=${filters.status}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/penarikan-tunai/{withdrawalId}
   * Ambil detail penarikan tunai (Admin)
   */
  async getPenarikanTunaiAdminById(id) {
    return this.request('GET', `/admin/penarikan-tunai/${id}`);
  }

  /**
   * PATCH /api/admin/penarikan-tunai/{withdrawalId}/approve
   * Approve penarikan tunai (Admin)
   */
  async approvePenarikanTunai(id, reference_number = '', catatan = '') {
    return this.request('PATCH', `/admin/penarikan-tunai/${id}/approve`, {
      reference_number,
      catatan
    });
  }

  /**
   * PATCH /api/admin/penarikan-tunai/{withdrawalId}/reject
   * Reject penarikan tunai (Admin)
   */
  async rejectPenarikanTunai(id, catatan) {
    return this.request('PATCH', `/admin/penarikan-tunai/${id}/reject`, { catatan });
  }

  /**
   * DELETE /api/admin/penarikan-tunai/{withdrawalId}
   * Hapus penarikan tunai (Admin)
   */
  async deletePenarikanTunai(id) {
    return this.request('DELETE', `/admin/penarikan-tunai/${id}`);
  }

  /**
   * GET /api/admin/penarikan-tunai/stats/overview
   * Ambil statistik penarikan tunai (Admin)
   */
  async getPenarikanTunaiStats() {
    return this.request('GET', '/admin/penarikan-tunai/stats/overview');
  }

  // ========================================================================
  // 7. BADGE ENDPOINTS
  // ========================================================================

  /**
   * GET /api/user/badges/progress
   * Ambil progress badge user
   */
  async getUserBadgesProgress() {
    return this.request('GET', '/user/badges/progress');
  }

  /**
   * GET /api/user/badges/completed
   * Ambil badge yang sudah diselesaikan
   */
  async getCompletedBadges() {
    return this.request('GET', '/user/badges/completed');
  }

  /**
   * GET /api/badges/leaderboard
   * Ambil leaderboard badge
   */
  async getBadgesLeaderboard(limit = 50) {
    return this.request('GET', `/badges/leaderboard?limit=${limit}`);
  }

  /**
   * GET /api/badges/available
   * Ambil badge yang tersedia
   */
  async getAvailableBadges() {
    return this.request('GET', '/badges/available');
  }

  /**
   * GET /api/users/{userId}/badge-progress
   * Ambil progress badge user tertentu
   */
  async getUserBadgeProgress(userId) {
    return this.request('GET', `/users/${userId}/badge-progress`);
  }

  /**
   * POST /api/users/{userId}/check-badges
   * Check dan award badge untuk user
   */
  async checkAndAwardBadges(userId) {
    return this.request('POST', `/users/${userId}/check-badges`, {});
  }

  /**
   * GET /api/admin/badges
   * List semua badge (Admin)
   */
  async getAllBadgesAdmin() {
    return this.request('GET', '/admin/badges');
  }

  /**
   * GET /api/admin/badges/{badgeId}
   * Ambil detail badge (Admin)
   */
  async getBadgeAdminById(id) {
    return this.request('GET', `/admin/badges/${id}`);
  }

  /**
   * POST /api/admin/badges
   * Create badge (Admin)
   */
  async createBadge(nama, deskripsi, poinDiperlukan, gambar = null) {
    const formData = new FormData();
    formData.append('nama', nama);
    formData.append('deskripsi', deskripsi);
    formData.append('poin_diperlukan', poinDiperlukan);
    if (gambar) formData.append('gambar', gambar);

    return fetch(`${this.baseURL}/admin/badges`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${this.token}` },
      body: formData
    }).then(r => r.json());
  }

  /**
   * PUT /api/admin/badges/{badgeId}
   * Update badge (Admin)
   */
  async updateBadge(id, data) {
    return this.request('PUT', `/admin/badges/${id}`, data);
  }

  /**
   * DELETE /api/admin/badges/{badgeId}
   * Hapus badge (Admin)
   */
  async deleteBadge(id) {
    return this.request('DELETE', `/admin/badges/${id}`);
  }

  /**
   * POST /api/admin/badges/{badgeId}/assign
   * Assign badge ke user (Admin)
   */
  async assignBadgeToUser(badgeId, userId) {
    return this.request('POST', `/admin/badges/${badgeId}/assign`, {
      user_id: userId
    });
  }

  /**
   * POST /api/admin/badges/{badgeId}/revoke
   * Revoke badge dari user (Admin)
   */
  async revokeBadgeFromUser(badgeId, userId) {
    return this.request('POST', `/admin/badges/${badgeId}/revoke`, {
      user_id: userId
    });
  }

  /**
   * GET /api/admin/badges/{badgeId}/users
   * Ambil users dengan badge tertentu (Admin)
   */
  async getUsersWithBadge(badgeId, page = 1, perPage = 50) {
    return this.request('GET', `/admin/badges/${badgeId}/users?page=${page}&per_page=${perPage}`);
  }

  /**
   * GET /api/admin/badges/analytics
   * Ambil analytics badge (Admin)
   */
  async getBadgesAnalytics() {
    return this.request('GET', '/admin/badges/analytics');
  }

  // ========================================================================
  // 8. POINTS ENDPOINTS
  // ========================================================================

  /**
   * GET /api/poin/history
   * Ambil history poin user
   */
  async getPointHistory(page = 1, perPage = 20, type = null) {
    let url = `/poin/history?page=${page}&per_page=${perPage}`;
    if (type) url += `&type=${type}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/poin/breakdown/{userId}
   * Ambil breakdown poin user
   */
  async getPointBreakdown(userId) {
    return this.request('GET', `/poin/breakdown/${userId}`);
  }

  /**
   * POST /api/poin/bonus
   * Award bonus points (Admin)
   */
  async awardBonusPoints(userId, jumlah, keterangan) {
    return this.request('POST', '/poin/bonus', {
      user_id: userId,
      jumlah,
      keterangan
    });
  }

  /**
   * GET /api/poin/admin/stats
   * Ambil poin stats (Admin)
   */
  async getPointStats() {
    return this.request('GET', '/poin/admin/stats');
  }

  /**
   * GET /api/poin/admin/history
   * Ambil semua point history (Admin)
   */
  async getAllPointHistory(page = 1, perPage = 50, userId = null) {
    let url = `/poin/admin/history?page=${page}&per_page=${perPage}`;
    if (userId) url += `&user_id=${userId}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/poin/admin/redemptions
   * Ambil redemption (Admin)
   */
  async getPointRedemptions() {
    return this.request('GET', '/poin/admin/redemptions');
  }

  /**
   * GET /api/poin/breakdown/all
   * Ambil breakdown semua user (Admin)
   */
  async getAllPointBreakdown() {
    return this.request('GET', '/poin/breakdown/all');
  }

  /**
   * GET /api/user/{id}/poin
   * Ambil poin user
   */
  async getUserPoints(userId) {
    return this.request('GET', `/user/${userId}/poin`);
  }

  // ========================================================================
  // 9. ACTIVITY LOGS ENDPOINTS (Feature #2)
  // ========================================================================

  /**
   * GET /api/admin/users/{userId}/activity-logs
   * Ambil activity logs user tertentu (Admin)
   */
  async getUserActivityLogs(userId, page = 1, perPage = 50) {
    return this.request('GET', `/admin/users/${userId}/activity-logs?page=${page}&per_page=${perPage}`);
  }

  /**
   * GET /api/admin/activity-logs
   * Ambil semua activity logs dengan filter (Admin)
   */
  async getAllActivityLogs(page = 1, perPage = 50, filters = {}) {
    let url = `/admin/activity-logs?page=${page}&per_page=${perPage}`;
    if (filters.user_id) url += `&user_id=${filters.user_id}`;
    if (filters.activity_type) url += `&activity_type=${filters.activity_type}`;
    if (filters.date_from) url += `&date_from=${filters.date_from}`;
    if (filters.date_to) url += `&date_to=${filters.date_to}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/activity-logs/{logId}
   * Ambil detail activity log (Admin)
   */
  async getActivityLogById(id) {
    return this.request('GET', `/admin/activity-logs/${id}`);
  }

  /**
   * GET /api/admin/activity-logs/stats/overview
   * Ambil statistik activity logs (Admin)
   */
  async getActivityLogsStats(dateFrom = null, dateTo = null) {
    let url = '/admin/activity-logs/stats/overview';
    if (dateFrom || dateTo) {
      url += '?';
      if (dateFrom) url += `date_from=${dateFrom}`;
      if (dateTo) url += `${dateFrom ? '&' : ''}date_to=${dateTo}`;
    }
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/activity-logs/export/csv
   * Export activity logs ke CSV (Admin)
   */
  async exportActivityLogsCSV(filters = {}) {
    let url = '/admin/activity-logs/export/csv?';
    if (filters.user_id) url += `user_id=${filters.user_id}&`;
    if (filters.date_from) url += `date_from=${filters.date_from}&`;
    if (filters.date_to) url += `date_to=${filters.date_to}`;

    const response = await fetch(`${this.baseURL}${url}`, {
      method: 'GET',
      headers: { 'Authorization': `Bearer ${this.token}` }
    });

    const blob = await response.blob();
    const urlObj = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = urlObj;
    a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(urlObj);
  }

  // ========================================================================
  // 10. BACKUP ENDPOINTS (Feature #4) - Superadmin only
  // ========================================================================

  /**
   * POST /api/superadmin/backup
   * Create database backup (Superadmin)
   */
  async createBackup() {
    return this.request('POST', '/superadmin/backup', {});
  }

  /**
   * GET /api/superadmin/backups
   * List semua backups (Superadmin)
   */
  async listBackups() {
    return this.request('GET', '/superadmin/backups');
  }

  /**
   * DELETE /api/superadmin/backups/{filename}
   * Hapus backup (Superadmin)
   */
  async deleteBackup(filename) {
    return this.request('DELETE', `/superadmin/backups/${filename}`);
  }

  // ========================================================================
  // 11. ADMIN ROUTES
  // ========================================================================

  /**
   * GET /api/admin/dashboard/overview
   * Ambil admin dashboard overview
   */
  async getAdminDashboardOverview() {
    return this.request('GET', '/admin/dashboard/overview');
  }

  /**
   * GET /api/admin/dashboard/stats
   * Ambil admin dashboard stats
   */
  async getAdminDashboardStats() {
    return this.request('GET', '/admin/dashboard/stats');
  }

  /**
   * GET /api/admin/users
   * List semua users (Admin)
   */
  async getAllUsers(page = 1, perPage = 50, search = null, status = null, role = null) {
    let url = `/admin/users?page=${page}&per_page=${perPage}`;
    if (search) url += `&search=${search}`;
    if (status) url += `&status=${status}`;
    if (role) url += `&role=${role}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/users/{userId}
   * Ambil detail user (Admin)
   */
  async getAdminUserById(userId) {
    return this.request('GET', `/admin/users/${userId}`);
  }

  /**
   * PUT /api/admin/users/{userId}
   * Update user (Admin)
   */
  async updateAdminUser(userId, data) {
    return this.request('PUT', `/admin/users/${userId}`, data);
  }

  /**
   * PATCH /api/admin/users/{userId}/status
   * Update user status (Admin)
   */
  async updateUserStatus(userId, status) {
    return this.request('PATCH', `/admin/users/${userId}/status`, { status });
  }

  /**
   * PATCH /api/admin/users/{userId}/role
   * Update user role (Admin)
   */
  async updateUserRole(userId, roleId) {
    return this.request('PATCH', `/admin/users/${userId}/role`, { role_id: roleId });
  }

  /**
   * PATCH /api/admin/users/{userId}/tipe
   * Update user type (Admin)
   */
  async updateUserType(userId, tipe) {
    return this.request('PATCH', `/admin/users/${userId}/tipe`, { tipe });
  }

  /**
   * DELETE /api/admin/users/{userId}
   * Hapus user (Admin)
   */
  async deleteAdminUser(userId) {
    return this.request('DELETE', `/admin/users/${userId}`);
  }

  /**
   * GET /api/admin/analytics/waste
   * Ambil waste analytics (Admin)
   */
  async getWasteAnalytics(dateFrom = null, dateTo = null, type = 'daily') {
    let url = '/admin/analytics/waste?type=' + type;
    if (dateFrom) url += `&date_from=${dateFrom}`;
    if (dateTo) url += `&date_to=${dateTo}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/admin/analytics/waste-by-user
   * Ambil waste by user analytics (Admin)
   */
  async getWasteByUserAnalytics() {
    return this.request('GET', '/admin/analytics/waste-by-user');
  }

  /**
   * GET /api/admin/analytics/points
   * Ambil point analytics (Admin)
   */
  async getPointsAnalytics() {
    return this.request('GET', '/admin/analytics/points');
  }

  /**
   * GET /api/admin/leaderboard
   * Ambil leaderboard (Admin)
   */
  async getAdminLeaderboard(limit = 100, type = 'points') {
    return this.request('GET', `/admin/leaderboard?limit=${limit}&type=${type}`);
  }

  /**
   * GET /api/admin/reports/list
   * List available reports (Admin)
   */
  async getReportsList() {
    return this.request('GET', '/admin/reports/list');
  }

  /**
   * POST /api/admin/reports/generate
   * Generate custom report (Admin)
   */
  async generateReport(type, dateFrom, dateTo) {
    return this.request('POST', '/admin/reports/generate', {
      type,
      date_from: dateFrom,
      date_to: dateTo
    });
  }

  /**
   * GET /api/admin/export
   * Export data (Admin)
   */
  async exportData(type = 'users', format = 'csv') {
    return this.request('GET', `/admin/export?type=${type}&format=${format}`);
  }

  // ========================================================================
  // 12. SUPERADMIN ROUTES
  // ========================================================================

  /**
   * GET /api/superadmin/admins
   * List semua admin (Superadmin)
   */
  async listAdmins(page = 1, perPage = 50) {
    return this.request('GET', `/superadmin/admins?page=${page}&per_page=${perPage}`);
  }

  /**
   * POST /api/superadmin/admins
   * Create admin (Superadmin)
   */
  async createAdmin(data) {
    return this.request('POST', '/superadmin/admins', data);
  }

  /**
   * GET /api/superadmin/admins/{adminId}
   * Ambil detail admin (Superadmin)
   */
  async getAdminById(adminId) {
    return this.request('GET', `/superadmin/admins/${adminId}`);
  }

  /**
   * PUT /api/superadmin/admins/{adminId}
   * Update admin (Superadmin)
   */
  async updateAdmin(adminId, data) {
    return this.request('PUT', `/superadmin/admins/${adminId}`, data);
  }

  /**
   * DELETE /api/superadmin/admins/{adminId}
   * Hapus admin (Superadmin)
   */
  async deleteAdmin(adminId) {
    return this.request('DELETE', `/superadmin/admins/${adminId}`);
  }

  /**
   * GET /api/superadmin/admins/{adminId}/activity
   * Ambil admin activity (Superadmin)
   */
  async getAdminActivity(adminId) {
    return this.request('GET', `/superadmin/admins/${adminId}/activity`);
  }

  /**
   * GET /api/superadmin/roles
   * List semua roles (Superadmin)
   */
  async listRoles() {
    return this.request('GET', '/superadmin/roles');
  }

  /**
   * POST /api/superadmin/roles
   * Create role (Superadmin)
   */
  async createRole(data) {
    return this.request('POST', '/superadmin/roles', data);
  }

  /**
   * GET /api/superadmin/roles/{roleId}
   * Ambil detail role (Superadmin)
   */
  async getRoleById(roleId) {
    return this.request('GET', `/superadmin/roles/${roleId}`);
  }

  /**
   * PUT /api/superadmin/roles/{roleId}
   * Update role (Superadmin)
   */
  async updateRole(roleId, data) {
    return this.request('PUT', `/superadmin/roles/${roleId}`, data);
  }

  /**
   * DELETE /api/superadmin/roles/{roleId}
   * Hapus role (Superadmin)
   */
  async deleteRole(roleId) {
    return this.request('DELETE', `/superadmin/roles/${roleId}`);
  }

  /**
   * GET /api/superadmin/roles/{roleId}/users
   * Ambil users dengan role tertentu (Superadmin)
   */
  async getRoleUsers(roleId) {
    return this.request('GET', `/superadmin/roles/${roleId}/users`);
  }

  /**
   * GET /api/superadmin/permissions
   * Ambil semua permissions (Superadmin)
   */
  async getAllPermissions() {
    return this.request('GET', '/superadmin/permissions');
  }

  /**
   * GET /api/superadmin/roles/{roleId}/permissions
   * Ambil permissions role (Superadmin)
   */
  async getRolePermissions(roleId) {
    return this.request('GET', `/superadmin/roles/${roleId}/permissions`);
  }

  /**
   * POST /api/superadmin/roles/{roleId}/permissions
   * Assign permission ke role (Superadmin)
   */
  async assignPermission(roleId, permissionCode) {
    return this.request('POST', `/superadmin/roles/${roleId}/permissions`, {
      permission_code: permissionCode
    });
  }

  /**
   * POST /api/superadmin/roles/{roleId}/permissions/bulk
   * Assign multiple permissions (Superadmin)
   */
  async assignPermissionsBulk(roleId, permissions) {
    return this.request('POST', `/superadmin/roles/${roleId}/permissions/bulk`, {
      permissions
    });
  }

  /**
   * DELETE /api/superadmin/roles/{roleId}/permissions/{permissionId}
   * Revoke permission dari role (Superadmin)
   */
  async revokePermission(roleId, permissionId) {
    return this.request('DELETE', `/superadmin/roles/${roleId}/permissions/${permissionId}`);
  }

  /**
   * GET /api/superadmin/audit-logs
   * List audit logs (Superadmin)
   */
  async listAuditLogs(page = 1, perPage = 100, filters = {}) {
    let url = `/superadmin/audit-logs?page=${page}&per_page=${perPage}`;
    if (filters.user_id) url += `&user_id=${filters.user_id}`;
    if (filters.action) url += `&action=${filters.action}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/superadmin/audit-logs/{logId}
   * Ambil detail audit log (Superadmin)
   */
  async getAuditLogById(logId) {
    return this.request('GET', `/superadmin/audit-logs/${logId}`);
  }

  /**
   * GET /api/superadmin/system-logs
   * Ambil system logs (Superadmin)
   */
  async getSystemLogs() {
    return this.request('GET', '/superadmin/system-logs');
  }

  /**
   * GET /api/superadmin/audit-logs/users/activity
   * Ambil user activity (Superadmin)
   */
  async getUserActivityFromAudit() {
    return this.request('GET', '/superadmin/audit-logs/users/activity');
  }

  /**
   * POST /api/superadmin/audit-logs/clear-old
   * Clear old audit logs (Superadmin)
   */
  async clearOldAuditLogs(days = 90) {
    return this.request('POST', '/superadmin/audit-logs/clear-old', { days });
  }

  /**
   * GET /api/superadmin/audit-logs/export
   * Export audit logs (Superadmin)
   */
  async exportAuditLogs(format = 'csv', dateFrom = null, dateTo = null) {
    let url = `/superadmin/audit-logs/export?format=${format}`;
    if (dateFrom) url += `&date_from=${dateFrom}`;
    if (dateTo) url += `&date_to=${dateTo}`;
    return this.request('GET', url);
  }

  /**
   * GET /api/superadmin/settings
   * Ambil semua settings (Superadmin)
   */
  async getSettings() {
    return this.request('GET', '/superadmin/settings');
  }

  /**
   * GET /api/superadmin/settings/{key}
   * Ambil specific setting (Superadmin)
   */
  async getSettingByKey(key) {
    return this.request('GET', `/superadmin/settings/${key}`);
  }

  /**
   * PUT /api/superadmin/settings/{key}
   * Update setting (Superadmin)
   */
  async updateSetting(key, value) {
    return this.request('PUT', `/superadmin/settings/${key}`, { value });
  }

  /**
   * GET /api/superadmin/health
   * Cek kesehatan sistem (Superadmin)
   */
  async getSystemHealth() {
    return this.request('GET', '/superadmin/health');
  }

  /**
   * GET /api/superadmin/cache-stats
   * Ambil cache stats (Superadmin)
   */
  async getCacheStats() {
    return this.request('GET', '/superadmin/cache-stats');
  }

  /**
   * POST /api/superadmin/cache/clear
   * Clear cache (Superadmin)
   */
  async clearCache() {
    return this.request('POST', '/superadmin/cache/clear', {});
  }

  /**
   * GET /api/superadmin/database-stats
   * Ambil database stats (Superadmin)
   */
  async getDatabaseStats() {
    return this.request('GET', '/superadmin/database-stats');
  }

  // ========================================================================
  // 13. UTILITY METHODS
  // ========================================================================

  /**
   * GET /api/user
   * Test API is working
   */
  async testAPI() {
    return this.request('GET', '/user');
  }
}

// ============================================================================
// EXPORT UNTUK DIGUNAKAN DI FRONTEND
// ============================================================================

export default MendaurAPI;

// ============================================================================
// CONTOH USAGE DI REACT
// ============================================================================

/**
 * STEP 1: Buat instance API client
 * 
 * const api = new MendaurAPI(process.env.REACT_APP_API_URL);
 * 
 * STEP 2: Login dan set token
 * 
 * const response = await api.login('user@example.com', 'password');
 * api.setToken(response.data.token);
 * 
 * STEP 3: Gunakan methods
 * 
 * const notifications = await api.getNotifications();
 * const activityLogs = await api.getAllActivityLogs();
 * const backups = await api.listBackups();
 * 
 * STEP 4: Setup .env.local
 * 
 * REACT_APP_API_URL=http://localhost:8000/api
 * // atau production
 * REACT_APP_API_URL=https://api.mendaur.com/api
 */

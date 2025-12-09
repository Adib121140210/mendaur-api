<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\TabungSampahController;
use App\Http\Controllers\JadwalPenyetoranController;
use App\Http\Controllers\JenisSampahController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\Api\BadgeProgressController;
use App\Http\Controllers\PenarikanTunaiController;
use App\Http\Controllers\Admin\AdminPenarikanTunaiController;
use App\Http\Controllers\PenukaranProdukController;
use App\Http\Controllers\KategoriSampahController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\AdminPointController;
use App\Http\Controllers\DashboardAdminController;

// ========================================
// PUBLIC ROUTES (No Authentication)
// ========================================

// Auth Routes
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register']);

// Jadwal Penyetoran (Public - for form)
Route::get('jadwal-penyetoran', [JadwalPenyetoranController::class, 'index']);
Route::get('jadwal-penyetoran/{id}', [JadwalPenyetoranController::class, 'show']);
Route::get('jadwal-penyetoran-aktif', [JadwalPenyetoranController::class, 'aktif']);

// Jenis Sampah (Public - for form dropdown)
Route::get('jenis-sampah', [JenisSampahController::class, 'index']);
Route::get('jenis-sampah/{id}', [JenisSampahController::class, 'show']);

// NEW: Hierarchical Kategori Sampah System
Route::get('kategori-sampah', [KategoriSampahController::class, 'index']); // Get all categories with their jenis
Route::get('kategori-sampah/{id}', [KategoriSampahController::class, 'show']); // Get specific category
Route::get('kategori-sampah/{id}/jenis', [KategoriSampahController::class, 'getJenisByKategori']); // Get jenis by category
Route::get('jenis-sampah-all', [KategoriSampahController::class, 'getAllJenisSampah']); // Get flat list of all jenis (for dropdowns)

// Produk (Public - for browsing)
Route::get('produk', [ProdukController::class, 'index']);
Route::get('produk/{id}', [ProdukController::class, 'show']);

// Artikel (Public - for reading)
Route::get('artikel', [ArtikelController::class, 'index']);
Route::get('artikel/{slug}', [ArtikelController::class, 'show']);

// ========================================
// PROTECTED ROUTES (Require Authentication)
// ========================================

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);

    // Tabung Sampah
    Route::apiResource('tabung-sampah', TabungSampahController::class);
    Route::get('users/{id}/tabung-sampah', [TabungSampahController::class, 'byUser']);

    // Transaksi
    Route::apiResource('transaksi', TransaksiController::class);
    Route::get('users/{id}/transaksi', [TransaksiController::class, 'byUser']);

    // Jadwal Management (Admin only - add middleware later)
    Route::post('jadwal-penyetoran', [JadwalPenyetoranController::class, 'store']);
    Route::put('jadwal-penyetoran/{id}', [JadwalPenyetoranController::class, 'update']);
    Route::delete('jadwal-penyetoran/{id}', [JadwalPenyetoranController::class, 'destroy']);

    // ========================================
    // KATEGORI & JENIS SAMPAH MANAGEMENT (Admin)
    // ========================================
    Route::post('kategori-sampah', [KategoriSampahController::class, 'store']);
    Route::put('kategori-sampah/{id}', [KategoriSampahController::class, 'update']);
    Route::delete('kategori-sampah/{id}', [KategoriSampahController::class, 'destroy']);

    Route::post('jenis-sampah', [JenisSampahController::class, 'store']);
    Route::put('jenis-sampah/{id}', [JenisSampahController::class, 'update']);
    Route::delete('jenis-sampah/{id}', [JenisSampahController::class, 'destroy']);

    // ========================================
    // CASH WITHDRAWAL ROUTES (User)
    // ========================================
    Route::get('penarikan-tunai', [PenarikanTunaiController::class, 'index']);
    Route::post('penarikan-tunai', [PenarikanTunaiController::class, 'store']);
    Route::get('penarikan-tunai/summary', [PenarikanTunaiController::class, 'summary']);
    Route::get('penarikan-tunai/{id}', [PenarikanTunaiController::class, 'show']);

    // ========================================
    // CASH WITHDRAWAL ROUTES (Admin)
    // ========================================
    // TODO: Add admin middleware check
    Route::prefix('admin')->group(function () {
        Route::get('penarikan-tunai', [AdminPenarikanTunaiController::class, 'index']);
        Route::post('penarikan-tunai/{id}/approve', [AdminPenarikanTunaiController::class, 'approve']);
        Route::post('penarikan-tunai/{id}/reject', [AdminPenarikanTunaiController::class, 'reject']);
        Route::get('penarikan-tunai/statistics', [AdminPenarikanTunaiController::class, 'statistics']);
    });

    // ========================================
    // PRODUCT REDEMPTION ROUTES (User)
    // ========================================
    Route::get('penukaran-produk', [PenukaranProdukController::class, 'index']);
    Route::post('penukaran-produk', [PenukaranProdukController::class, 'store']);
    Route::get('penukaran-produk/{id}', [PenukaranProdukController::class, 'show']);
    Route::put('penukaran-produk/{id}/cancel', [PenukaranProdukController::class, 'cancel']);
    Route::delete('penukaran-produk/{id}', [PenukaranProdukController::class, 'destroy']);

    // Legacy endpoint (backward compatibility)
    Route::get('tukar-produk', [PenukaranProdukController::class, 'index']);
    Route::get('tukar-produk/{id}', [PenukaranProdukController::class, 'show']);

    // ========================================
    // BADGE PROGRESS TRACKING ROUTES (New)
    // ========================================
    Route::get('user/badges/progress', [BadgeProgressController::class, 'getUserProgress']);
    Route::get('user/badges/completed', [BadgeProgressController::class, 'getCompletedBadges']);
    Route::get('badges/leaderboard', [BadgeProgressController::class, 'getLeaderboard']);
    Route::get('badges/available', [BadgeProgressController::class, 'getAvailableBadges']);

    // ========================================
    // POINT SYSTEM ROUTES (User)
    // ========================================
    Route::get('poin/history', [PointController::class, 'getHistory']);
    Route::get('poin/breakdown/{userId}', [PointController::class, 'getBreakdown']);
    Route::post('poin/bonus', [PointController::class, 'awardBonus']); // Admin only

    // ========================================
    // ADMIN BADGE ANALYTICS ROUTES
    // ========================================
    Route::middleware('admin')->get('admin/badges/analytics', [BadgeProgressController::class, 'getAnalytics']);

    // ========================================
    // ADMIN POINT DASHBOARD ROUTES
    // ========================================
    Route::middleware('admin')->prefix('poin/admin')->group(function () {
        Route::get('stats', [AdminPointController::class, 'getStats']);
        Route::get('history', [AdminPointController::class, 'getHistory']);
        Route::get('redemptions', [AdminPointController::class, 'getRedemptions']);
    });

    Route::middleware('admin')->get('poin/breakdown/all', [AdminPointController::class, 'getBreakdown']);

    // ========================================
    // ADMIN DASHBOARD ROUTES (New)
    // ========================================
    Route::middleware('admin')->prefix('admin/dashboard')->group(function () {
        Route::get('overview', [DashboardAdminController::class, 'getOverview']);
        Route::get('users', [DashboardAdminController::class, 'getUsers']);
        Route::get('waste-summary', [DashboardAdminController::class, 'getWasteSummary']);
        Route::get('point-summary', [DashboardAdminController::class, 'getPointSummary']);
        Route::get('waste-by-user', [DashboardAdminController::class, 'getWasteByUser']);
        Route::get('report', [DashboardAdminController::class, 'getReport']);
    });
});

// ========================================
// USER PROFILE ROUTES (Public for now, can be protected later)
// ========================================
Route::get('user/{id}/poin', [PointController::class, 'getUserPoints']);
// ========================================
// PROTECTED USER ROUTES (Auth Required)
// ========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/{id}/redeem-history', [PointController::class, 'getRedeemHistory']);
    Route::get('user/{id}/poin/statistics', [PointController::class, 'getStatistics']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::post('users/{id}/update-photo', [UserController::class, 'updatePhoto']);
    Route::get('users/{id}/tabung-sampah', [UserController::class, 'tabungSampahHistory']);
    Route::get('users/{id}/badges', [UserController::class, 'badges']);
    // New: detailed badge list with user-specific progress and filters
    Route::get('users/{userId}/badges-list', [BadgeController::class, 'getUserBadges']);
    Route::get('users/badges', [BadgeController::class, 'getUserBadges']); // current authenticated user
    Route::get('users/{id}/aktivitas', [UserController::class, 'aktivitas']);
});

// ========================================
// DASHBOARD ROUTES (Public for now, can be protected later)
// ========================================
Route::get('dashboard/stats/{userId}', [DashboardController::class, 'getUserStats']);
Route::get('dashboard/leaderboard', [DashboardController::class, 'getLeaderboard']);
Route::get('dashboard/global-stats', [DashboardController::class, 'getGlobalStats']);

// ========================================
// BADGE & REWARD ROUTES
// ========================================
Route::get('badges', [BadgeController::class, 'index']);
Route::get('users/{userId}/badge-progress', [BadgeController::class, 'getUserProgress']);
Route::post('users/{userId}/check-badges', [BadgeController::class, 'checkBadges']);

// Badge-related actions on waste deposits
Route::post('tabung-sampah/{id}/approve', [TabungSampahController::class, 'approve']);
Route::post('tabung-sampah/{id}/reject', [TabungSampahController::class, 'reject']);

// ========================================
// TEST ROUTE
// ========================================

Route::get('/user', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!',
        'data' => 'good'
    ], 200);
});


<?php

namespace App\Services;

use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\PoinTransaksi;

/**
 * Service untuk mengontrol akses fitur berdasarkan tipe nasabah
 * dan mengelola poin untuk sistem dual-nasabah (konvensional vs modern)
 */
class DualNasabahFeatureAccessService
{
    /**
     * Check if user can access penarikan tunai (withdrawal) feature
     *
     * Modern nasabah CANNOT use this feature
     * Konvensional nasabah CAN use if they have poin and permission
     */
    public function canAccessWithdrawal(User $user): array
    {
        $result = [
            'allowed' => false,
            'reason' => '',
            'code' => 'UNAUTHORIZED',
        ];

        // Check if user is modern nasabah
        if ($user->isNasabahModern()) {
            $result['reason'] = 'Modern nasabah tidak dapat menggunakan fitur penarikan tunai';
            $result['code'] = 'MODERN_NASABAH_BLOCKED';
            return $result;
        }

        // Check if user has permission
        if (!$user->hasPermission('request_withdrawal')) {
            $result['reason'] = 'Anda tidak memiliki izin untuk fitur ini';
            $result['code'] = 'NO_PERMISSION';
            return $result;
        }

        // Check if user has poin
        if ($user->total_poin <= 0) {
            $result['reason'] = 'Saldo poin Anda tidak cukup';
            $result['code'] = 'INSUFFICIENT_POIN';
            return $result;
        }

        // Check if user has banking info
        if (empty($user->nama_bank) || empty($user->nomor_rekening)) {
            $result['reason'] = 'Data rekening bank belum lengkap';
            $result['code'] = 'INCOMPLETE_BANKING_INFO';
            return $result;
        }

        $result['allowed'] = true;
        $result['code'] = 'OK';
        return $result;
    }

    /**
     * Check if user can access penukaran produk (product redemption) feature
     *
     * Modern nasabah CANNOT use this feature
     * Konvensional nasabah CAN use if they have poin and permission
     */
    public function canAccessRedemption(User $user, int $poinRequired = 0): array
    {
        $result = [
            'allowed' => false,
            'reason' => '',
            'code' => 'UNAUTHORIZED',
        ];

        // Check if user is modern nasabah
        if ($user->isNasabahModern()) {
            $result['reason'] = 'Modern nasabah tidak dapat menukar poin dengan produk';
            $result['code'] = 'MODERN_NASABAH_BLOCKED';
            return $result;
        }

        // Check if user has permission
        if (!$user->hasPermission('redeem_poin')) {
            $result['reason'] = 'Anda tidak memiliki izin untuk fitur ini';
            $result['code'] = 'NO_PERMISSION';
            return $result;
        }

        // Check if user has enough poin
        if ($user->total_poin < $poinRequired) {
            $result['reason'] = "Poin Anda tidak cukup (diperlukan: {$poinRequired}, poin Anda: {$user->total_poin})";
            $result['code'] = 'INSUFFICIENT_POIN';
            return $result;
        }

        $result['allowed'] = true;
        $result['code'] = 'OK';
        return $result;
    }

    /**
     * Check if user can deposit sampah
     * Both types can deposit, but poin handling will differ
     */
    public function canAccessDeposit(User $user): array
    {
        $result = [
            'allowed' => false,
            'reason' => '',
            'code' => 'UNAUTHORIZED',
        ];

        // Check if user has permission
        if (!$user->hasPermission('deposit_sampah')) {
            $result['reason'] = 'Anda tidak memiliki izin untuk fitur ini';
            $result['code'] = 'NO_PERMISSION';
            return $result;
        }

        $result['allowed'] = true;
        $result['code'] = 'OK';
        return $result;
    }

    /**
     * Add poin untuk nasabah setelah deposit sampah
     *
     * - Konvensional: tambah ke total_poin (usable) DAN poin_tercatat (audit)
     * - Modern: hanya tambah ke poin_tercatat (recorded only, not usable)
     */
    public function addPoinForDeposit(
        User $user,
        int $poin,
        int $tabungSampahId,
        string $jenisSampah = '',
        string $sumber = 'tabung_sampah'
    ): void {
        // Always add to poin_tercatat for audit trail
        $user->addPoinTercatat($poin, "Deposit sampah dari $sumber");

        // Add to total_poin only for konvensional nasabah
        if ($user->isNasabahKonvensional()) {
            $user->increment('total_poin', $poin);
        }

        $user->save();

        // Create poin transaction log
        PoinTransaksi::create([
            'user_id' => $user->id,
            'tabung_sampah_id' => $tabungSampahId,
            'jenis_sampah' => $jenisSampah,
            'berat_kg' => 0, // Will be updated by actual deposit
            'poin_didapat' => $poin,
            'sumber' => $sumber,
            'keterangan' => 'Poin dari penyetoran sampah',
            'referensi_tipe' => 'TabungSampah',
            'referensi_id' => $tabungSampahId,
            'is_usable' => $user->isNasabahKonvensional(),
            'reason_not_usable' => $user->isNasabahModern() ? 'modern_nasabah_type' : null,
        ]);
    }

    /**
     * Deduct poin untuk penukaran produk (redemption)
     *
     * Hanya untuk konvensional nasabah (modern nasabah tidak bisa sampai sini)
     */
    public function deductPoinForRedemption(
        User $user,
        int $poin,
        int $penukaranProdukId,
        string $reason = ''
    ): void {
        // Only konvensional nasabah can reach here due to access control
        if (!$user->isNasabahKonvensional()) {
            throw new \Exception('Modern nasabah cannot redeem poin');
        }

        // Deduct from total_poin
        if ($user->total_poin >= $poin) {
            $user->decrement('total_poin', $poin);
        }

        // Log to poin_transaksis
        PoinTransaksi::create([
            'user_id' => $user->id,
            'poin_didapat' => -$poin, // negative for deduction
            'sumber' => 'penukaran_produk',
            'keterangan' => "Penukaran produk: {$reason}",
            'referensi_tipe' => 'PenukaranProduk',
            'referensi_id' => $penukaranProdukId,
            'is_usable' => true,
            'reason_not_usable' => null,
        ]);

        $user->save();
    }

    /**
     * Deduct poin untuk penarikan tunai (withdrawal)
     *
     * Hanya untuk konvensional nasabah
     */
    public function deductPoinForWithdrawal(
        User $user,
        int $poin,
        int $penarikanTunaiId,
        string $reason = ''
    ): void {
        // Only konvensional nasabah can reach here
        if (!$user->isNasabahKonvensional()) {
            throw new \Exception('Modern nasabah cannot withdraw poin');
        }

        // Deduct from total_poin
        if ($user->total_poin >= $poin) {
            $user->decrement('total_poin', $poin);
        }

        // Log to poin_transaksis
        PoinTransaksi::create([
            'user_id' => $user->id,
            'poin_didapat' => -$poin, // negative for deduction
            'sumber' => 'penarikan_tunai',
            'keterangan' => "Penarikan tunai: {$reason}",
            'referensi_tipe' => 'PenarikanTunai',
            'referensi_id' => $penarikanTunaiId,
            'is_usable' => true,
            'reason_not_usable' => null,
        ]);

        $user->save();
    }

    /**
     * Get poin display info for user
     * Returns different values based on nasabah type
     */
    public function getPoinDisplay(User $user): array
    {
        return [
            'tipe_nasabah' => $user->tipe_nasabah,
            'poin_tercatat' => $user->poin_tercatat,
            'poin_usable' => $user->isNasabahKonvensional() ? $user->total_poin : 0,
            'total_poin' => $user->total_poin,
            'display_poin' => $user->getDisplayedPoin(),
            'message' => $user->isNasabahModern()
                ? 'Poin Anda tercatat untuk badge dan leaderboard, tetapi tidak dapat digunakan untuk penarikan atau penukaran produk'
                : 'Poin Anda dapat digunakan untuk penarikan tunai dan penukaran produk'
        ];
    }

    /**
     * Log aktivitas untuk dual-nasabah tracking
     */
    public function logActivity(
        User $user,
        string $tipeAktivitas,
        string $deskripsi,
        int $poinChange = 0,
        string $sourceTipe = ''
    ): void {
        LogAktivitas::create([
            'user_id' => $user->id,
            'tipe_aktivitas' => $tipeAktivitas,
            'deskripsi' => $deskripsi,
            'poin_perubahan' => $poinChange,
            'poin_tercatat' => $user->poin_tercatat,
            'poin_usable' => $user->isNasabahKonvensional() ? $user->total_poin : 0,
            'source_tipe' => $sourceTipe,
            'tanggal' => now(),
        ]);
    }

    /**
     * Summary data untuk dashboard nasabah
     */
    public function getNasabahSummary(User $user): array
    {
        $badges = $user->userBadges()->with('badge')->get();

        return [
            'user_id' => $user->id,
            'nama' => $user->nama,
            'tipe_nasabah' => $user->tipe_nasabah,
            'poin_tercatat' => $user->poin_tercatat,
            'poin_usable' => $user->isNasabahKonvensional() ? $user->total_poin : 0,
            'total_poin' => $user->total_poin,
            'badges_count' => $badges->count(),
            'deposits_count' => $user->tabungSampahs()->count(),
            'feature_access' => [
                'can_withdraw' => $this->canAccessWithdrawal($user)['allowed'],
                'can_redeem' => $this->canAccessRedemption($user)['allowed'],
                'can_deposit' => $this->canAccessDeposit($user)['allowed'],
            ],
            'message_type' => $user->isNasabahModern() ? 'modern' : 'konvensional',
        ];
    }
}

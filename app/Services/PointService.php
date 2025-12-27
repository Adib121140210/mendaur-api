<?php

namespace App\Services;

use App\Models\PoinTransaksi;
use App\Models\TabungSampah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointService
{
    /**
     * Point values per kg for each waste type
     * These can be moved to database as configuration if needed
     */
    const POINT_VALUES = [
        'Kertas' => 5,
        'Plastik' => 10,
        'Logam' => 15,
        'Kaca' => 8,
        'Organik' => 3,
        'Tekstil' => 8,
        'Elektronik' => 20,
        'Lainnya' => 5,
    ];

    /**
     * Bonus configuration
     */
    const BONUS_CONFIG = [
        'first_deposit' => 50,           // First deposit ever
        'fifth_deposit' => 25,           // Every 5th deposit
        'tenth_deposit' => 40,           // Every 10th deposit
        'large_deposit_10kg' => 30,      // Deposit >= 10kg
        'large_deposit_20kg' => 50,      // Deposit >= 20kg
    ];

    /**
     * Record a point transaction in the point ledger
     * This is the core method that all point changes go through
     *
     * @param int $userId
     * @param int $points (can be negative for deductions)
     * @param string $sumber Type of point source
     * @param string $keterangan Description
     * @param TabungSampah|null $tabungSampah Related waste deposit
     * @param int|null $referensiId Reference to related entity
     * @param string|null $referensiTipe Type of reference
     * @return PoinTransaksi
     * @throws \Exception
     */
    public static function recordPointTransaction(
        $userId,
        $points,
        $sumber = 'setor_sampah',
        $keterangan = '',
        $tabungSampah = null,
        $referensiId = null,
        $referensiTipe = null
    ): PoinTransaksi {
        return DB::transaction(function() use (
            $userId, $points, $sumber, $keterangan,
            $tabungSampah, $referensiId, $referensiTipe
        ) {
            try {
                // Create transaction record
                $transaction = PoinTransaksi::create([
                    'user_id' => $userId,
                    'tabung_sampah_id' => $tabungSampah?->id,
                    'jenis_sampah' => $tabungSampah?->jenis_sampah,
                    'berat_kg' => $tabungSampah?->berat_kg,
                    'poin_didapat' => $points,
                    'sumber' => $sumber,
                    'keterangan' => $keterangan,
                    'referensi_id' => $referensiId,
                    'referensi_tipe' => $referensiTipe,
                ]);

                // Update user total points
                $user = User::findOrFail($userId);
                $oldTotal = $user->actual_poin;
                $user->increment('actual_poin', $points);
                $newTotal = $user->actual_poin;

                // Log for debugging
                Log::info('Point transaction recorded', [
                    'user_id' => $userId,
                    'points' => $points,
                    'sumber' => $sumber,
                    'old_total' => $oldTotal,
                    'new_total' => $newTotal,
                    'transaction_id' => $transaction->id,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                Log::error('Failed to record point transaction', [
                    'user_id' => $userId,
                    'points' => $points,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Calculate points for a waste deposit including bonuses
     *
     * @param TabungSampah $tabungSampah
     * @return array ['base' => int, 'bonuses' => array, 'total' => int]
     */
    public static function calculatePointsForDeposit(TabungSampah $tabungSampah): array
    {
        $jenis = $tabungSampah->jenis_sampah;
        $berat = $tabungSampah->berat_kg ?? 1;
        $userId = $tabungSampah->user_id;

        // Step 1: Base points calculation
        $basePerKg = self::POINT_VALUES[$jenis] ?? 5;
        $basePoin = (int)($basePerKg * $berat);

        // Step 2: Calculate eligible bonuses
        $bonuses = [];
        $totalBonus = 0;

        // Bonus: First approved deposit
        $approvedDeposits = TabungSampah::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        if ($approvedDeposits === 0) {
            $bonuses['first_deposit'] = self::BONUS_CONFIG['first_deposit'];
            $totalBonus += self::BONUS_CONFIG['first_deposit'];
        }

        // Bonus: Every 5th deposit
        if (($approvedDeposits + 1) % 5 === 0) {
            $bonuses['fifth_deposit'] = self::BONUS_CONFIG['fifth_deposit'];
            $totalBonus += self::BONUS_CONFIG['fifth_deposit'];
        }

        // Bonus: Every 10th deposit
        if (($approvedDeposits + 1) % 10 === 0) {
            $bonuses['tenth_deposit'] = self::BONUS_CONFIG['tenth_deposit'];
            $totalBonus += self::BONUS_CONFIG['tenth_deposit'];
        }

        // Bonus: Large deposits
        if ($berat >= 20) {
            $bonuses['large_deposit_20kg'] = self::BONUS_CONFIG['large_deposit_20kg'];
            $totalBonus += self::BONUS_CONFIG['large_deposit_20kg'];
        } elseif ($berat >= 10) {
            $bonuses['large_deposit_10kg'] = self::BONUS_CONFIG['large_deposit_10kg'];
            $totalBonus += self::BONUS_CONFIG['large_deposit_10kg'];
        }

        return [
            'base' => $basePoin,
            'bonuses' => $bonuses,
            'total' => $basePoin + $totalBonus,
            'breakdown' => [
                'base_calculation' => "{$basePerKg} poin/kg × {$berat}kg = {$basePoin} poin",
                'bonus_details' => self::formatBonusDetails($bonuses),
            ],
        ];
    }

    /**
     * Format bonus details for human-readable output
     *
     * @param array $bonuses
     * @return string
     */
    private static function formatBonusDetails(array $bonuses): string
    {
        if (empty($bonuses)) {
            return 'Tidak ada bonus';
        }

        $details = [];
        $labels = [
            'first_deposit' => 'Bonus penyetoran pertama',
            'fifth_deposit' => 'Bonus setiap 5 penyetoran',
            'tenth_deposit' => 'Bonus setiap 10 penyetoran',
            'large_deposit_10kg' => 'Bonus penyetoran besar (10kg+)',
            'large_deposit_20kg' => 'Bonus penyetoran besar (20kg+)',
        ];

        foreach ($bonuses as $type => $points) {
            $label = $labels[$type] ?? $type;
            $details[] = "{$label}: +{$points}";
        }

        return implode(', ', $details);
    }

    /**
     * Apply points when a deposit is approved
     * This combines base points + bonus calculation + recording
     *
     * @param TabungSampah $tabungSampah
     * @return PoinTransaksi
     * @throws \Exception
     */
    public static function applyDepositPoints(TabungSampah $tabungSampah): PoinTransaksi
    {
        $calculation = self::calculatePointsForDeposit($tabungSampah);

        $keterangan = "Setor {$tabungSampah->berat_kg}kg {$tabungSampah->jenis_sampah}";
        if (!empty($calculation['bonuses'])) {
            $keterangan .= " + " . $calculation['breakdown']['bonus_details'];
        }

        return self::recordPointTransaction(
            $tabungSampah->user_id,
            $calculation['total'],
            'setor_sampah',
            $keterangan,
            $tabungSampah,
            $tabungSampah->id,
            'tabung_sampah'
        );
    }

    /**
     * Deduct points for product redemption
     *
     * @param User $user
     * @param int $poinDigunakan
     * @param int|null $penukaranId Reference to penukaran_produk record
     * @return PoinTransaksi
     * @throws \Exception
     */
    public static function deductPointsForRedemption(
        User $user,
        int $poinDigunakan,
        ?int $penukaranId = null
    ): PoinTransaksi {
        // Validate sufficient points
        if ($user->actual_poin < $poinDigunakan) {
            throw new \Exception("Poin tidak cukup. Anda memiliki {$user->actual_poin} poin tetapi membutuhkan {$poinDigunakan} poin.");
        }

        return self::recordPointTransaction(
            $user->id,
            -$poinDigunakan,  // Negative for deduction
            'redemption',
            "Penukaran produk: -{$poinDigunakan} poin",
            null,
            $penukaranId,
            'penukaran_produk'
        );
    }

    /**
     * Award bonus points (for events, admin, badges, etc)
     *
     * @param int $userId
     * @param int $points
     * @param string $reason (e.g., 'badge_unlock', 'event', 'manual')
     * @param string $description
     * @param int|null $referensiId
     * @param string|null $referensiTipe
     * @return PoinTransaksi
     * @throws \Exception
     */
    public static function awardBonusPoints(
        $userId,
        int $points,
        string $reason = 'bonus',
        string $description = '',
        ?int $referensiId = null,
        ?string $referensiTipe = null
    ): PoinTransaksi {
        $sumber = in_array($reason, ['badge_unlock', 'event', 'manual']) ? $reason : 'bonus';

        return self::recordPointTransaction(
            $userId,
            $points,
            $sumber,
            $description ?: "Bonus poin: +{$points}",
            null,
            $referensiId,
            $referensiTipe
        );
    }

    /**
     * Refund points (e.g., when redemption is rejected)
     *
     * @param int $userId
     * @param int $points
     * @param string $reason
     * @param int|null $referensiId
     * @param string|null $referensiTipe
     * @return PoinTransaksi
     * @throws \Exception
     */
    public static function refundPoints(
        $userId,
        int $points,
        string $reason = 'manual',
        ?int $referensiId = null,
        ?string $referensiTipe = null
    ): PoinTransaksi {
        return self::recordPointTransaction(
            $userId,
            $points,
            'manual',
            "Pengembalian poin: +{$points} ({$reason})",
            null,
            $referensiId,
            $referensiTipe
        );
    }

    /**
     * Get user's point transaction history
     *
     * @param int $userId
     * @param int $limit
     * @param array|null $sumberFilter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTransactionHistory(
        int $userId,
        int $limit = 20,
        ?array $sumberFilter = null
    ) {
        $query = PoinTransaksi::where('user_id', $userId);

        if ($sumberFilter) {
            $query->whereIn('sumber', $sumberFilter);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's total earned points
     *
     * @param int $userId
     * @return int
     */
    public static function getTotalEarned(int $userId): int
    {
        return (int) PoinTransaksi::where('user_id', $userId)
            ->where('poin_didapat', '>', 0)
            ->sum('poin_didapat');
    }

    /**
     * Get user's total spent points
     *
     * @param int $userId
     * @return int
     */
    public static function getTotalSpent(int $userId): int
    {
        return (int) abs(
            PoinTransaksi::where('user_id', $userId)
                ->where('poin_didapat', '<', 0)
                ->sum('poin_didapat')
        );
    }

    /**
     * Get summary statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public static function getStatistics(int $userId): array
    {
        $user = User::findOrFail($userId);
        $earned = self::getTotalEarned($userId);
        $spent = self::getTotalSpent($userId);

        return [
            'current_balance' => $user->actual_poin,
            'total_earned' => $earned,
            'total_spent' => $spent,
            'transaction_count' => PoinTransaksi::where('user_id', $userId)->count(),
            'breakdown' => [
                'from_deposits' => (int) PoinTransaksi::where('user_id', $userId)
                    ->where('sumber', 'setor_sampah')
                    ->sum('poin_didapat'),
                'from_bonuses' => (int) PoinTransaksi::where('user_id', $userId)
                    ->where('sumber', 'bonus')
                    ->sum('poin_didapat'),
                'from_badges' => (int) PoinTransaksi::where('user_id', $userId)
                    ->where('sumber', 'badge')
                    ->sum('poin_didapat'),
                'from_events' => (int) PoinTransaksi::where('user_id', $userId)
                    ->where('sumber', 'event')
                    ->sum('poin_didapat'),
                'spent_on_redemptions' => (int) abs(
                    PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'redemption')
                        ->sum('poin_didapat')
                ),
            ],
        ];
    }
}

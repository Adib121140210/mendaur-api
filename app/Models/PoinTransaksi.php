<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PoinTransaksi extends Model
{
    use HasFactory;

    protected $table = 'poin_transaksis';
    protected $primaryKey = 'poin_transaksi_id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'tabung_sampah_id',
        'jenis_sampah',
        'berat_kg',
        'poin_didapat',
        'sumber',
        'keterangan',
        'referensi_id',
        'referensi_tipe',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'berat_kg' => 'decimal:2',
        'poin_didapat' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    /**
     * Get the user that owns this transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the waste deposit associated with this transaction
     */
    public function tabungSampah()
    {
        return $this->belongsTo(TabungSampah::class);
    }

    /**
     * Query Scopes
     */

    /**
     * Filter transactions by source type
     */
    public function scopeBySumber($query, $sumber)
    {
        return $query->where('sumber', $sumber);
    }

    /**
     * Filter deposit-related transactions
     */
    public function scopeDeposits($query)
    {
        return $query->where('sumber', 'setor_sampah');
    }

    /**
     * Filter bonus transactions
     */
    public function scopeBonuses($query)
    {
        return $query->where('sumber', 'bonus');
    }

    /**
     * Filter badge reward transactions
     */
    public function scopeBadgeRewards($query)
    {
        return $query->where('sumber', 'badge');
    }

    /**
     * Filter redemption transactions (point deductions)
     */
    public function scopeRedemptions($query)
    {
        return $query->where('sumber', 'redemption');
    }

    /**
     * Filter admin-issued transactions
     */
    public function scopeManual($query)
    {
        return $query->where('sumber', 'manual');
    }

    /**
     * Get only positive point transactions
     */
    public function scopePositive($query)
    {
        return $query->where('poin_didapat', '>', 0);
    }

    /**
     * Get only negative point transactions
     */
    public function scopeNegative($query)
    {
        return $query->where('poin_didapat', '<', 0);
    }

    /**
     * Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('Y-m-d');
    }

    /**
     * Get formatted date time
     */
    public function getFormattedDateTimeAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Helper Methods
     */

    /**
     * Check if transaction is a point gain
     */
    public function isGain(): bool
    {
        return $this->poin_didapat > 0;
    }

    /**
     * Check if transaction is a point deduction
     */
    public function isDeduction(): bool
    {
        return $this->poin_didapat < 0;
    }

    /**
     * Get human-readable source label
     */
    public function getSourceLabel(): string
    {
        $labels = [
            'setor_sampah' => 'Penyetoran Sampah',
            'bonus' => 'Bonus Poin',
            'event' => 'Event Spesial',
            'manual' => 'Poin Manual',
            'badge' => 'Badge Reward',
            'redemption' => 'Penukaran Produk',
        ];

        return $labels[$this->sumber] ?? $this->sumber;
    }
};

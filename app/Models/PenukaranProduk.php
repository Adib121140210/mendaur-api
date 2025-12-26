<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenukaranProduk extends Model
{
    use HasFactory;

    protected $table = 'penukaran_produk';
    protected $primaryKey = 'penukaran_produk_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'produk_id',
        'nama_produk',
        'poin_digunakan',
        'jumlah',
        'status',
        'metode_ambil',
        'catatan',
        'tanggal_penukaran',
        'tanggal_diambil',
    ];

    protected $casts = [
        'poin_digunakan' => 'integer',
        'jumlah' => 'integer',
        'tanggal_penukaran' => 'datetime',
        'tanggal_diambil' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    /**
     * Query Scopes - Business Logic
     */

    // Main scope: Orders waiting for approval
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Main scope: Approved orders (shipped + delivered)
    public function scopeApprove($query)
    {
        return $query->whereIn('status', ['approved']);
    }

    // Main scope: Cancelled orders
    public function scopeCanceled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Helper: Filter by any status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper: Filter by multiple statuses
    public function scopeStatuses($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    // Helper: Exclude one status
    public function scopeExcludeStatus($query, $status)
    {
        return $query->where('status', '!=', $status);
    }

    // Helper: Exclude multiple statuses
    public function scopeExcludeStatuses($query, array $statuses)
    {
        return $query->whereNotIn('status', $statuses);
    }
}

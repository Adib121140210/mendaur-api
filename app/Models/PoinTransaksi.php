<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model PoinTransaksi - Tabel poin_transaksis
 *
 * Mencatat semua transaksi poin (masuk dan keluar) - LEDGER POIN
 * Ini adalah sumber kebenaran untuk kalkulasi actual_poin
 */
class PoinTransaksi extends Model
{
    use HasFactory;

    protected $table = 'poin_transaksis';
    protected $primaryKey = 'poin_transaksi_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',          // ID user pemilik transaksi
        'tabung_sampah_id', // ID tabung sampah (jika dari setor sampah)
        'jenis_sampah',     // Jenis sampah (jika dari setor sampah)
        'berat_kg',         // Berat kg (jika dari setor sampah)
        'poin_didapat',     // Jumlah poin (+positif untuk masuk, -negatif untuk keluar)
        'sumber',           // Sumber transaksi (lihat konstanta di bawah)
        'keterangan',       // Deskripsi/catatan transaksi
        'referensi_id',     // ID entitas terkait (produk_id, penarikan_id, dll)
        'referensi_tipe',   // Tipe entitas terkait (PenukaranProduk, PenarikanTunai, dll)
    ];

    /**
     * Nilai sumber transaksi yang valid:
     *
     * POSITIF (menambah poin):
     * - 'setor_sampah'           : Poin dari setor sampah
     * - 'bonus'                  : Bonus event/promo
     * - 'badge_reward'           : Reward dari unlock badge
     * - 'event'                  : Event khusus
     * - 'manual'                 : Adjustment manual oleh admin
     * - 'refund_penukaran'       : Refund dari cancel penukaran produk
     * - 'pengembalian_penarikan' : Refund dari reject penarikan tunai
     *
     * NEGATIF (mengurangi poin):
     * - 'penukaran_produk'       : Penukaran/redeem produk
     * - 'penarikan_tunai'        : Penarikan tunai/withdrawal
     */

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'poin_didapat' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function tabungSampah()
    {
        return $this->belongsTo(TabungSampah::class, 'tabung_sampah_id', 'tabung_sampah_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeBySumber($query, $sumber)
    {
        return $query->where('sumber', $sumber);
    }

    public function scopeDeposits($query)
    {
        return $query->where('sumber', 'setor_sampah');
    }

    public function scopeBonuses($query)
    {
        return $query->where('sumber', 'bonus');
    }

    public function scopePositive($query)
    {
        return $query->where('poin_didapat', '>', 0);
    }

    public function scopeNegative($query)
    {
        return $query->where('poin_didapat', '<', 0);
    }
}

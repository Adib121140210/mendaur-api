<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kategori_transaksi';
    protected $primaryKey = 'kategori_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama',
        'slug',
    ];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'kategori_id');
    }
}

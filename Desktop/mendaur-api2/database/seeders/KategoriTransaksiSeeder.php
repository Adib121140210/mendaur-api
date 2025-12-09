<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kategori_transaksi')->insert([
            [
                'nama' => 'Penukaran Poin',
                'deskripsi' => 'Transaksi penukaran poin dengan produk',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Penyetoran Sampah',
                'deskripsi' => 'Transaksi penerimaan poin dari setor sampah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bonus Reward',
                'deskripsi' => 'Poin bonus dari program reward',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

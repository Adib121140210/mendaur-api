<?php

namespace Database\Seeders;

use App\Models\PoinTransaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PoinTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role_id', 1)->take(15)->get();

        if ($users->isEmpty()) {
            $this->command->info('⚠️  Users tidak ditemukan. Silakan jalankan seeder yang sesuai terlebih dahulu.');
            return;
        }

        $tipeTransaksi = [
            'penyetoran_sampah',
            'tukar_produk',
            'penarikan_tunai',
            'bonus_referral',
            'correction',
            'adjustment',
        ];

        $totalTransaksi = 0;

        foreach ($users as $user) {
            // Setiap user memiliki 10-20 transaksi poin
            for ($i = 0; $i < rand(10, 20); $i++) {
                $tipe = $tipeTransaksi[array_rand($tipeTransaksi)];
                $poinAmount = match ($tipe) {
                    'penyetoran_sampah' => rand(50, 500),
                    'tukar_produk' => -rand(100, 1000),
                    'penarikan_tunai' => -rand(200, 500),
                    'bonus_referral' => rand(100, 500),
                    'correction' => rand(-100, 100),
                    'adjustment' => rand(-50, 50),
                    default => rand(50, 200),
                };

                $tanggalTransaksi = Carbon::now()->subDays(rand(1, 60));

                PoinTransaksi::create([
                    'user_id' => $user->id,
                    'tipe_transaksi' => $tipe,
                    'poin_awal' => $user->poin ?? 0,
                    'poin_perubahan' => $poinAmount,
                    'poin_akhir' => ($user->poin ?? 0) + $poinAmount,
                    'keterangan' => $this->getKeterangan($tipe),
                    'referensi_id' => rand(1, 1000),
                    'referensi_tabel' => $this->getTabelReferensi($tipe),
                    'created_at' => $tanggalTransaksi,
                    'updated_at' => $tanggalTransaksi,
                ]);

                $totalTransaksi++;
            }
        }

        $this->command->info("✅ PoinTransaksi seeder berhasil dijalankan (Total: {$totalTransaksi} transaksi)");
    }

    private function getKeterangan(string $tipe): string
    {
        $keterangan = [
            'penyetoran_sampah' => 'Poin dari penyetoran sampah 5kg plastik',
            'tukar_produk' => 'Penggunaan poin untuk menukar produk',
            'penarikan_tunai' => 'Penggunaan poin untuk penarikan tunai',
            'bonus_referral' => 'Bonus poin dari program referral',
            'correction' => 'Koreksi poin oleh admin',
            'adjustment' => 'Penyesuaian poin sistem',
        ];

        return $keterangan[$tipe] ?? 'Transaksi poin';
    }

    private function getTabelReferensi(string $tipe): string
    {
        $tabel = [
            'penyetoran_sampah' => 'tabung_sampah',
            'tukar_produk' => 'penukaran_produk',
            'penarikan_tunai' => 'penarikan_tunai',
            'bonus_referral' => 'users',
            'correction' => 'poin_corrections',
            'adjustment' => 'log_user_activity',
        ];

        return $tabel[$tipe] ?? 'transaksi';
    }
}

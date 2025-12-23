<?php

namespace Database\Seeders;

use App\Models\PoinCorrection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PoinCorrectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nasabahUsers = User::where('role_id', 1)->take(10)->get();
        $adminUsers = User::where('role_id', '>=', 2)->first();

        if ($nasabahUsers->isEmpty() || !$adminUsers) {
            $this->command->info('⚠️  Users tidak ditemukan. Silakan jalankan seeder yang sesuai terlebih dahulu.');
            return;
        }

        $alasanKoreksi = [
            'Koreksi poin yang dihitung kurang',
            'Pemberian bonus poin sebagai kompensasi',
            'Penyesuaian karena data error',
            'Koreksi atas komplain user',
            'Pemberian insentif bulanan',
            'Poin backup dari backup deposit yang hilang',
            'Kesalahan sistem saat approval',
        ];

        $totalKoreksi = 0;

        foreach ($nasabahUsers as $user) {
            // Setiap user memiliki 0-3 koreksi poin
            for ($i = 0; $i < rand(0, 3); $i++) {
                $poinAwal = $user->poin ?? 0;
                $poinPerubahan = rand(-500, 500);
                $tanggalKoreksi = Carbon::now()->subDays(rand(1, 60));

                PoinCorrection::create([
                    'user_id' => $user->id,
                    'poin_awal' => $poinAwal,
                    'poin_perubahan' => $poinPerubahan,
                    'poin_akhir' => $poinAwal + $poinPerubahan,
                    'alasan' => $alasanKoreksi[array_rand($alasanKoreksi)],
                    'dikoreksi_oleh' => $adminUsers->id,
                    'catatan_admin' => rand(0, 1) ? $this->getRandomCatatan() : null,
                    'tanggal_koreksi' => $tanggalKoreksi,
                    'created_at' => $tanggalKoreksi,
                    'updated_at' => $tanggalKoreksi,
                ]);

                $totalKoreksi++;
            }
        }

        $this->command->info("✅ PoinCorrection seeder berhasil dijalankan (Total: {$totalKoreksi} koreksi)");
    }

    private function getRandomCatatan(): string
    {
        $catatan = [
            'Sudah dikonfirmasi dengan user',
            'Sesuai dengan laporan admin',
            'Koreksi retroaktif',
            'Pemberian bonus spesial',
            'Penggantian poin yang salah hitung',
        ];

        return $catatan[array_rand($catatan)];
    }
}

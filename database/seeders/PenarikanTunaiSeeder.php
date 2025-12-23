<?php

namespace Database\Seeders;

use App\Models\PenarikanTunai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PenarikanTunaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa users
        $users = User::where('role_id', 1)->take(10)->get();

        if ($users->isEmpty()) {
            $this->command->info('⚠️  Users tidak ditemukan. Silakan jalankan seeder yang sesuai terlebih dahulu.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected', 'completed'];
        $jumlahOptions = [50000, 100000, 150000, 200000, 250000, 500000];

        foreach ($users as $user) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $status = $statuses[array_rand($statuses)];
                $tanggalRequest = Carbon::now()->subDays(rand(1, 30));
                $tanggalAproval = in_array($status, ['approved', 'completed', 'rejected']) 
                    ? $tanggalRequest->clone()->addDays(rand(1, 5))
                    : null;

                PenarikanTunai::create([
                    'user_id' => $user->id,
                    'jumlah' => $jumlahOptions[array_rand($jumlahOptions)],
                    'status' => $status,
                    'alasan_penolakan' => $status === 'rejected' ? $this->getRandomAlasan() : null,
                    'catatan_admin' => rand(0, 1) ? $this->getRandomCatatan() : null,
                    'tanggal_request' => $tanggalRequest,
                    'tanggal_approval' => $tanggalAproval,
                    'tanggal_selesai' => $status === 'completed' ? $tanggalAproval->clone()->addDays(rand(1, 3)) : null,
                ]);
            }
        }

        $this->command->info('✅ PenarikanTunai seeder berhasil dijalankan');
    }

    private function getRandomAlasan(): string
    {
        $alasan = [
            'Saldo tidak mencukupi',
            'Verifikasi akun belum lengkap',
            'Jumlah penarikan terlalu kecil',
            'Frekuensi penarikan terlalu sering',
            'Rekening bank tidak valid',
        ];
        
        return $alasan[array_rand($alasan)];
    }

    private function getRandomCatatan(): string
    {
        $catatan = [
            'Tunai siap diambil di kantor cabang',
            'Transfer sudah dilakukan',
            'Menunggu konfirmasi bank',
            'Silakan hubungi admin untuk detail lebih lanjut',
            null,
        ];
        
        return $catatan[array_rand($catatan)];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data to avoid duplicates (disable FK checks first)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_badges')->truncate();
        DB::table('badges')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('badges')->insert([
            [
                'nama' => 'Pemula Peduli',
                'deskripsi' => 'Setor sampah pertama kali',
                'icon' => '🌱',
                'syarat_poin' => 0,
                'syarat_setor' => 1,
                'reward_poin' => 50,
                'tipe' => 'setor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Eco Warrior',
                'deskripsi' => 'Setor sampah 5 kali',
                'icon' => '♻️',
                'syarat_poin' => 0,
                'syarat_setor' => 5,
                'reward_poin' => 100,
                'tipe' => 'setor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Green Hero',
                'deskripsi' => 'Setor sampah 10 kali',
                'icon' => '🦸',
                'syarat_poin' => 0,
                'syarat_setor' => 10,
                'reward_poin' => 200,
                'tipe' => 'setor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Planet Saver',
                'deskripsi' => 'Setor sampah 25 kali',
                'icon' => '🌍',
                'syarat_poin' => 0,
                'syarat_setor' => 25,
                'reward_poin' => 500,
                'tipe' => 'setor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Bronze Collector',
                'deskripsi' => 'Mencapai 100 poin',
                'icon' => '🥉',
                'syarat_poin' => 100,
                'syarat_setor' => 0,
                'reward_poin' => 100,
                'tipe' => 'poin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Silver Collector',
                'deskripsi' => 'Mencapai 300 poin',
                'icon' => '🥈',
                'syarat_poin' => 300,
                'syarat_setor' => 0,
                'reward_poin' => 200,
                'tipe' => 'poin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Gold Collector',
                'deskripsi' => 'Mencapai 600 poin',
                'icon' => '🥇',
                'syarat_poin' => 600,
                'syarat_setor' => 0,
                'reward_poin' => 400,
                'tipe' => 'poin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Capai Ranking 10',
                'deskripsi' => 'Masuk 10 besar leaderboard',
                'icon' => '🏅',
                'syarat_poin' => 0,
                'syarat_setor' => 0,
                'reward_poin' => 150,
                'tipe' => 'ranking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Capai Ranking 5',
                'deskripsi' => 'Masuk 5 besar leaderboard',
                'icon' => '🥇',
                'syarat_poin' => 0,
                'syarat_setor' => 0,
                'reward_poin' => 300,
                'tipe' => 'ranking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Capai Ranking 1',
                'deskripsi' => 'Menjadi juara #1 leaderboard',
                'icon' => '👑',
                'syarat_poin' => 0,
                'syarat_setor' => 0,
                'reward_poin' => 500,
                'tipe' => 'ranking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Give badges to users based on their activity
        // User 1 (Adib) - 5 setor, 150 poin, Bronze
        DB::table('user_badges')->insert([
            [
                'user_id' => 1,
                'badge_id' => 1,
                'tanggal_dapat' => now()->subDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'badge_id' => 2,
                'tanggal_dapat' => now()->subDays(15),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'badge_id' => 5,
                'tanggal_dapat' => now()->subDays(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // User 2 (Siti) - 12 setor, 300 poin, Silver
        DB::table('user_badges')->insert([
            [
                'user_id' => 2,
                'badge_id' => 1,
                'tanggal_dapat' => now()->subDays(60),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'badge_id' => 2,
                'tanggal_dapat' => now()->subDays(50),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'badge_id' => 3,
                'tanggal_dapat' => now()->subDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'badge_id' => 5,
                'tanggal_dapat' => now()->subDays(35),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'badge_id' => 6,
                'tanggal_dapat' => now()->subDays(20),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // User 3 (Budi) - 2 setor, 50 poin, Pemula
        DB::table('user_badges')->insert([
            [
                'user_id' => 3,
                'badge_id' => 1,
                'tanggal_dapat' => now()->subDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

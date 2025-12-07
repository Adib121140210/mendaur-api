<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\BadgeProgressService;

class BadgeProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $progressService = new BadgeProgressService();

        echo "\n🔄 Initializing badge progress for all users...\n";

        // Initialize progress for all users
        $users = User::all();
        $totalUsers = $users->count();
        $current = 0;

        foreach ($users as $user) {
            $current++;
            echo "  [{$current}/{$totalUsers}] Processing {$user->nama}...\n";

            $progressService->syncUserBadgeProgress($user);
        }

        echo "\n✅ Badge progress initialized for all {$totalUsers} users!\n\n";
    }
}

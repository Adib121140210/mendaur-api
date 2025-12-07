<?php

namespace App\Console\Commands;

use App\Services\BadgeTrackingService;
use Illuminate\Console\Command;

class RecalculateBadgeProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badge:recalculate {--force : Force recalculation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate badge progress for all users';

    /**
     * Execute the console command.
     */
    public function handle(BadgeTrackingService $badgeService)
    {
        $this->info('Starting badge progress recalculation...');
        $this->newLine();

        try {
            $count = $badgeService->recalculateAllUserProgress();

            $this->info("✅ Badge progress recalculated for {$count} users");
            $this->newLine();
            $this->info('Completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\ForgotPasswordOTP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendOtpEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * The user instance.
     */
    protected User $user;

    /**
     * The OTP code.
     */
    protected string $otp;

    /**
     * The expiry time.
     */
    protected Carbon $expiresAt;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $otp, Carbon $expiresAt)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(
                new ForgotPasswordOTP($this->user, $this->otp, $this->expiresAt)
            );

            Log::info('OTP email sent successfully', [
                'email' => $this->user->email,
                'expires_at' => $this->expiresAt->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'email' => $this->user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('OTP email job failed after all retries', [
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
        ]);

        // Optional: Send notification to admin
        // Notification::route('slack', config('logging.channels.slack.url'))
        //     ->notify(new OtpEmailFailedNotification($this->user));
    }
}

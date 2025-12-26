<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;
    public $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otp, Carbon $expiresAt)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - Mendaur Bank Sampah',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-password-otp',
            with: [
                'userName' => $this->user->nama,
                'otp' => $this->otp,
                'expiresAt' => $this->expiresAt,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use App\Mail\ForgotPasswordOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Send OTP to user's email
     * POST /api/forgot-password
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;

            // Check if user exists and is active
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak ditemukan'
                ], 404);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak aktif. Silakan hubungi administrator.'
                ], 403);
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Delete any existing OTP for this email
            PasswordReset::where('email', $email)->delete();

            // Create new OTP record (expires in 15 minutes)
            PasswordReset::create([
                'email' => $email,
                'token' => Hash::make($otp),
                'otp' => $otp, // Store plain OTP for email (will be hashed in model)
                'expires_at' => Carbon::now()->addMinutes(15),
                'created_at' => Carbon::now(),
            ]);

            // Send OTP via email
            $this->sendOTPEmail($user, $otp);

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
                'data' => [
                    'email' => $email,
                    'expires_in' => 900 // 15 minutes in seconds
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP code
     * POST /api/verify-otp
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;
            $otp = $request->otp;

            // Find OTP record
            $resetRecord = PasswordReset::where('email', $email)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa'
                ], 400);
            }

            // Verify OTP
            if ($resetRecord->otp !== $otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid'
                ], 400);
            }

            // Generate reset token for password reset step
            $resetToken = Str::random(60);
            
            // Mark OTP as verified AND extend expiry time for password reset (30 more minutes)
            $newExpiresAt = Carbon::now()->addMinutes(30);
            $resetRecord->update([
                'verified_at' => Carbon::now(),
                'reset_token' => Hash::make($resetToken),
                'expires_at' => $newExpiresAt  // Extend expiry for password reset step
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP berhasil diverifikasi',
                'data' => [
                    'email' => $email,
                    'reset_token' => $resetToken,
                    'expires_in' => 1800 // 30 minutes in seconds
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi OTP. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password with verified OTP
     * POST /api/reset-password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;
            $resetToken = $request->reset_token;
            $password = $request->password;

            // Find verified OTP record
            $resetRecord = PasswordReset::where('email', $email)
                ->whereNotNull('verified_at')
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token reset tidak valid atau sudah kedaluwarsa'
                ], 400);
            }

            // Verify reset token
            if (!Hash::check($resetToken, $resetRecord->reset_token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token reset tidak valid'
                ], 400);
            }

            // Update user password
            $user = User::where('email', $email)->first();
            $user->update([
                'password' => Hash::make($password)
            ]);

            // Delete the reset record
            $resetRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset. Silakan login dengan password baru.',
                'data' => [
                    'email' => $email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset password. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send OTP email to user
     */
    private function sendOTPEmail($user, $otp)
    {
        try {
            $expiresAt = Carbon::now()->addMinutes(10);
            Mail::to($user->email)->send(new ForgotPasswordOTP($user, $otp, $expiresAt));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
        }
    }

    /**
     * Resend OTP (optional endpoint)
     * POST /api/resend-otp
     */
    public function resendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check rate limiting (max 3 requests per 5 minutes)
        $recentRequests = PasswordReset::where('email', $request->email)
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->count();

        if ($recentRequests >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan. Silakan tunggu 5 menit.'
            ], 429);
        }

        // Reuse the sendOTP logic
        return $this->sendOTP($request);
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private const OTP_EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    
    // Test user credentials for development
    private const TEST_USERS = [
        'admin' => [
            'phone' => '+9641234567890',
            'otp' => '123456',
        ],
        'provider' => [
            'phone' => '+9649876543210',
            'otp' => '123456',
        ],
        'customer' => [
            'phone' => '07700281899',
            'otp' => '123456',
        ],
    ];

    /**
     * Generate and send OTP code.
     */
    public function generateOtp(string $phone): array
    {
        // ========================================
        // FOR TESTING ONLY - COMMENT OUT IN PRODUCTION
        // ========================================
        $code = '123456'; // Simple test OTP
        // ========================================
        
        // PRODUCTION CODE - UNCOMMENT IN PRODUCTION
        // Check if it's a test user
        // $testOtp = $this->getTestOtp($phone);
        // Generate a 6-digit code
        // $code = $testOtp ?: str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        // ========================================
        
        $expiresAt = Carbon::now()->addMinutes((int) self::OTP_EXPIRY_MINUTES);
        
        // Store OTP in cache with expiry
        $cacheKey = $this->getOtpCacheKey($phone);
        Cache::put($cacheKey, [
            'code' => Hash::make($code),
            'expires_at' => $expiresAt->timestamp,
            'attempts' => 0,
        ], (int) (self::OTP_EXPIRY_MINUTES * 60)); // seconds

        // TODO: Send SMS with the code
        // For development, log it
        Log::info("OTP Code for {$phone}: {$code}");

        return [
            'expires_at' => $expiresAt,
            'otp' => $code, // Return OTP for development (remove in production)
        ];
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(string $phone, string $code): bool
    {
        $cacheKey = $this->getOtpCacheKey($phone);
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            Log::warning("OTP verification failed: No OTP data found for {$phone}");
            return false;
        }

        // Check if expired
        if (Carbon::now()->timestamp > $otpData['expires_at']) {
            Log::warning("OTP verification failed: OTP expired for {$phone}");
            Cache::forget($cacheKey);
            return false;
        }

        // Check attempts limit
        if ($otpData['attempts'] >= self::MAX_ATTEMPTS) {
            Log::warning("OTP verification failed: Max attempts exceeded for {$phone}");
            Cache::forget($cacheKey);
            return false;
        }

        // Increment attempts
        $otpData['attempts']++;
        Cache::put($cacheKey, $otpData, (int) (self::OTP_EXPIRY_MINUTES * 60));

        // Verify code
        if (!Hash::check($code, $otpData['code'])) {
            Log::warning("OTP verification failed: Invalid code for {$phone}. Attempts: {$otpData['attempts']}");
            return false;
        }

        // Valid OTP - delete from cache
        Cache::forget($cacheKey);
        Log::info("OTP verified successfully for {$phone}");

        return true;
    }

    /**
     * Get cache key for OTP.
     */
    private function getOtpCacheKey(string $phone): string
    {
        return 'otp:' . $phone;
    }

    /**
     * Get test OTP for test users.
     */
    private function getTestOtp(string $phone): ?string
    {
        foreach (self::TEST_USERS as $user) {
            if ($user['phone'] === $phone) {
                return $user['otp'];
            }
        }
        
        return null;
    }

    /**
     * Get or create user by phone.
     */
    public function getOrCreateUser(string $phone, ?string $name = null): User
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $user = User::create([
                'phone' => $phone,
                'name' => $name ?? 'User',
                'email' => $phone . '@booking.app', // Temporary email
                'password' => Hash::make(uniqid()), // Random password
            ]);
        }

        return $user;
    }
}

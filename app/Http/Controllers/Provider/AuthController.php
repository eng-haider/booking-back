<?php

namespace App\Http\Controllers\Provider;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Provider\RegisterProviderRequest;
use App\Models\Provider;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Register a new provider
     */
    public function register(RegisterProviderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create user account
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'role' => UserRole::OWNER,
                'password' => Hash::make(Str::random(32)), // Random password, not used with OTP
            ]);

            // Assign provider role with api guard
            $user->assignRole('owner');

            // Create provider profile
            $provider = Provider::create([
                'user_id' => $user->id,
                'governorate_id' => $request->input('governorate_id'),
                'name' => $request->input('provider_name'),
                'slug' => Str::slug($request->input('provider_name')),
                'description' => $request->input('description'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'lat' => $request->input('lat'),
                'lng' => $request->input('lng'),
                'license_number' => $request->input('license_number'),
                'status' => 'pending', // Requires admin approval
            ]);

            // Send OTP for verification
            $otpData = $this->otpService->generateOtp($user->phone);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Provider registered successfully. Please verify your phone with the OTP sent.',
                'data' => [
                    'user' => $user,
                    'owner' => $provider,
                    'phone' => $user->phone,
                    'expires_at' => $otpData['expires_at'],
                    // Remove this in production
                    'otp' => $otpData['otp'],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Send OTP to provider's phone number
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $phone = $request->input('phone');

        // Check if user exists and is owner/provider
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->role !== UserRole::OWNER) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Provider access only.',
            ], 403);
        }

        $otpData = $this->otpService->generateOtp($phone);

        // TODO: Send OTP via SMS service
        // For development, return OTP in response (remove in production)
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'data' => [
                'phone' => $phone,
                'expires_at' => $otpData['expires_at'],
                // Remove this in production
                'otp' => $otpData['otp'],
            ],
        ]);
    }

    /**
     * Verify OTP and login provider
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $phone = $request->input('phone');
        $code = $request->input('code');

        if (!$this->otpService->verifyOtp($phone, $code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 401);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user || $user->role !== UserRole::OWNER) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $token = JWTAuth::fromUser($user);

        // Load provider relationship
        $user->load('owner');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'owner' => $user->provider,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => (int) config('jwt.ttl') * 60,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    /**
     * Get authenticated provider user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('owner');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'owner' => $user->provider,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    /**
     * Logout provider
     */
    public function logout(Request $request): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}

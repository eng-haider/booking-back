<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Customer\RegisterRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Register a new customer.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create customer directly (no user)
            $customer = Customer::create([
                'full_name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            DB::commit();

            // Send OTP for verification
            $otpResult = $this->otpService->generateOtp($request->phone);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. OTP sent to your phone.',
                'data' => [
                    'customer' => $customer,
                    'otp' => $otpResult,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send OTP to phone number.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Check if phone number exists in customers table
        $customer = Customer::where('phone', $request->phone)->first();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not registered',
            ], 404);
        }

        $result = $this->otpService->generateOtp($request->phone);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'data' => $result,
        ]);
    }

    /**
     * Verify OTP and authenticate customer.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $isValid = $this->otpService->verifyOtp(
            $request->phone,
            $request->code
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 401);
        }

        // Get customer by phone
        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        // Mark phone as verified
        if (!$customer->phone_verified_at) {
            $customer->update(['phone_verified_at' => now()]);
        }

        // Create token directly from customer
        $token = JWTAuth::fromUser($customer);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => $customer,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => (int) config('jwt.ttl') * 60,
            ],
        ]);
    }

    /**
     * Get authenticated customer profile.
     */
    public function me(): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
            ],
        ]);
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $customer->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $customer->fresh(),
        ]);
    }

    /**
     * Logout customer.
     */
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}

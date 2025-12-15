<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $phone = $request->input('phone');
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($user->role !== UserRole::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access only.',
            ], 403);
        }

        $otpData = $this->otpService->generateOtp($phone);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'data' => [
                'phone' => $phone,
                'expires_at' => $otpData['expires_at'],
                'otp' => $otpData['otp'],
            ],
        ]);
    }

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

        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => (int) config('jwt.ttl') * 60,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}

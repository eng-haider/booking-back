<?php

// use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ConfigCheckController;
use App\Http\Controllers\GovernorateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Auth
|--------------------------------------------------------------------------
*/

// Commented out - Use customer/admin/provider specific auth routes instead
// Route::prefix('auth')->group(function () {
//     Route::post('/login', [AuthController::class, 'login']);
//     Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
//     
//     Route::middleware('auth:sanctum')->group(function () {
//         Route::post('/logout', [AuthController::class, 'logout']);
//         Route::get('/me', [AuthController::class, 'me']);
//     });
// });

/*
|--------------------------------------------------------------------------
| Debug/Config Check Routes (Protected by token)
|--------------------------------------------------------------------------
*/

Route::get('/config/check-qicard', [ConfigCheckController::class, 'checkQiCard']);

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Get governorates list (public)
Route::get('/governorates', [GovernorateController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/customer.php';

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Add more protected routes here
});

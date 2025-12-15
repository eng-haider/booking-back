<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\VenueController;
use App\Http\Controllers\Admin\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin API routes for your application.
| These routes are protected and require admin role authentication.
|
*/

// Admin Authentication (public routes)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Admin routes (protected with auth:admin and admin middleware)
Route::middleware(['auth:admin', 'admin'])->group(function () {
    
    // Admin Auth Protected Routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    
    // Provider Management
    Route::prefix('providers')->group(function () {
        Route::get('/', [ProviderController::class, 'index']);
        Route::post('/', [ProviderController::class, 'store']);
        Route::get('/{id}', [ProviderController::class, 'show']);
        Route::put('/{id}', [ProviderController::class, 'update']);
        Route::delete('/{id}', [ProviderController::class, 'destroy']);
        Route::patch('/{id}/status', [ProviderController::class, 'updateStatus']);
        Route::get('/{id}/statistics', [ProviderController::class, 'statistics']);
    });
    
    // Customer Management
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/search', [CustomerController::class, 'search']);
        Route::get('/statistics', [CustomerController::class, 'overallStatistics']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
        Route::patch('/{id}/status', [CustomerController::class, 'updateStatus']);
        Route::patch('/{id}/verify-email', [CustomerController::class, 'verifyEmail']);
        Route::get('/{id}/statistics', [CustomerController::class, 'statistics']);
    });
    
    // Booking Management
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/search', [BookingController::class, 'search']);
        Route::get('/statistics', [BookingController::class, 'overallStatistics']);
        Route::get('/upcoming', [BookingController::class, 'upcoming']);
        Route::get('/past', [BookingController::class, 'past']);
        Route::post('/check-availability', [BookingController::class, 'checkAvailability']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::put('/{id}', [BookingController::class, 'update']);
        Route::delete('/{id}', [BookingController::class, 'destroy']);
        Route::patch('/{id}/status', [BookingController::class, 'updateStatus']);
        Route::patch('/{id}/confirm', [BookingController::class, 'confirm']);
        Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);
        Route::patch('/{id}/complete', [BookingController::class, 'complete']);
        Route::get('/{id}/statistics', [BookingController::class, 'statistics']);
    });
    
    // Venue Management
    Route::prefix('venues')->group(function () {
        Route::get('/', [VenueController::class, 'index']);
        Route::post('/', [VenueController::class, 'store']);
        Route::get('/search', [VenueController::class, 'search']);
        Route::get('/statistics', [VenueController::class, 'overallStatistics']);
        Route::get('/featured', [VenueController::class, 'featured']);
        Route::get('/by-city', [VenueController::class, 'byCity']);
        Route::get('/provider/{providerId}', [VenueController::class, 'byProvider']);
        Route::get('/{id}', [VenueController::class, 'show']);
        Route::put('/{id}', [VenueController::class, 'update']);
        Route::delete('/{id}', [VenueController::class, 'destroy']);
        Route::patch('/{id}/status', [VenueController::class, 'updateStatus']);
        Route::patch('/{id}/toggle-featured', [VenueController::class, 'toggleFeatured']);
        Route::post('/{id}/amenities', [VenueController::class, 'syncAmenities']);
        Route::get('/{id}/statistics', [VenueController::class, 'statistics']);
    });
    
    // Category Management
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/active', [CategoryController::class, 'active']);
        Route::post('/reorder', [CategoryController::class, 'reorder']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::patch('/{id}/toggle-active', [CategoryController::class, 'toggleActive']);
        Route::get('/{id}/statistics', [CategoryController::class, 'statistics']);
    });
    
    // Review Management
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::get('/statistics', [ReviewController::class, 'statistics']);
        Route::get('/recent', [ReviewController::class, 'recent']);
        Route::get('/top-rated-venues', [ReviewController::class, 'topRatedVenues']);
        Route::get('/venue/{venueId}', [ReviewController::class, 'byVenue']);
        Route::get('/customer/{customerId}', [ReviewController::class, 'byCustomer']);
        Route::get('/provider/{providerId}', [ReviewController::class, 'byProvider']);
        Route::get('/{id}', [ReviewController::class, 'show']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
    });

    // Payment Management
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/statistics', [PaymentController::class, 'statistics']);
        Route::get('/recent-activities', [PaymentController::class, 'recentActivities']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('/{id}/verify', [PaymentController::class, 'verify']);
        Route::post('/{id}/refund', [PaymentController::class, 'refund']);
    });
    
});

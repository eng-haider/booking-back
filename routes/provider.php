<?php

use App\Http\Controllers\Provider\AmenityController;
use App\Http\Controllers\Provider\AuthController;
use App\Http\Controllers\Provider\BookingController;
use App\Http\Controllers\Provider\OfferController;
use App\Http\Controllers\Provider\ProfileController;
use App\Http\Controllers\Provider\ReviewController;
use App\Http\Controllers\Provider\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Provider API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register provider API routes for your application.
| These routes are for venue owners/providers to manage their business.
|
*/

// Provider Authentication (public routes)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

// Provider routes (protected with auth:provider and provider middleware)
Route::middleware(['auth:provider', 'provider'])->group(function () {
    
    // Provider Auth Protected Routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    
    // Provider Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::get('/statistics', [ProfileController::class, 'statistics']);
    });
    
    // Venue Management
    Route::prefix('venues')->group(function () {
        Route::get('/', [VenueController::class, 'index']);
        Route::post('/', [VenueController::class, 'store']);
        Route::get('/{id}', [VenueController::class, 'show']);
        Route::put('/{id}', [VenueController::class, 'update']);
        Route::delete('/{id}', [VenueController::class, 'destroy']);
        Route::patch('/{id}/status', [VenueController::class, 'updateStatus']);
        Route::get('/{id}/statistics', [VenueController::class, 'statistics']);
        Route::get('/{id}/available-time-periods', [VenueController::class, 'availableTimePeriods']);
        
        // Photo Management
        Route::post('/{id}/photos', [VenueController::class, 'uploadPhoto']);
        Route::delete('/{venueId}/photos/{photoId}', [VenueController::class, 'deletePhoto']);
        Route::patch('/{venueId}/photos/{photoId}/primary', [VenueController::class, 'setPrimaryPhoto']);
    });
    
    // Amenities (read-only for providers)
    Route::get('/amenities', [AmenityController::class, 'index']);
    
    // Booking Management
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/statistics', [BookingController::class, 'statistics']);
        Route::get('/upcoming', [BookingController::class, 'upcoming']);
        Route::get('/today', [BookingController::class, 'today']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::patch('/{id}/confirm', [BookingController::class, 'confirm']);
        Route::patch('/{id}/cancel', [BookingController::class, 'cancel']);
        Route::patch('/{id}/complete', [BookingController::class, 'complete']);
    });

    // Reviews Management
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::get('/statistics', [ReviewController::class, 'statistics']);
        Route::get('/venue/{venueId}', [ReviewController::class, 'getVenueReviews']);
    });

    // Offer Management
    Route::prefix('offers')->group(function () {
        Route::get('/', [OfferController::class, 'index']);
        Route::post('/', [OfferController::class, 'store']);
        Route::get('/statistics', [OfferController::class, 'statistics']);
        Route::get('/{id}', [OfferController::class, 'show']);
        Route::put('/{id}', [OfferController::class, 'update']);
        Route::delete('/{id}', [OfferController::class, 'destroy']);
        Route::patch('/{id}/toggle-active', [OfferController::class, 'toggleActive']);
        Route::get('/venue/{venueId}', [OfferController::class, 'venueOffers']);
        Route::get('/venue/{venueId}/active', [OfferController::class, 'activeVenueOffers']);
    });
    
});

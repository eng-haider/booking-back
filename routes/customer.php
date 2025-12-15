<?php

use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\ProviderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\VenueController;
use App\Http\Controllers\Customer\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('customer')->name('customer.')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
        
        Route::middleware('auth:customer')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('update-profile');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    // Public routes (no authentication required)
    Route::prefix('providers')->name('providers.')->group(function () {
        Route::get('/', [ProviderController::class, 'index'])->name('index');
        Route::get('/search', [ProviderController::class, 'search'])->name('search');
        Route::get('/{id}', [ProviderController::class, 'show'])->name('show');
    });

    Route::prefix('venues')->name('venues.')->group(function () {
        Route::get('/', [VenueController::class, 'index'])->name('index');
        Route::get('/featured', [VenueController::class, 'featured'])->name('featured');
        Route::get('/search', [VenueController::class, 'search'])->name('search');
        Route::get('/location', [VenueController::class, 'byLocation'])->name('by-location');
        Route::get('/{id}', [VenueController::class, 'show'])->name('show');
        Route::get('/{id}/availability', [VenueController::class, 'availability'])->name('availability');
        Route::get('/{id}/reviews', [ReviewController::class, 'getVenueReviews'])->name('reviews');
    });

    // Protected customer routes
    Route::middleware('auth:customer')->group(function () {
        // Bookings
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [BookingController::class, 'index'])->name('index');
            Route::post('/', [BookingController::class, 'store'])->name('store');
            Route::get('/upcoming', [BookingController::class, 'upcoming'])->name('upcoming');
            Route::get('/past', [BookingController::class, 'past'])->name('past');
            Route::get('/statistics', [BookingController::class, 'statistics'])->name('statistics');
            Route::get('/{id}', [BookingController::class, 'show'])->name('show');
            Route::post('/{id}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        });

        // Reviews
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/my-reviews', [ReviewController::class, 'myReviews'])->name('my-reviews');
            Route::post('/', [ReviewController::class, 'store'])->name('store');
            Route::put('/{id}', [ReviewController::class, 'update'])->name('update');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
        });
    });
});

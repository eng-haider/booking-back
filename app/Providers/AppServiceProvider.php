<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;
use App\Events\PaymentRefunded;
use App\Listeners\UpdateBookingStatusOnPayment;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Listeners\LogPaymentFailure;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register payment event listeners
        Event::listen(
            PaymentCompleted::class,
            [UpdateBookingStatusOnPayment::class, 'handle']
        );

        Event::listen(
            PaymentCompleted::class,
            [SendPaymentConfirmationEmail::class, 'handle']
        );

        Event::listen(
            PaymentFailed::class,
            [LogPaymentFailure::class, 'handle']
        );
    }
}

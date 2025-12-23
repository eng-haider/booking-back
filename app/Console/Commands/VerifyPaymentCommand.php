<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Services\QiCardPaymentService;

class VerifyPaymentCommand extends Command
{
    protected $signature = 'payment:verify {transactionId}';
    protected $description = 'Verify payment status with QiCard and update database';

    public function handle(QiCardPaymentService $paymentService)
    {
        $transactionId = $this->argument('transactionId');
        
        $this->info("Verifying payment: {$transactionId}");
        
        try {
            // Find payment
            $payment = Payment::where('transaction_ref', $transactionId)->first();
            
            if (!$payment) {
                $this->error("Payment not found with transaction_ref: {$transactionId}");
                return 1;
            }
            
            $this->info("Found payment #{$payment->id}");
            $this->info("Current status: {$payment->status->value}");
            $this->info("Booking ID: {$payment->booking_id}");
            
            // Verify with QiCard
            $this->info("\nVerifying with QiCard API...");
            $result = $paymentService->verifyPayment($transactionId);
            
            // Refresh payment
            $payment->refresh();
            
            $this->info("\n✅ Verification complete!");
            $this->info("New status: {$payment->status->value}");
            $this->info("Paid at: " . ($payment->paid_at ? $payment->paid_at->toDateTimeString() : 'null'));
            
            // Check booking
            $booking = $payment->booking;
            $this->info("\nBooking Status:");
            $this->info("Payment Status: {$booking->payment_status}");
            $this->info("Confirmed At: " . ($booking->confirmed_at ? $booking->confirmed_at->toDateTimeString() : 'null'));
            
            if ($booking->status) {
                $this->info("Status: {$booking->status->name}");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}

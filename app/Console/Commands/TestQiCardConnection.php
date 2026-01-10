<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestQiCardConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qicard:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test QiCard payment gateway connection and credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing QiCard Payment Gateway Configuration...');
        $this->newLine();

        // Check configuration
        $apiUrl = config('services.qicard.api_url');
        $username = config('services.qicard.username');
        $password = config('services.qicard.password');
        $terminalId = config('services.qicard.terminal_id');

        $this->info('Configuration:');
        $this->line('API URL: ' . ($apiUrl ?: '<not set>'));
        $this->line('Username: ' . ($username ?: '<not set>'));
        $this->line('Password: ' . ($password ? str_repeat('*', strlen($password)) : '<not set>'));
        $this->line('Terminal ID: ' . ($terminalId ?: '<not set>'));
        $this->newLine();

        // Check if all required configs are set
        if (empty($apiUrl) || empty($username) || empty($password) || empty($terminalId)) {
            $this->error('❌ Configuration is incomplete!');
            $this->warn('Please ensure all QICARD_* environment variables are set in your .env file.');
            return 1;
        }

        // Test API connection
        $this->info('Testing API connection...');
        
        try {
            $testPayload = [
                'requestId' => 'TEST-' . time(),
                'amount' => 1000, // 1000 IQD (minimum test amount)
                'currency' => 'IQD',
                'merchantOrderId' => 'TEST-ORDER-' . time(),
                'description' => 'Test Connection',
                'finishPaymentUrl' => config('services.qicard.return_url'),
                'notificationUrl' => config('services.qicard.webhook_url'),
            ];

            $this->info('Sending test request to: ' . $apiUrl . 'payment');
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Terminal-Id' => $terminalId,
                ])
                ->withBasicAuth($username, $password)
                ->post($apiUrl . 'payment', $testPayload);

            $statusCode = $response->status();
            $this->newLine();
            $this->info('Response Status: ' . $statusCode);
            
            if ($response->successful()) {
                $this->info('✅ Connection successful!');
                $this->line('Response: ' . $response->body());
                return 0;
            } else {
                $this->error('❌ Connection failed!');
                $this->line('Status Code: ' . $statusCode);
                $this->line('Response: ' . $response->body());
                
                if ($statusCode === 401 || $statusCode === 403) {
                    $this->newLine();
                    $this->warn('Authentication Error:');
                    $this->line('• Check if username and password are correct');
                    $this->line('• Verify terminal ID is valid');
                    $this->line('• Ensure credentials haven\'t expired');
                    $this->line('• Contact QiCard support if credentials need to be reset');
                }
                
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}

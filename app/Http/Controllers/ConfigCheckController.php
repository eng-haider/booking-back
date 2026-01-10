<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConfigCheckController extends Controller
{
    /**
     * Check QiCard configuration
     * 
     * @return JsonResponse
     */
    public function checkQiCard(Request $request): JsonResponse
    {
        // Simple security check - require a token
        $token = $request->header('X-Config-Check-Token');
        $expectedToken = config('app.key'); // Use app key as simple auth
        
        if ($token !== $expectedToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'hint' => 'Set X-Config-Check-Token header to APP_KEY value'
            ], 401);
        }

        $config = [
            'api_url' => config('services.qicard.api_url'),
            'username' => config('services.qicard.username'),
            'password_set' => !empty(config('services.qicard.password')),
            'password_length' => strlen(config('services.qicard.password') ?? ''),
            'terminal_id' => config('services.qicard.terminal_id'),
            'terminal_id_type' => gettype(config('services.qicard.terminal_id')),
            'currency' => config('services.qicard.currency'),
            'webhook_url' => config('services.qicard.webhook_url'),
            'return_url' => config('services.qicard.return_url'),
            'cancel_url' => config('services.qicard.cancel_url'),
            'config_cached' => app()->configurationIsCached(),
        ];

        // Test connection if requested
        $testConnection = $request->input('test', false);
        $connectionResult = null;

        if ($testConnection) {
            try {
                $testPayload = [
                    'requestId' => 'CONFIG-CHECK-' . time(),
                    'amount' => 1000,
                    'currency' => 'IQD',
                    'merchantOrderId' => 'TEST-' . time(),
                    'description' => 'Config Check Test',
                    'finishPaymentUrl' => config('services.qicard.return_url'),
                    'notificationUrl' => config('services.qicard.webhook_url'),
                ];

                $response = Http::timeout(30)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-Terminal-Id' => config('services.qicard.terminal_id'),
                    ])
                    ->withBasicAuth(
                        config('services.qicard.username'),
                        config('services.qicard.password')
                    )
                    ->post(config('services.qicard.api_url') . 'payment', $testPayload);

                $connectionResult = [
                    'success' => $response->successful(),
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'headers' => $response->headers(),
                ];
            } catch (\Exception $e) {
                $connectionResult = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'config' => $config,
            'connection_test' => $connectionResult,
            'instructions' => [
                'Add ?test=1 to test actual connection',
                'If config_cached is true, run: php artisan config:clear',
            ],
        ]);
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'qicard' => [
        'api_url' => env('QICARD_BASE_URL', 'https://uat-sandbox-3ds-api.qi.iq/api/v1/'),
        'username' => env('QICARD_USERNAME'),
        'password' => env('QICARD_PASSWORD'),
        'terminal_id' => env('QICARD_TERMINAL_ID'),
        'public_key_path' => env('QICARD_PUBLIC_KEY_PATH', 'storage/qicard/public-key.pem'),
        'verify_webhooks' => env('QICARD_VERIFY_WEBHOOKS', false),
        'webhook_url' => env('QICARD_WEBHOOK_URL'),
        'return_url' => env('QICARD_RETURN_URL', env('APP_URL') . '/api/customer/payment/callback'),
        'cancel_url' => env('QICARD_CANCEL_URL', env('APP_URL') . '/api/customer/payment/cancel'),
        'currency' => env('QICARD_CURRENCY', 'IQD'),
    ],

];

<?php
/**
 * QiCard Configuration Checker
 * 
 * Upload this file to your public_html directory and access via browser:
 * https://apibooking.tctate.com/check-qicard.php
 * 
 * This will show you the current QiCard configuration.
 * DELETE THIS FILE after checking for security reasons!
 */

// Set path to Laravel bootstrap
$laravelPath = __DIR__ . '/booking_back';

// Check if Laravel exists
if (!file_exists($laravelPath . '/vendor/autoload.php')) {
    die('❌ Laravel not found. Make sure this file is in public_html directory.');
}

// Bootstrap Laravel
require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>QiCard Configuration Check</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .config-value { font-family: monospace; background: #f8f9fa; padding: 2px 5px; }
        .warning-box { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .delete-warning { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 QiCard Configuration Checker</h1>
    
    <div class="delete-warning">
        <strong>⚠️ SECURITY WARNING:</strong> Delete this file immediately after checking! 
        It exposes configuration details.
    </div>

    <h2>Configuration Status</h2>
    <table>
        <tr>
            <th>Setting</th>
            <th>Value</th>
            <th>Status</th>
        </tr>
        <?php
        $configs = [
            'API URL' => config('services.qicard.api_url'),
            'Username' => config('services.qicard.username'),
            'Password' => config('services.qicard.password') ? str_repeat('*', 20) : null,
            'Terminal ID' => config('services.qicard.terminal_id'),
            'Currency' => config('services.qicard.currency'),
            'Webhook URL' => config('services.qicard.webhook_url'),
            'Return URL' => config('services.qicard.return_url'),
            'Cancel URL' => config('services.qicard.cancel_url'),
        ];

        $allGood = true;
        foreach ($configs as $name => $value) {
            $status = $value ? '<span class="success">✅ Set</span>' : '<span class="error">❌ Missing</span>';
            if (!$value) $allGood = false;
            echo "<tr>";
            echo "<td><strong>{$name}</strong></td>";
            echo "<td><span class='config-value'>" . ($value ?: 'NOT SET') . "</span></td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <?php if ($allGood): ?>
        <div class="warning-box">
            <h3 class="success">✅ All Configuration Values Are Set!</h3>
            <p>If you're still getting 403 errors, try:</p>
            <ol>
                <li>Clear configuration cache: <code>php artisan config:clear</code></li>
                <li>Run connection test: <code>php artisan qicard:test</code></li>
                <li>Check if credentials are valid with QiCard support</li>
                <li>Verify IP address is not blocked by QiCard</li>
            </ol>
        </div>
    <?php else: ?>
        <div class="delete-warning">
            <h3 class="error">❌ Configuration Incomplete!</h3>
            <p>Update your <code>.env</code> file and add missing values, then run:</p>
            <code>php artisan config:clear</code>
        </div>
    <?php endif; ?>

    <h2>Environment Information</h2>
    <table>
        <tr>
            <th>Item</th>
            <th>Value</th>
        </tr>
        <tr>
            <td><strong>App Environment</strong></td>
            <td><span class='config-value'><?php echo config('app.env'); ?></span></td>
        </tr>
        <tr>
            <td><strong>App Debug</strong></td>
            <td><span class='config-value'><?php echo config('app.debug') ? 'true' : 'false'; ?></span></td>
        </tr>
        <tr>
            <td><strong>App URL</strong></td>
            <td><span class='config-value'><?php echo config('app.url'); ?></span></td>
        </tr>
        <tr>
            <td><strong>Config Cached</strong></td>
            <td>
                <?php 
                $cached = app()->configurationIsCached();
                echo $cached 
                    ? '<span class="warning">⚠️ Yes - Run config:clear to update</span>' 
                    : '<span class="success">✅ No - Reading from .env</span>';
                ?>
            </td>
        </tr>
    </table>

    <div class="warning-box">
        <h3>🚀 Next Steps:</h3>
        <ol>
            <li>If configuration is complete, clear cache: <code>php artisan config:clear</code></li>
            <li>Test connection: <code>php artisan qicard:test</code></li>
            <li>Try the payment API again</li>
            <li><strong>DELETE THIS FILE for security!</strong></li>
        </ol>
    </div>

    <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #6c757d;">
        <small>Generated: <?php echo date('Y-m-d H:i:s'); ?> | Delete this file after use!</small>
    </footer>
</body>
</html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$basePath = file_exists(__DIR__ . '/../artisan') ? dirname(__DIR__) : __DIR__;

// Clear cached config so fresh .env is loaded
$cachedConfig = $basePath . '/bootstrap/cache/config.php';
if (file_exists($cachedConfig)) {
    @unlink($cachedConfig);
}

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "<pre>";
    echo "=== REFRESHING CONFIG CACHE ===\n";
    $kernel->call('config:clear');
    echo "Config cache cleared.\n\n";

    echo "=== ACTIVE DB CONFIG ===\n";
    $config = config('database.connections.mysql');
    echo "Host: " . $config['host'] . "\n";
    echo "Port: " . $config['port'] . "\n";
    echo "Database: " . $config['database'] . "\n";
    echo "Username: " . $config['username'] . "\n";
    echo "Password Length: " . strlen($config['password'] ?? '') . " chars\n\n";

    echo "=== TESTING MYSQL CREDENTIALS ===\n";
    $testUsers = [
        ['user' => $config['username'], 'pass' => $config['password']],
        ['user' => 'twmaorgn', 'pass' => $config['password']],
    ];

    $workingUser = null;
    foreach ($testUsers as $u) {
        try {
            $dsn = "mysql:host=localhost;dbname={$config['database']};charset=utf8";
            new PDO($dsn, $u['user'], $u['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            echo "✅ MySQL Connected successfully with user: '{$u['user']}'!\n\n";
            $workingUser = $u['user'];
            config(['database.connections.mysql.username' => $u['user']]);
            break;
        } catch (Throwable $e) {
            echo "❌ User '{$u['user']}' failed: " . $e->getMessage() . "\n";
        }
    }

    if (! $workingUser) {
        throw new Exception("MySQL credentials failed. Please check cPanel -> MySQL Databases to verify the password for 'twmaorgn_twcachurchadmin' or add 'twmaorgn' to database 'twmaorgn_twcachurch'.");
    }

    echo "=== REBUILDING ALL TABLES VIA MIGRATIONS ===\n";

    // Disable FK checks globally so migration order doesn't cause constraint failures
    $dsn = "mysql:host=localhost;dbname={$config['database']};charset=utf8";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    echo "✅ Foreign key checks disabled globally.\n";

    $status = $kernel->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    echo $kernel->output();

    // Re-enable FK checks
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "✅ Foreign key checks re-enabled.\n\n";

    // Ensure storage/installed flag exists
    $installedFlag = $basePath . '/storage/installed';
    if (! file_exists($installedFlag)) {
        @file_put_contents($installedFlag, date('Y-m-d H:i:s'));
        echo "✅ Created storage/installed flag.\n\n";
    }

    echo "=== CLEARING & REBUILDING CACHES ===\n";
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    echo "Caches refreshed successfully!\n";

    echo "\n=== SUCCESS! YOUR SITE IS NOW 100% LIVE! ===\n";
    echo "</pre>";
} catch (Throwable $e) {
    echo "<pre style='color:red;'>";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "TRACE:\n" . $e->getTraceAsString();
    echo "</pre>";
}

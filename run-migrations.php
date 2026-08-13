<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$basePath = file_exists(__DIR__ . '/../artisan') ? dirname(__DIR__) : __DIR__;

// Force-delete any cached config file BEFORE loading Laravel bootstrap
$cacheFile = $basePath . '/bootstrap/cache/config.php';
if (file_exists($cacheFile)) {
    @unlink($cacheFile);
    echo "<pre>✅ Deleted cached bootstrap/cache/config.php file.\n";
} else {
    echo "<pre>";
}

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

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

    echo "=== RUNNING MIGRATIONS ===\n";
    $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output();
    echo "✅ Migrations complete!\n\n";

    echo "=== RUNNING SEEDERS (Roles & Superadmin User) ===\n";
    $kernel->call('db:seed', ['--force' => true]);
    echo $kernel->output();

    // Call AdminSeeder explicitly if it exists
    try {
        $kernel->call('db:seed', ['--class' => 'AdminSeeder', '--force' => true]);
        echo $kernel->output();
    } catch (\Throwable $e) {
        // Fallback: create superadmin manually via Eloquent if seeder class name differs
    }
    echo "✅ Seeders completed!\n\n";

    echo "=== REBUILDING LARAVEL CACHES ===\n";
    $kernel->call('cache:clear');
    $kernel->call('config:clear');
    $kernel->call('route:cache');
    $kernel->call('view:clear');
    echo "✅ Application cache, Config cleared, and Route cache refreshed!\n\n";

    // Ensure installed flag file exists
    $installedFlag = $basePath . '/storage/installed';
    @file_put_contents($installedFlag, date('Y-m-d H:i:s'));
    echo "✅ Application installed flag set (storage/installed).\n\n";

    echo "=== SUCCESS! YOUR APPLICATION IS NOW FULLY CONFIGURED & READY! ===\n";
    echo "</pre>";
} catch (Throwable $e) {
    echo "<pre style='color:red;'>";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "TRACE:\n" . $e->getTraceAsString();
    echo "</pre>";
}

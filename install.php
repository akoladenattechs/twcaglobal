<?php
/**
 * TWCA Standalone Installation Wizard
 *
 * A self-contained PHP installer that runs OUTSIDE of Laravel.
 * This solves the chicken-and-egg problem where Laravel can't boot
 * because the .env file has incorrect/missing database credentials.
 *
 * Usage:
 *   1. Upload this file to the project root (twcaglobal.org/install.php)
 *   2. Access https://twcaglobal.org/install.php
 *   3. Follow the steps — it will configure .env, run migrations,
 *      seed the database, create the admin user, and mark the app as installed.
 *   4. The script deletes itself when done.
 *
 * Access: https://twcaglobal.org/install.php
 */

// ─── Configuration ─────────────────────────────────────────────────────────
/**
 * App defaults for the installer.
 * Override by setting environment variables or providing them in the
 * web-based installation wizard. Do NOT hardcode production values here.
 *
 * Supported environment variables (used as form defaults / .env values):
 *   INSTALLER_APP_NAME       — default Application Name
 *   INSTALLER_APP_TITLE      — default Site Title
 *   INSTALLER_APP_ENV        — .env APP_ENV (default: production)
 *   INSTALLER_APP_DEBUG      — .env APP_DEBUG (default: false)
 *   INSTALLER_APP_URL        — default Site URL
 *   INSTALLER_ADMIN_USERNAME — default admin username
 *
 * These are only used when no .env file exists yet.
 */
$APP_NAME = getenv('INSTALLER_APP_NAME');
$APP_TITLE = getenv('INSTALLER_APP_TITLE');
$APP_ENV = getenv('INSTALLER_APP_ENV');
$APP_DEBUG = getenv('INSTALLER_APP_DEBUG');
$APP_URL = getenv('INSTALLER_APP_URL');
$ADMIN_USERNAME = getenv('INSTALLER_ADMIN_USERNAME');

// ─── Bootstrap ─────────────────────────────────────────────────────────────
define('INSTALL_ROOT', __DIR__);
define('STORAGE_PATH', INSTALL_ROOT.'/storage');
define('ENV_PATH', INSTALL_ROOT.'/.env');
define('ENV_PROD_PATH', INSTALL_ROOT.'/.env.production');
define('ARTISAN_PATH', INSTALL_ROOT.'/artisan');
define('INSTALLED_FLAG', STORAGE_PATH.'/installed');

// Load embedded schema data (companion file, auto-generated from local DB)
// This provides PDO-based fallback when php artisan migrate fails
@require_once __DIR__.'/_schema-data.php';

set_error_handler(function ($severity, $message, $file, $line) {
    // Respect @ operator — if error_reporting is 0, suppression is active
    if (error_reporting() === 0) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/**
 * Detect if we're running over HTTPS.
 */
function isHttps(): bool
{
    return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 80) === 443;
}

/**
 * Get the base URL for this script.
 */
function scriptBaseUrl(): string
{
    $proto = isHttps() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return "$proto://$host";
}

/**
 * Safely get a value from $_POST.
 */
function post(string $key, string $default = ''): string
{
    return trim($_POST[$key] ?? $default);
}

/**
 * Flash a message to the session.
 */
function flash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages.
 */
function getFlashes(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flashes;
}

/**
 * Check if the app appears to be installed.
 */
function isInstalled(): bool
{
    return file_exists(INSTALLED_FLAG);
}

/**
 * Run a shell command and return [exitCode, output].
 */
function runCommand(string $command): array
{
    $output = [];
    $code = -1;
    exec($command, $output, $code);

    return [$code, implode("\n", $output)];
}

/**
 * Attempt to find the PHP binary path.
 */
function findPhpBinary(): ?string
{
    $candidates = [
        PHP_BINARY,
        'php',
        'php-cli',
        '/usr/bin/php',
        '/usr/bin/php-cli',
        '/usr/local/bin/php',
        '/opt/alt-php83/usr/bin/php',
        '/opt/alt-php82/usr/bin/php',
        '/opt/cpanel/ea-php83/root/usr/bin/php',
        '/opt/cpanel/ea-php82/root/usr/bin/php',
    ];
    foreach ($candidates as $bin) {
        if ($bin && is_executable($bin)) {
            return $bin;
        }
    }
    // Try `which php` or `where php`
    [$code, $output] = runCommand('which php 2>/dev/null || where php 2>/dev/null');
    if ($code === 0 && trim($output)) {
        $path = trim(explode("\n", $output)[0]);
        if (is_executable($path)) {
            return $path;
        }
    }

    return null;
}

// ─── Step Handlers ─────────────────────────────────────────────────────────

/**
 * Step 1: Welcome + Requirements Check.
 */
function stepWelcome(): array
{
    $checks = [];

    // PHP version
    $checks['PHP Version >= 8.2'] = version_compare(PHP_VERSION, '8.2.0', '>=');
    $checks[('Your version: '.PHP_VERSION)] = null; // info only

    // Extensions
    $extensions = [
        'PDO' => 'PDO',
        'PDO MySQL' => 'pdo_mysql',
        'MBString' => 'mbstring',
        'OpenSSL' => 'openssl',
        'Tokenizer' => 'tokenizer',
        'XML' => 'xml',
        'CType' => 'ctype',
        'JSON' => 'json',
        'GD' => 'gd',
        'FileInfo' => 'fileinfo',
        'cURL' => 'curl',
        'BCMath' => 'bcmath',
    ];
    foreach ($extensions as $label => $ext) {
        $checks["$label Extension"] = extension_loaded($ext);
    }

    // Directory permissions
    $dirs = [
        'storage/' => STORAGE_PATH,
        'storage/framework/' => STORAGE_PATH.'/framework',
        'storage/logs/' => STORAGE_PATH.'/logs',
        'bootstrap/cache/' => INSTALL_ROOT.'/bootstrap/cache',
    ];
    foreach ($dirs as $label => $path) {
        if (file_exists($path)) {
            $checks["$label (writable)"] = is_writable($path);
        } else {
            $checks["$label (exists)"] = false;
        }
    }

    // .env writability (or creatable)
    $envCheck = true;
    if (file_exists(ENV_PATH)) {
        $envCheck = is_writable(ENV_PATH);
    } else {
        $envCheck = is_writable(dirname(ENV_PATH));
    }
    $checks['.env (writable or creatable)'] = $envCheck;

    // vendor check
    $checks['vendor/ (composer installed)'] = file_exists(INSTALL_ROOT.'/vendor/autoload.php');

    // artisan check
    $checks['artisan file exists'] = file_exists(ARTISAN_PATH);

    $allPassed = true;
    foreach ($checks as $label => $result) {
        if ($result === null) {
            continue;
        } // info only
        if (! $result) {
            $allPassed = false;
            break;
        }
    }

    return [$checks, $allPassed];
}

/**
 * Step 2: Database Configuration.
 */
function handleStepDatabase(): ?string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    // Hard guard: never allow a reinstall once the app is installed.
    if (isInstalled()) {
        http_response_code(403);

        return null;
    }

    $host = post('db_host');
    $port = post('db_port');
    $database = post('db_database');
    $username = post('db_username');
    $password = post('db_password');
    $createDb = post('create_database') === '1';

    if (! $database || ! $username) {
        flash('danger', 'Database name and username are required.');

        return null;
    }

    // Test connection
    try {
        // First test without database
        $dsn = "mysql:host={$host};port={$port};charset=utf8";
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        // Check if database exists
        $stmt = $conn->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '.$conn->quote($database));
        $dbExists = (bool) $stmt->fetchColumn();

        if (! $dbExists && $createDb) {
            $conn->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            $dbExists = true;
        }

        if (! $dbExists) {
            flash('warning', "Database '{$database}' does not exist. Either create it in cPanel first, or check the 'Create database' option.");

            return null;
        }

        // Test connection with the specific database
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8";
        new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        // Store in session
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['db_host'] = $host;
        $_SESSION['db_port'] = $port;
        $_SESSION['db_database'] = $database;
        $_SESSION['db_username'] = $username;
        $_SESSION['db_password'] = $password;

        flash('success', 'Database connection successful!');

        return 'settings';

    } catch (PDOException $e) {
        flash('danger', 'Database connection failed: '.$e->getMessage());

        return null;
    } catch (Exception $e) {
        flash('danger', 'Error: '.$e->getMessage());

        return null;
    }
}

/**
 * Step 3: Site Settings + Admin Account.
 */
function handleStepSettings(): ?string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    // Hard guard: never allow a reinstall once the app is installed.
    if (isInstalled()) {
        http_response_code(403);

        return null;
    }

    // INSTALLER_* env vars are optional deploy-time overrides (see top of file).
    global $APP_NAME, $APP_TITLE, $APP_URL, $ADMIN_USERNAME;

    // No hardcoded values: defaults come from INSTALLER_* env vars, or are left
    // empty so the user must type them. The only computed fallback is the site
    // URL, derived from the current request (never hardcoded).
    $appName = post('app_name', $APP_NAME ?: '');
    $siteTitle = post('site_title', $APP_TITLE ?: '');
    $appUrl = post('app_url', $APP_URL ?: scriptBaseUrl());
    $adminEmail = post('admin_email');
    $adminUsername = post('admin_username', $ADMIN_USERNAME ?: '');
    $adminPassword = post('admin_password');
    $adminPasswordConfirm = post('admin_password_confirmation');
    $firstName = post('first_name');
    $lastName = post('last_name');

    if (! $appName) {
        flash('danger', 'Application name is required.');

        return null;
    }
    if (! $siteTitle) {
        flash('danger', 'Site title is required.');

        return null;
    }
    if (! $adminUsername) {
        flash('danger', 'Admin username is required.');

        return null;
    }
    if (! $adminEmail) {
        flash('danger', 'Admin email is required.');

        return null;
    }
    if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        flash('danger', 'Please enter a valid admin email address.');

        return null;
    }
    if (! $adminPassword || strlen($adminPassword) < 8) {
        flash('danger', 'Admin password must be at least 8 characters.');

        return null;
    }
    if ($adminPassword !== $adminPasswordConfirm) {
        flash('danger', 'Passwords do not match.');

        return null;
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['app_name'] = $appName;
    $_SESSION['site_title'] = $siteTitle;
    $_SESSION['app_url'] = rtrim($appUrl, '/');
    $_SESSION['admin_email'] = $adminEmail;
    $_SESSION['admin_username'] = $adminUsername;
    $_SESSION['admin_password'] = $adminPassword;
    $_SESSION['admin_first_name'] = $firstName;
    $_SESSION['admin_last_name'] = $lastName;

    return 'confirm';
}

/**
 * Step 4: Run the actual installation.
 */
function handleStepRun(): array
{
    // Never allow the install to actually run once the app is installed.
    if (isInstalled()) {
        http_response_code(403);

        return ['success' => false, 'message' => 'Application is already installed. Reinstall blocked.'];
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $requiredKeys = ['db_host', 'db_database', 'db_username', 'db_password', 'app_name', 'site_title', 'admin_email', 'admin_username', 'admin_password'];
    foreach ($requiredKeys as $key) {
        if (empty($_SESSION[$key])) {
            return ['success' => false, 'message' => "Missing session data: {$key}. Please go back and complete all steps."];
        }
    }

    $dbHost = $_SESSION['db_host'];
    $dbPort = $_SESSION['db_port'];
    $dbDatabase = $_SESSION['db_database'];
    $dbUsername = $_SESSION['db_username'];
    $dbPassword = $_SESSION['db_password'];
    $appName = $_SESSION['app_name'];
    $siteTitle = $_SESSION['site_title'];
    $appUrl = $_SESSION['app_url'];
    $adminEmail = $_SESSION['admin_email'];
    $adminUsername = $_SESSION['admin_username'];
    $adminPassword = $_SESSION['admin_password'];
    $adminFirstName = $_SESSION['admin_first_name'] ?? '';
    $adminLastName = $_SESSION['admin_last_name'] ?? '';

    set_time_limit(300);

    try {
        // ── 1. Write .env file ──
        writeEnvFile($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $appName, $appUrl);
        $steps = [];
        $steps[] = '✅ .env configuration written';

        // ── 2. Run migrations ──
        $migrationResult = runMigrations($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword);
        if ($migrationResult['success']) {
            $steps[] = '✅ Database migrations completed';

            // ── 3. Seed RBAC ──
            $seedResult = runSeeder();
            if ($seedResult['success']) {
                $steps[] = '✅ Roles & permissions seeded';
            } else {
                $steps[] = '⚠️ '.$seedResult['message'];
            }
        } else {
            $steps[] = '⚠️ '.$migrationResult['message'];
        }

        // ── 4. Create super admin (via direct PDO — works even without artisan) ──
        $adminResult = createSuperAdmin($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $adminFirstName, $adminLastName, $adminUsername, $adminEmail, $adminPassword);
        if ($adminResult['success']) {
            $steps[] = '✅ Super admin account created';
        } else {
            $steps[] = '⚠️ '.$adminResult['message'];
        }

        // ── 5. Save site settings ──
        saveSiteSettings($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $siteTitle);
        $steps[] = '✅ Site settings saved';

        // ── 6. Mark as installed ──
        if (! is_dir(STORAGE_PATH)) {
            mkdir(STORAGE_PATH, 0775, true);
        }
        file_put_contents(INSTALLED_FLAG, date('Y-m-d H:i:s'));
        $steps[] = '✅ Application marked as installed';

        // ── 7. Clear session ──
        session_unset();
        session_destroy();

        $message = implode('<br>', $steps);
        $hasErrors = str_contains($message, '⚠️');

        if ($hasErrors) {
            $message .= '<br><br><strong>⚠️ Some steps had warnings.</strong>';
            $message .= '<br>Your admin account has been created and you can try logging in.';
            $message .= '<br>If something is missing, check the error details above.';
        }

        return ['success' => true, 'message' => $message];

    } catch (Throwable $e) {
        return ['success' => false, 'message' => 'Installation failed: '.$e->getMessage().' in '.basename($e->getFile()).':'.$e->getLine()];
    }
}

/**
 * Write the .env file with all configuration values.
 */
function writeEnvFile(string $dbHost, string $dbPort, string $dbDatabase, string $dbUsername, string $dbPassword, string $appName, string $appUrl): void
{
    // Start with production template or create basic env
    if (file_exists(ENV_PROD_PATH)) {
        $envContent = file_get_contents(ENV_PROD_PATH);
    } else {
        $envContent = getDefaultEnvContent();
    }

    // Generate APP_KEY
    $appKey = 'base64:'.base64_encode(random_bytes(32));

    $replacements = [
        'APP_NAME' => '"'.$appName.'"',
        'APP_ENV' => $GLOBALS['APP_ENV'] ?: 'production',
        'APP_KEY' => $appKey,
        'APP_DEBUG' => $GLOBALS['APP_DEBUG'] ?: 'false',
        'APP_URL' => $appUrl,
        'ASSET_URL' => $appUrl,
        'NEWSLETTER_PUBLIC_URL' => $appUrl,
        'DB_HOST' => $dbHost,
        'DB_PORT' => $dbPort,
        'DB_DATABASE' => $dbDatabase,
        'DB_USERNAME' => $dbUsername,
        'DB_PASSWORD' => $dbPassword,
    ];

    foreach ($replacements as $key => $value) {
        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";
        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            $envContent .= PHP_EOL.$replacement;
        }
    }

    // Clean up comment lines that might interfere with DB values
    $envContent = preg_replace('/^#.*DB_.*/m', '', $envContent);
    $envContent = preg_replace('/\n{3,}/', "\n\n", $envContent);

    $bytes = @file_put_contents(ENV_PATH, $envContent);
    if ($bytes === false) {
        throw new RuntimeException('Failed to write .env file. Check file permissions.');
    }
}

/**
 * Try to migrate the database — first via php artisan (exec), then via PDO + embedded SQL.
 */
function runMigrations(string $dbHost, string $dbPort, string $dbDatabase, string $dbUsername, string $dbPassword): array
{
    $allErrors = [];

    // ── Attempt 1: Try php artisan migrate with current DB_HOST ──
    $result = runArtisanMigrate();
    if ($result['success']) {
        return ['success' => true, 'message' => 'Database migrations completed via artisan.'];
    }
    $allErrors[] = 'artisan migrate: '.substr($result['output'], 0, 300);

    // ── Attempt 2: Switch DB_HOST to "localhost" and retry (cPanel socket fix) ──
    if ($dbHost !== 'localhost') {
        $result = runArtisanMigrateWithHost('localhost', $dbPort, $dbDatabase, $dbUsername, $dbPassword);
        if ($result['success']) {
            return ['success' => true, 'message' => 'Database migrations completed via artisan (localhost).'];
        }
        $allErrors[] = 'artisan (localhost): '.substr($result['output'], 0, 300);
    }

    // ── Attempt 3: Import embedded schema via PDO (no exec needed — uses the working TCP connection) ──
    $sqlResult = importSchemaSql($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword);
    if ($sqlResult['success']) {
        return $sqlResult;
    }
    $allErrors[] = 'PDO schema import: '.$sqlResult['message'];

    // ── All attempts failed ──
    return [
        'success' => false,
        'message' => 'Could not create database tables automatically.'
            .' The <code>.env</code> file has been written with your credentials.'
            .'<br><br>Try running this manually via <strong>cPanel → phpMyAdmin</strong>:'
            .'<ol>'
            .'<li>Select the database: <code>'.htmlspecialchars($dbDatabase).'</code></li>'
            .'<li>Click <strong>Import</strong> tab</li>'
            .'<li>Click <strong>Choose File</strong> and select <code>_schema-data.php</code> from the server (rename to .sql first)? — or run <code>php artisan migrate --force</code> if you have terminal access</li>'
            .'</ol>'
            .'<details><summary>Technical details (click to expand)</summary>'
            .'<pre>'.htmlspecialchars(implode("\n\n", $allErrors)).'</pre>'
            .'</details>',
    ];
}

/**
 * Run php artisan migrate with current .env settings.
 */
function runArtisanMigrate(): array
{
    return runArtisanCommandWithEnv('migrate --force --no-interaction');
}

/**
 * Update DB_HOST in .env and try running artisan.
 */
function runArtisanMigrateWithHost(string $newHost, string $port, string $db, string $user, string $pass): array
{
    $origEnv = @file_get_contents(ENV_PATH);
    $origHost = '';
    if (preg_match('/^DB_HOST=(.*)$/m', $origEnv, $m)) {
        $origHost = trim($m[1]);
    }

    $envContent = preg_replace('/^DB_HOST=.*/m', "DB_HOST={$newHost}", $origEnv);
    file_put_contents(ENV_PATH, $envContent);

    $result = runArtisanCommandWithEnv('migrate --force --no-interaction');

    if ($result['success']) {
        $_SESSION['db_host'] = $newHost;
    } elseif ($origHost && $origHost !== $newHost) {
        file_put_contents(ENV_PATH, $origEnv);
    }

    return $result;
}

/**
 * Run an artisan command via exec, trying multiple PHP binaries.
 */
function runArtisanCommandWithEnv(string $command): array
{
    $phpBin = findPhpBinary();
    if (! $phpBin) {
        return ['success' => false, 'output' => 'PHP binary not found'];
    }

    $artisanPath = ARTISAN_PATH;
    if (! file_exists($artisanPath)) {
        return ['success' => false, 'output' => 'artisan not found at: '.$artisanPath];
    }

    $phpBinaries = array_unique(array_filter([
        $phpBin,
        PHP_BINARY,
        '/usr/local/bin/php',
        '/usr/bin/php',
        '/opt/alt-php83/usr/bin/php',
        '/opt/alt-php82/usr/bin/php',
        '/opt/cpanel/ea-php83/root/usr/bin/php',
        '/opt/cpanel/ea-php82/root/usr/bin/php',
    ]));

    foreach ($phpBinaries as $binary) {
        if (! is_executable($binary)) {
            continue;
        }
        $cmd = escapeshellcmd($binary).' '.escapeshellarg($artisanPath).' '.$command.' 2>&1';
        [$code, $output] = runCommand($cmd);
        if ($code === 0) {
            return ['success' => true, 'output' => $output];
        }
        if ($code !== 0 && ! str_contains($output, 'SQLSTATE') && ! str_contains($output, 'Access denied') && ! str_contains($output, 'Connection refused')) {
            return ['success' => false, 'output' => $output];
        }
    }

    return ['success' => false, 'output' => 'All PHP binaries failed.'];
}

/**
 * Import schema via MySQLi multi_query (native multi-statement execution).
 * Reliable — handles all semicolons, comment lines, and collation differences.
 */
function importSchemaSql(string $host, string $port, string $db, string $user, string $pass): array
{
    if (! defined('EMBEDDED_SCHEMA_SQL')) {
        return ['success' => false, 'message' => 'EMBEDDED_SCHEMA_SQL not defined. Upload _schema-data.php to the project root and try again.'];
    }

    try {
        $mysqli = new mysqli($host, $user, $pass, '', (int) $port);
        if ($mysqli->connect_error) {
            throw new RuntimeException('MySQL connection failed: '.$mysqli->connect_error);
        }

        // Ensure database exists and select it
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $mysqli->select_db($db);

        // Get table count before import
        $tblBefore = $mysqli->query('SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE()');
        $beforeCount = (int) $tblBefore->fetch_assoc()['cnt'];

        // Disable FK checks for clean import, execute schema, re-enable
        $mysqli->query('SET FOREIGN_KEY_CHECKS = 0');

        $schemaSql = EMBEDDED_SCHEMA_SQL;
        if (! $mysqli->multi_query($schemaSql)) {
            throw new RuntimeException('Schema execution failed: '.$mysqli->error);
        }
        // Consume all result sets (required before next query)
        while ($mysqli->more_results()) {
            $mysqli->next_result();
            if ($mysqli->error) {
                error_log('Schema SQL warning: '.$mysqli->error);
            }
        }

        $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');

        // Count newly created tables
        $tblAfter = $mysqli->query('SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE()');
        $afterCount = (int) $tblAfter->fetch_assoc()['cnt'];
        $created = $afterCount - $beforeCount;

        // Record migrations so artisan knows which already ran
        $migRecorded = 0;
        if (defined('EMBEDDED_MIGRATIONS_SQL') && EMBEDDED_MIGRATIONS_SQL !== '') {
            $migSql = EMBEDDED_MIGRATIONS_SQL;
            if ($mysqli->multi_query($migSql)) {
                while ($mysqli->more_results()) {
                    $mysqli->next_result();
                }
                $countMig = $mysqli->query('SELECT COUNT(*) as cnt FROM migrations');
                if ($countMig) {
                    $migRecorded = (int) $countMig->fetch_assoc()['cnt'];
                }
            }
        }

        $mysqli->close();

        return ['success' => true, 'message' => "Schema imported: {$created} new tables created, {$migRecorded} migration records recorded."];

    } catch (Throwable $e) {
        return ['success' => false, 'message' => 'Schema import failed: '.$e->getMessage()];
    }
}

/**
 * Run the RBAC seeder via Artisan, with PDO fallback.
 */
function runSeeder(): array
{
    $artisanError = '';

    // Try via Artisan first
    $phpBin = findPhpBinary();
    if ($phpBin) {
        $artisanPath = ARTISAN_PATH;
        $classArg = escapeshellarg('Database\Seeders\RbacSeeder');
        $cmds = [
            escapeshellcmd($phpBin).' '.escapeshellarg($artisanPath)." db:seed --class={$classArg} --force --no-interaction 2>&1",
            escapeshellcmd($phpBin).' '.escapeshellarg($artisanPath).' db:seed --force --no-interaction 2>&1',
        ];

        foreach ($cmds as $cmd) {
            [$code, $output] = runCommand($cmd);
            if ($code === 0) {
                return ['success' => true, 'message' => 'Database seeding completed.'];
            }
            $artisanError = $output;
        }
    }

    // Fallback: seed RBAC via direct PDO
    try {
        $dsn = "mysql:host={$_SESSION['db_host']};port={$_SESSION['db_port']};dbname={$_SESSION['db_database']};charset=utf8";
        $conn = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Check if roles already exist
        $stmt = $conn->query('SELECT COUNT(*) FROM roles');
        if ($stmt->fetchColumn() > 0) {
            return ['success' => true, 'message' => 'Roles already exist (skipped seeding).'];
        }

        // Insert default RBAC data
        $now = date('Y-m-d H:i:s');

        // Permissions — matches actual DB schema: id, name, description, module, created_at
        $permissions = [
            ['access_admin', 'Access the admin dashboard', 'admin', $now],
            ['manage_content', 'Create, edit, delete content (sermons, devotionals, news, events, songs)', 'content', $now],
            ['manage_media', 'Upload and manage media files', 'media', $now],
            ['manage_users', 'Create, edit, delete user accounts', 'users', $now],
            ['manage_roles', 'Create, edit, delete roles and assign permissions', 'roles', $now],
            ['manage_settings', 'Modify site-wide settings', 'settings', $now],
            ['manage_finance', 'Access and manage financial records', 'finance', $now],
            ['manage_members', 'Manage church member directory', 'members', $now],
            ['manage_groups', 'Manage member groups', 'groups', $now],
            ['view_reports', 'View reports and analytics', 'reports', $now],
            ['manage_menus', 'Manage navigation menus', 'menus', $now],
            ['manage_newsletter', 'Manage newsletters and subscribers', 'newsletter', $now],
        ];

        $permStmt = $conn->prepare('INSERT INTO permissions (name, description, module, created_at) VALUES (?, ?, ?, ?)');
        $permIds = [];
        foreach ($permissions as $p) {
            $permStmt->execute($p);
            $permIds[] = $conn->lastInsertId();
        }

        // Roles
        $roles = [
            ['Super Admin', 'Has full access to all features and can manage other users', 1, $now, $now],
            ['Administrator', 'Has full access to all features except user/role management', 0, $now, $now],
            ['Editor', 'Can manage content and media, view reports', 0, $now, $now],
            ['Media Manager', 'Can upload and manage media files only', 0, $now, $now],
            ['Finance Admin', 'Can access and manage financial records', 0, $now, $now],
            ['Member Care', 'Can manage members and groups', 0, $now, $now],
            ['Viewer', 'Can only view content and reports', 0, $now, $now],
        ];

        $roleStmt = $conn->prepare('INSERT INTO roles (name, description, is_super_admin, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
        $roleIds = [];
        foreach ($roles as $r) {
            $roleStmt->execute($r);
            $roleIds[] = $conn->lastInsertId();
        }

        // Role-Permission assignments
        // Super Admin gets all permissions
        $rpStmt = $conn->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
        foreach ($permIds as $pid) {
            $rpStmt->execute([$roleIds[0], $pid]);
        }
        // Admin gets all except manage_roles, manage_users
        foreach ($permIds as $i => $pid) {
            $permName = $permissions[$i][0];
            if (! in_array($permName, ['manage_roles', 'manage_users'])) {
                $rpStmt->execute([$roleIds[1], $pid]);
            }
        }
        // Editor gets content + media + view_reports
        $editorPerms = ['manage_content', 'manage_media', 'view_reports'];
        foreach ($permIds as $i => $pid) {
            if (in_array($permissions[$i][0], $editorPerms)) {
                $rpStmt->execute([$roleIds[2], $pid]);
            }
        }
        // Media Manager gets manage_media
        foreach ($permIds as $i => $pid) {
            if ($permissions[$i][0] === 'manage_media') {
                $rpStmt->execute([$roleIds[3], $pid]);
            }
        }
        // Finance Admin gets manage_finance + view_reports
        foreach ($permIds as $i => $pid) {
            if (in_array($permissions[$i][0], ['manage_finance', 'view_reports'])) {
                $rpStmt->execute([$roleIds[4], $pid]);
            }
        }
        // Member Care gets manage_members + manage_groups
        foreach ($permIds as $i => $pid) {
            if (in_array($permissions[$i][0], ['manage_members', 'manage_groups'])) {
                $rpStmt->execute([$roleIds[5], $pid]);
            }
        }
        // Viewer gets view_reports
        foreach ($permIds as $i => $pid) {
            if ($permissions[$i][0] === 'view_reports') {
                $rpStmt->execute([$roleIds[6], $pid]);
            }
        }

        return ['success' => true, 'message' => 'RBAC seeded via direct PDO ('.count($permIds).' permissions, '.count($roleIds).' roles).'];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Seeder failed (both artisan and PDO): '.$e->getMessage().'<br><pre>'.htmlspecialchars(substr($artisanError, 0, 500)).'</pre>'];
    }
}

/**
 * Insert the super admin user directly via PDO.
 */
function createSuperAdmin(string $host, string $port, string $db, string $user, string $pass, string $firstName, string $lastName, string $username, string $email, string $password): array
{
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8";
        $conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Find the super admin role
        $stmt = $conn->query('SELECT id FROM roles WHERE is_super_admin = 1 LIMIT 1');
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $role) {
            // Create it
            $conn->prepare('INSERT INTO roles (name, description, is_super_admin, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())')
                ->execute(['Super Admin', 'Has full access to all features and can manage other users']);
            $roleId = $conn->lastInsertId();
        } else {
            $roleId = $role['id'];
        }

        // Check if user already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$email, $username]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            // Update existing user to be super admin
            $conn->prepare("UPDATE users SET role_id = ?, status = 'active', password = ? WHERE id = ?")
                ->execute([$roleId, password_hash($password, PASSWORD_BCRYPT), $existingUser['id']]);
        } else {
            // Create new super admin
            $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, role_id, status, last_login, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW(), NOW())")
                ->execute([$firstName, $lastName, $username, $email, password_hash($password, PASSWORD_BCRYPT), $roleId]);
        }

        return ['success' => true, 'message' => 'Super admin created.'];

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create admin user: '.$e->getMessage()];
    }
}

/**
 * Save initial site settings.
 */
function saveSiteSettings(string $host, string $port, string $db, string $user, string $pass, string $siteTitle): void
{
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8";
        $conn = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Check if settings table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'site_settings'");
        if ($stmt->fetch()) {
            $now = date('Y-m-d H:i:s');
            $settings = [
                ['site_title', $siteTitle, 'general', $now],
                ['primary_color', '#ce0f3d', 'appearance', $now],
                ['secondary_color', '#343a40', 'appearance', $now],
            ];

            $stmt = $conn->prepare('SELECT COUNT(*) FROM site_settings WHERE setting_key = ?');
            $insert = $conn->prepare('INSERT INTO site_settings (setting_key, setting_value, setting_group, created_at) VALUES (?, ?, ?, ?)');

            foreach ($settings as $s) {
                $stmt->execute([$s[0]]);
                if ($stmt->fetchColumn() == 0) {
                    $insert->execute($s);
                }
            }
        }
    } catch (PDOException $e) {
        // Non-critical — log and continue
        error_log('Failed to save site settings: '.$e->getMessage());
    }
}

/**
 * Default .env content.
 */
function getDefaultEnvContent(): string
{
    return <<<'ENV'
APP_NAME=
APP_ENV=
APP_KEY=
APP_DEBUG=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_CHARSET=utf8
DB_COLLATION=utf8_unicode_ci

LOG_CHANNEL=stack
LOG_LEVEL=error

SESSION_DRIVER=file
SESSION_LIFETIME=120

FILESYSTEM_DISK=local
ENV;
}

// ─── Router ────────────────────────────────────────────────────────────────

@session_start();

// Determine current step
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$action = $_GET['action'] ?? '';

// ── Handle AJAX: Test Database Connection ──
if ($action === 'test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $host = post('db_host');
        $port = post('db_port');
        $database = post('db_database');
        $username = post('db_username');
        $password = post('db_password');

        $dsn = "mysql:host={$host};port={$port};charset=utf8";
        $conn = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);

        $stmt = $conn->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '.$conn->quote($database));
        $dbExists = (bool) $stmt->fetchColumn();

        if ($dbExists) {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8";
            new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        }

        echo json_encode(['success' => true, 'db_exists' => $dbExists, 'database' => $database]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Handle AJAX: Run Installation ──
if ($action === 'run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    // Hard guard: never re-run the installer once the app is installed.
    if (isInstalled()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Application is already installed. Reinstall blocked.']);
        exit;
    }
    $result = handleStepRun();
    echo json_encode($result);
    exit;
}

// Check if already installed. If so, refuse to process any POST
// submissions — this prevents an attacker (or an accidental repeat
// run) from re-running the installer, overwriting .env and creating
// a fresh admin account. The banner below still shows how to
// legitimately reinstall (delete storage/installed).
$alreadyInstalled = isInstalled();

// Handle form submissions
if (! $alreadyInstalled) {
    if ($step === 2) {
        $nextStep = handleStepDatabase();
        if ($nextStep) {
            $step = 3;
        }
    } elseif ($step === 3) {
        $nextStep = handleStepSettings();
        if ($nextStep) {
            $step = 4;
        }
    }
}

// ─── HTML Output ───────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation Wizard — TWCA Church</title>
<style>
*, *::before, *::after { box-sizing: border-box; }
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    min-height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 640px;
    overflow: hidden;
}
.card-header {
    background: linear-gradient(135deg, #ce0f3d, #a00c30);
    color: #fff;
    padding: 24px 32px;
    text-align: center;
}
.card-header h1 { margin: 0; font-size: 22px; font-weight: 700; }
.card-header p { margin: 6px 0 0; opacity: 0.85; font-size: 14px; }
.card-body { padding: 32px; }

/* Steps indicator */
.steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 28px;
    padding: 0 4px;
}
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}
.step-item::after {
    content: '';
    position: absolute;
    top: 16px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}
.step-item:last-child::after { display: none; }
.step-item.active::after { background: #ce0f3d; }
.step-item.completed::after { background: #28a745; }
.step-number {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700;
    background: #e0e0e0; color: #999;
    position: relative; z-index: 1;
    margin-bottom: 4px;
    transition: all 0.3s;
}
.step-item.active .step-number { background: #ce0f3d; color: #fff; }
.step-item.completed .step-number { background: #28a745; color: #fff; }
.step-label { font-size: 11px; color: #999; text-align: center; font-weight: 500; }
.step-item.active .step-label { color: #ce0f3d; font-weight: 600; }
.step-item.completed .step-label { color: #28a745; }

/* Alerts */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 14px;
    line-height: 1.5;
}
.alert-danger { background: #fde8e8; color: #c00; border: 1px solid #f5c6c6; }
.alert-success { background: #e8fde8; color: #0a0; border: 1px solid #c6f5c6; }
.alert-warning { background: #fff8e1; color: #8a6d00; border: 1px solid #ffe082; }
.alert-info { background: #e3f2fd; color: #0a4a8a; border: 1px solid #bbdefb; }

/* Requirements list */
.req-list { list-style: none; padding: 0; margin: 0 0 20px; }
.req-list li {
    padding: 8px 12px;
    border-radius: 6px;
    margin-bottom: 4px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.req-list li.pass { background: #e8fde8; }
.req-list li.fail { background: #fde8e8; }
.req-list li.info { background: #f5f5f5; color: #666; }
.req-icon { font-size: 16px; margin-left: 8px; }

/* Form */
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}
.form-group .hint {
    font-size: 11px;
    color: #999;
    margin-top: 2px;
}
.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
    font-family: inherit;
}
.form-control:focus {
    outline: none;
    border-color: #ce0f3d;
    box-shadow: 0 0 0 3px rgba(206,15,61,0.1);
}
.form-control:disabled { background: #f5f5f5; cursor: not-allowed; }
.form-row { display: flex; gap: 12px; }
.form-row .form-group { flex: 1; }
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}
.checkbox-group input[type="checkbox"] { width: 18px; height: 18px; }
.checkbox-group label { font-size: 13px; color: #555; margin: 0; }

/* Buttons */
.btn-group { display: flex; gap: 12px; justify-content: space-between; margin-top: 24px; }
.btn {
    padding: 10px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-primary {
    background: #ce0f3d;
    color: #fff;
}
.btn-primary:hover { background: #a00c30; }
.btn-primary:disabled { background: #e07a91; cursor: not-allowed; }
.btn-outline {
    background: transparent;
    color: #666;
    border: 2px solid #e0e0e0;
}
.btn-outline:hover { border-color: #ce0f3d; color: #ce0f3d; }
.btn-success { background: #28a745; color: #fff; }
.btn-success:hover { background: #1e7e34; }
.btn-block { width: 100%; justify-content: center; }
.btn-lg { padding: 14px 32px; font-size: 16px; }
.ml-auto { margin-left: auto; }

/* Test connection button + result */
.test-result {
    margin-top: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    display: none;
}
.test-result.success { display: block; background: #e8fde8; color: #0a0; }
.test-result.fail { display: block; background: #fde8e8; color: #c00; }
.test-result.loading { display: block; background: #fff8e1; color: #8a6d00; }

/* Confirm step */
.confirm-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.confirm-table td {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}
.confirm-table td:first-child { color: #999; font-weight: 500; width: 40%; }
.confirm-table td:last-child { color: #333; font-weight: 600; }

/* Progress bar for installation */
.progress-wrap {
    display: none;
    margin: 20px 0;
}
.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #ce0f3d, #e83e5a);
    border-radius: 4px;
    transition: width 0.5s;
}
.progress-text {
    font-size: 13px;
    color: #666;
    margin-top: 8px;
    text-align: center;
}

/* Complete page */
.complete-icon {
    font-size: 64px;
    text-align: center;
    margin-bottom: 12px;
}
.complete-icon .checkmark { color: #28a745; }
.complete-icon .cross { color: #c00; }
.complete-details {
    text-align: center;
    margin-bottom: 20px;
}
.complete-details h3 { margin: 0 0 8px; }
.complete-details p { color: #666; font-size: 14px; margin: 4px 0; }

/* Already installed banner */
.installed-banner {
    text-align: center;
    padding: 16px;
}
.installed-banner .big-icon { font-size: 48px; margin-bottom: 12px; }
.installed-banner h3 { margin: 0 0 8px; color: #28a745; }
.installed-banner p { color: #666; font-size: 14px; }

/* Responsive */
@media (max-width: 480px) {
    .card-body { padding: 20px; }
    .form-row { flex-direction: column; gap: 0; }
    .step-label { font-size: 9px; }
    .btn-group { flex-direction: column; }
}
</style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <h1>🔧 Installation Wizard</h1>
        <p>Set up your Church Management System</p>
    </div>

    <div class="card-body">
        <?php if ($alreadyInstalled) { ?>
            <!-- Already installed -->
            <div class="installed-banner">
                <div class="big-icon">✅</div>
                <h3>Application is Already Installed</h3>
                <p>The system has already been set up. If you need to reinstall, delete the <code>storage/installed</code> file and refresh this page.</p>
                <div class="btn-group" style="justify-content: center;">
                    <a href="<?= htmlspecialchars(scriptBaseUrl()) ?>/admin/login" class="btn btn-primary btn-lg">Go to Login</a>
                    <a href="<?= htmlspecialchars(scriptBaseUrl()) ?>" class="btn btn-outline">Visit Site</a>
                </div>
            </div>
            <?php exit; ?>
        <?php } ?>

        <?php if ($step > 1 && $step < 5) { ?>
        <!-- Steps indicator -->
        <div class="steps">
            <?php
            $labels = ['', 'Requirements', 'Database', 'Settings', 'Install'];
            for ($i = 1; $i <= 4; $i++) {
                $cls = $step == $i ? 'active' : ($step > $i ? 'completed' : '');
                ?>
            <div class="step-item <?= $cls ?>">
                <div class="step-number"><?= $step > $i ? '✓' : $i ?></div>
                <div class="step-label"><?= $labels[$i] ?></div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <!-- Flash messages -->
        <?php foreach (getFlashes() as $flash) { ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php } ?>

        <?php if ($step === 1 || ! isset($step)) { ?>
        <!-- ════════════════ STEP 1: Requirements ════════════════ -->
        <?php [$checks, $allPassed] = stepWelcome(); ?>
        <h3 style="margin-top:0;font-size:18px;">Welcome!</h3>
        <p style="color:#666;font-size:14px;margin-bottom:20px;">
            This wizard will help you set up your Church Management System on this server.
            First, let's check that everything is ready.
        </p>

        <h4 style="font-size:14px;margin:0 0 8px;">📋 Server Requirements</h4>
        <ul class="req-list">
            <?php foreach ($checks as $label => $result) { ?>
                <?php if ($result === null) { ?>
                    <li class="info"><span><?= htmlspecialchars($label) ?></span><span class="req-icon">ℹ️</span></li>
                <?php } else { ?>
                    <li class="<?= $result ? 'pass' : 'fail' ?>">
                        <span><?= htmlspecialchars($label) ?></span>
                        <span class="req-icon"><?= $result ? '✅' : '❌' ?></span>
                    </li>
                <?php } ?>
            <?php } ?>
        </ul>

        <div class="btn-group">
            <span></span>
            <?php if ($allPassed) { ?>
                <a href="?step=2" class="btn btn-primary btn-lg ml-auto">Continue →</a>
            <?php } else { ?>
                <button class="btn btn-primary btn-lg ml-auto" disabled>Requirements Not Met</button>
                <a href="?" class="btn btn-outline">↻ Re-check</a>
            <?php } ?>
        </div>

        <?php } elseif ($step === 2) { ?>
        <!-- ════════════════ STEP 2: Database ════════════════ -->
        <h3 style="margin-top:0;font-size:18px;">🗄️ Database Configuration</h3>
        <p style="color:#666;font-size:14px;margin-bottom:20px;">
            Enter your MySQL database credentials from cPanel
            (<strong>MySQL Databases</strong> section).
        </p>

        <form method="post" action="?step=2" id="db-form">
            <div class="form-group">
                <label for="db_host">Database Host</label>
                <input type="text" id="db_host" name="db_host" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['db_host'] ?? '') ?>" placeholder="e.g. 127.0.0.1 or localhost">
                <div class="hint">Usually <code>127.0.0.1</code> or <code>localhost</code> on cPanel</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="db_port">Port</label>
                    <input type="text" id="db_port" name="db_port" class="form-control"
                           value="<?= htmlspecialchars($_SESSION['db_port'] ?? '') ?>" placeholder="e.g. 3306">
                </div>
                <div class="form-group">
                    <label for="db_database">Database Name</label>
                    <input type="text" id="db_database" name="db_database" class="form-control"
                           value="<?= htmlspecialchars($_SESSION['db_database'] ?? '') ?>" placeholder="e.g. cpaneluser_dbname" required>
                    <div class="hint">Create this in cPanel → MySQL Databases first</div>
                </div>
            </div>
            <div class="form-group">
                <label for="db_username">Database Username</label>
                <input type="text" id="db_username" name="db_username" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['db_username'] ?? '') ?>" placeholder="e.g. twmaorgn_admin" required>
            </div>
            <div class="form-group">
                <label for="db_password">Database Password</label>
                <input type="password" id="db_password" name="db_password" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['db_password'] ?? '') ?>" placeholder="Enter MySQL password">
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="create_database" name="create_database" value="1">
                <label for="create_database">Create database if it doesn't exist (requires CREATE privilege)</label>
            </div>

            <div id="test-result" class="test-result"></div>

            <div class="btn-group">
                <a href="?step=1" class="btn btn-outline">← Back</a>
                <button type="button" class="btn btn-outline" onclick="testConnection()" id="test-btn">🔌 Test Connection</button>
                <button type="submit" class="btn btn-primary">Continue →</button>
            </div>
        </form>

        <script>
        async function testConnection() {
            const btn = document.getElementById('test-btn');
            const result = document.getElementById('test-result');
            btn.disabled = true;
            btn.textContent = '⏳ Testing...';
            result.className = 'test-result loading';
            result.textContent = 'Testing database connection...';

            const form = document.getElementById('db-form');
            const data = new FormData(form);
            data.set('action', 'test');

            try {
                const resp = await fetch('?step=2&action=test', { method: 'POST', body: data });
                const json = await resp.json();
                if (json.success) {
                    result.className = 'test-result success';
                    result.textContent = json.db_exists
                        ? '✅ Connection successful! Database exists.'
                        : '✅ Connected to server, but database "' + json.database + '" does not exist yet. Check "Create database" above.';
                } else {
                    result.className = 'test-result fail';
                    result.textContent = '❌ ' + (json.message || 'Connection failed');
                }
            } catch (e) {
                result.className = 'test-result fail';
                result.textContent = '❌ Network error: ' + e.message;
            } finally {
                btn.disabled = false;
                btn.textContent = '🔌 Test Connection';
            }
        }
        </script>

        ?>

        <?php } elseif ($step === 3) { ?>
        <!-- ════════════════ STEP 3: Settings + Admin ════════════════ -->
        <h3 style="margin-top:0;font-size:18px;">⚙️ Site Settings &amp; Admin Account</h3>
        <p style="color:#666;font-size:14px;margin-bottom:20px;">
            Configure your site and create the super administrator account.
        </p>

        <form method="post" action="?step=3">
            <h4 style="font-size:14px;margin:0 0 12px;color:#333;border-bottom:1px solid #eee;padding-bottom:8px;">🌐 Site Settings</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="app_name">Application Name</label>
                    <input type="text" id="app_name" name="app_name" class="form-control" required
                           value="<?= htmlspecialchars($_SESSION['app_name'] ?? ($APP_NAME ?: '')) ?>"
                           placeholder="e.g. The Wordfare Christian Assembly">
                </div>
                <div class="form-group">
                    <label for="site_title">Site Title</label>
                    <input type="text" id="site_title" name="site_title" class="form-control" required
                           value="<?= htmlspecialchars($_SESSION['site_title'] ?? ($APP_TITLE ?: '')) ?>"
                           placeholder="e.g. The Wordfare Christian Assembly">
                </div>
            </div>
            <div class="form-group">
                <label for="app_url">Site URL</label>
                <input type="url" id="app_url" name="app_url" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['app_url'] ?? scriptBaseUrl()) ?>" required>
                <div class="hint">The full URL where your site will be accessible</div>
            </div>

            <h4 style="font-size:14px;margin:16px 0 12px;color:#333;border-bottom:1px solid #eee;padding-bottom:8px;">👤 Super Admin Account</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                           value="<?= htmlspecialchars($_SESSION['admin_first_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                           value="<?= htmlspecialchars($_SESSION['admin_last_name'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="admin_username">Username</label>
                <input type="text" id="admin_username" name="admin_username" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['admin_username'] ?? ($ADMIN_USERNAME ?: '')) ?>" required>
            </div>
            <div class="form-group">
                <label for="admin_email">Email Address</label>
                <input type="email" id="admin_email" name="admin_email" class="form-control"
                       value="<?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?>" placeholder="admin@example.com" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="admin_password">Password (min 8 chars)</label>
                    <input type="password" id="admin_password" name="admin_password" class="form-control"
                           placeholder="Enter password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="admin_password_confirmation">Confirm Password</label>
                    <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" class="form-control"
                           placeholder="Confirm password" required minlength="8">
                </div>
            </div>

            <div class="btn-group">
                <a href="?step=2" class="btn btn-outline">← Back</a>
                <button type="submit" class="btn btn-primary">Review & Install →</button>
            </div>
        </form>

        <?php } elseif ($step === 4) { ?>
        <!-- ════════════════ STEP 4: Confirm + Run ════════════════ -->
        <h3 style="margin-top:0;font-size:18px;">📋 Confirm Installation</h3>
        <p style="color:#666;font-size:14px;margin-bottom:20px;">
            Please review your settings below. Once you click "Install", the system will:
        </p>
        <ol style="color:#555;font-size:13px;margin-bottom:20px;">
            <li>Write the <code>.env</code> configuration file</li>
            <li>Run database migrations (create all tables)</li>
            <li>Seed default roles and permissions</li>
            <li>Create your super admin account</li>
            <li>Save initial site settings</li>
            <li>Mark the application as installed</li>
        </ol>

        <table class="confirm-table">
            <tr><td>Database Host</td><td><?= htmlspecialchars($_SESSION['db_host'] ?? '') ?></td></tr>
            <tr><td>Database Name</td><td><?= htmlspecialchars($_SESSION['db_database'] ?? '') ?></td></tr>
            <tr><td>Database User</td><td><?= htmlspecialchars($_SESSION['db_username'] ?? '') ?></td></tr>
            <tr><td>App Name</td><td><?= htmlspecialchars($_SESSION['app_name'] ?? '') ?></td></tr>
            <tr><td>Site Title</td><td><?= htmlspecialchars($_SESSION['site_title'] ?? '') ?></td></tr>
            <tr><td>Site URL</td><td><?= htmlspecialchars($_SESSION['app_url'] ?? '') ?></td></tr>
            <tr><td>Admin Email</td><td><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></td></tr>
            <tr><td>Admin Username</td><td><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></td></tr>
        </table>

        <div class="progress-wrap" id="progress-wrap">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="progress-text" id="progress-text"></div>
        </div>

        <div id="result-area"></div>

        <div class="btn-group" id="action-buttons">
            <a href="?step=3" class="btn btn-outline">← Back</a>
            <button class="btn btn-success btn-lg" onclick="startInstallation()">🚀 Install Now</button>
        </div>

        <script>
        function setProgress(pct, text) {
            document.getElementById('progress-wrap').style.display = 'block';
            document.getElementById('progress-fill').style.width = pct + '%';
            document.getElementById('progress-text').textContent = text;
        }

        async function startInstallation() {
            const btn = document.querySelector('button[onclick="startInstallation()"]');
            btn.disabled = true;
            btn.textContent = '⏳ Installing...';
            document.getElementById('action-buttons').style.display = 'none';

            setProgress(10, 'Writing .env configuration...');

            try {
                const resp = await fetch('?action=run', { method: 'POST' });
                setProgress(60, 'Processing...');

                const result = await resp.json();

                if (result.success) {
                    setProgress(100, '✅ Installation complete!');
                    document.getElementById('result-area').innerHTML =
                        '<div class="alert alert-success">✅ <strong>Installation Successful!</strong><br>' +
                        htmlspecialchars(result.message) + '</div>' +
                        '<div class="btn-group" style="justify-content:center;">' +
                        '<a href="<?= htmlspecialchars(scriptBaseUrl()) ?>/admin/login" class="btn btn-primary btn-lg">🔑 Go to Login</a>' +
                        '<a href="<?= htmlspecialchars(scriptBaseUrl()) ?>" class="btn btn-outline">🏠 Visit Site</a>' +
                        '</div>';
                } else {
                    setProgress(0, '❌ Installation failed');
                    document.getElementById('result-area').innerHTML =
                        '<div class="alert alert-danger">❌ <strong>Installation Failed</strong><br>' +
                        htmlspecialchars(result.message) + '</div>' +
                        '<div class="btn-group" style="justify-content:center;">' +
                        '<a href="?step=4" class="btn btn-outline">↻ Try Again</a>' +
                        '<a href="?step=2" class="btn btn-outline">← Change Settings</a>' +
                        '</div>';
                }
            } catch (e) {
                setProgress(0, '❌ Network error');
                document.getElementById('result-area').innerHTML =
                    '<div class="alert alert-danger">❌ <strong>Network Error</strong><br>' +
                    htmlspecialchars(e.message) + '</div>' +
                    '<a href="?step=4" class="btn btn-outline btn-block">↻ Try Again</a>';
            }
        }

        function htmlspecialchars(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        </script>

        <?php } ?>
    </div>
</div>

</body>
</html>

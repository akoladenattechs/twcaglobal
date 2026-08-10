<?php
// Verify AppServiceProvider env() -> config() fix works after config:cache
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "mail.default           = ".var_export(config('mail.default'), true).PHP_EOL;
echo "mail.from.address      = ".var_export(config('mail.from.address'), true).PHP_EOL;
echo "services.resend.key    = ".var_export(config('services.resend.key') ? 'set' : 'null', true).PHP_EOL;
echo "mail.mailers.smtp.host = ".var_export(config('mail.mailers.smtp.host'), true).PHP_EOL;
echo "services.cors.allowed  = ".var_export(config('services.cors.allowed_origins'), true).PHP_EOL;
echo "app.url                = ".var_export(config('app.url'), true).PHP_EOL;
echo "OK".PHP_EOL;

<?php
// Backup legacy orphaned table data before dropping (safe to delete after).
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$dir = __DIR__.'/backups';
if (! is_dir($dir)) {
    mkdir($dir, 0775, true);
}

foreach (['news' => 'news', 'mail_templates' => 'mail_templates'] as $table => $label) {
    $rows = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
    $file = $dir.'/'.$table.'-backup-'.date('Ymd-His').'.json';
    file_put_contents($file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo $table.': '.count($rows)." rows -> ".basename($file).PHP_EOL;
}

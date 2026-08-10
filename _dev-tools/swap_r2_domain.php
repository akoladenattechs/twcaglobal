<?php

/**
 * Dry-run: find every table/column containing the old R2 dev URL.
 * Run: php _dev-tools/swap_r2_domain.php --dry
 * Execute: php _dev-tools/swap_r2_domain.php --run
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$old = 'pub-96bec4ce2cc7493f971099fe50b30231.r2.dev';
$new = 'cdn.twcaglobal.org';
$isRun = in_array('--run', $argv, true);

$tables = array_column(DB::select('SHOW TABLES'), 'Tables_in_'.env('DB_DATABASE'));

$affected = [];
foreach ($tables as $table) {
    // skip legacy/temp tables
    if (in_array($table, ['migrations', 'failed_jobs', 'jobs', 'cache', 'cache_locks', 'sessions', 'password_reset_tokens', 'personal_access_tokens', 'activity_logs'], true)) {
        continue;
    }
    $cols = DB::select("SHOW COLUMNS FROM `{$table}`");
    foreach ($cols as $col) {
        $type = strtolower($col->Type);
        $isText = str_contains($type, 'char') || str_contains($type, 'text') || str_contains($type, 'blob');
        if (! $isText) {
            continue;
        }
        $colName = $col->Field;
        $count = (int) DB::table($table)->where($colName, 'like', '%'.$old.'%')->count();
        if ($count > 0) {
            $affected[] = ['table' => $table, 'column' => $colName, 'count' => $count];
        }
    }
}

if (empty($affected)) {
    echo "No rows contain the old R2 URL. Nothing to do.\n";
    exit(0);
}

echo "Found ".count($affected)." column(s) with the old R2 URL:\n";
$total = 0;
foreach ($affected as $a) {
    echo sprintf("  %-30s %-25s %d row(s)\n", $a['table'], $a['column'], $a['count']);
    $total += $a['count'];
}
echo "Total rows to update: $total\n\n";

if ($isRun) {
    foreach ($affected as $a) {
        DB::table($a['table'])
            ->where($a['column'], 'like', '%'.$old.'%')
            ->update([$a['column'] => DB::raw("REPLACE(`{$a['column']}`, '{$old}', '{$new}')")]);
    }
    echo "DONE: replaced {$old} -> {$new} in $total row(s).\n";
} else {
    echo "Dry-run only. Re-run with --run to apply.\n";
}

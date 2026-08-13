<?php
// Standard permissions fixer for cPanel shared hosting
$path = file_exists(__DIR__ . '/../artisan') ? dirname(__DIR__) : __DIR__;

function fixPermissions($dir) {
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            chmod($item->getPathname(), 0755);
        } else {
            chmod($item->getPathname(), 0644);
        }
    }
}

try {
    if (is_dir($path . '/vendor')) {
        fixPermissions($path . '/vendor');
    }
    if (is_dir($path . '/storage')) {
        @chmod($path . '/storage', 0775);
    }
    if (is_dir($path . '/bootstrap/cache')) {
        @chmod($path . '/bootstrap/cache', 0775);
    }
    echo "SUCCESS: Permissions updated cleanly!";
} catch (Throwable $e) {
    echo "NOTICE: " . $e->getMessage();
}

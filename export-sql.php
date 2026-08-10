<?php
require_once __DIR__ . '/_schema-data.php';

$rawSql = EMBEDDED_SCHEMA_SQL;
if (defined('EMBEDDED_MIGRATIONS_SQL') && EMBEDDED_MIGRATIONS_SQL !== '') {
    $rawSql .= "\n\n" . EMBEDDED_MIGRATIONS_SQL;
}

// Unescape backslashes before single quotes
$cleanSql = "SET FOREIGN_KEY_CHECKS = 0;\n\n";
$cleanSql .= str_replace(["\\'", '\\"'], ["'", '"'], $rawSql);
$cleanSql .= "\n\nSET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/database_dump.sql', $cleanSql);
echo "Successfully generated clean database_dump.sql (" . strlen($cleanSql) . " bytes)\n";

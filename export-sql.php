<?php
require_once __DIR__ . '/_schema-data.php';

$sql = "SET FOREIGN_KEY_CHECKS = 0;\n\n";
$sql .= EMBEDDED_SCHEMA_SQL;
if (defined('EMBEDDED_MIGRATIONS_SQL') && EMBEDDED_MIGRATIONS_SQL !== '') {
    $sql .= "\n\n" . EMBEDDED_MIGRATIONS_SQL;
}
$sql .= "\n\nSET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents(__DIR__ . '/database_dump.sql', $sql);
echo "Successfully generated database_dump.sql (" . strlen($sql) . " bytes)\n";

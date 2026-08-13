<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pre-migration fix for live server (MySQL / cPanel hosting).
 *
 * Problem: Older tables were created in utf8/utf8_unicode_ci charset
 * (triggered by Schema::defaultStringLength(191) in AppServiceProvider).
 * Newer 2026 migrations use utf8mb4. MySQL rejects FK constraints between
 * tables with mismatched charsets (errno: 150).
 *
 * Fix: Convert all existing tables to utf8mb4 so that subsequent FK-creating
 * migrations can link to them without a charset conflict.
 * This migration runs BEFORE all the 2026_08_05 migrations that create FKs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Convert the database default charset to utf8mb4
        $database = config('database.connections.mysql.database');

        DB::statement("ALTER DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Convert each existing table to utf8mb4 to allow cross-table FK constraints
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $database;

        foreach ($tables as $row) {
            $table = $row->$tableKey;
            try {
                DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (\Throwable $e) {
                // Log but don't abort — some system tables may refuse conversion
                \Illuminate\Support\Facades\Log::warning("CharsetFix: Could not convert table `{$table}`: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Reverting charset conversion is generally safe to skip
        // as utf8mb4 is a superset of utf8
    }
};

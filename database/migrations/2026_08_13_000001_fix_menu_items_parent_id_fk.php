<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Safely check and drop any existing FK on parent_id using raw SQL
        // to avoid Laravel's Schema builder throwing 1091 if the constraint doesn't exist
        $database = config('database.connections.mysql.database');
        $fkNames = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? 
              AND TABLE_NAME = 'menu_items' 
              AND COLUMN_NAME = 'parent_id' 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database]);

        foreach ($fkNames as $fk) {
            try {
                DB::statement("ALTER TABLE `menu_items` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // Ignore if already dropped
            }
        }

        // Recreate the foreign key correctly referencing menu_items(id)
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('menu_items')
                  ->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('menu_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['parent_id']);
            } catch (\Throwable $e) {
                //
            }

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('menus')
                  ->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }
};

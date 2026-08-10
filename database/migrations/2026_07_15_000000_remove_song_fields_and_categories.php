<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign keys if they exist.
        // Must drop by column name: SQLite's schema grammar does not support
        // dropping FKs by name (compileDropForeign throws for empty columns),
        // and Schema::getForeignKeys() has no 'name' key on SQLite.
        foreach (Schema::getForeignKeys('songs') as $fk) {
            $columns = $fk['columns'] ?? [];
            if (empty($columns)) {
                continue;
            }
            try {
                Schema::table('songs', function (Blueprint $table) use ($columns) {
                    $table->dropForeign($columns);
                });
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
        }

        Schema::table('songs', function (Blueprint $table) {
            $columns = ['lyrics', 'composer', 'category_id', 'image_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('songs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('song_categories');
    }

    public function down(): void
    {
        Schema::create('song_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'lyrics')) {
                $table->text('lyrics')->nullable();
            }
            if (! Schema::hasColumn('songs', 'composer')) {
                $table->string('composer', 255)->nullable();
            }
            if (! Schema::hasColumn('songs', 'category_id')) {
                $table->unsignedInteger('category_id')->nullable();
            }
            if (! Schema::hasColumn('songs', 'image_id')) {
                $table->unsignedInteger('image_id')->nullable();
            }
        });
    }
};

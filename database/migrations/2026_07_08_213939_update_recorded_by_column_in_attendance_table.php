<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change recorded_by from unsignedInteger to string to accept names.
        // Uses the Schema builder so it runs on both MySQL (live) and
        // SQLite (test DB), which does not understand raw MODIFY COLUMN.
        Schema::table('attendance', function (Blueprint $table) {
            $table->string('recorded_by', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->unsignedInteger('recorded_by')->change();
        });
    }
};

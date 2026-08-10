<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // JSON array of day-of-week numbers (0=Sun, 1=Mon, ..., 6=Sat)
            // Used by recurring (non-expiring) events to specify which days repeat.
            $table->json('recurrence_days')->nullable()->after('expires');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('recurrence_days');
        });
    }
};

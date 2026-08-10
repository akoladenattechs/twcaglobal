<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change status from ENUM('draft','sent') to VARCHAR to support 'scheduled'
        // (Schema builder is cross-driver; raw MODIFY COLUMN breaks on SQLite)
        Schema::table('newsletters', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->nullable(false)->change();
        });

        Schema::table('newsletters', function (Blueprint $table) {
            // Scheduling
            $table->timestamp('scheduled_at')->nullable()->after('sent_at');

            // Test send
            $table->string('test_email', 191)->nullable()->after('total_sent');

            // Tracking counters
            $table->unsignedInteger('opens_count')->default(0)->after('total_sent');
            $table->unsignedInteger('clicks_count')->default(0)->after('opens_count');

            // Indexes
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_at',
                'test_email',
                'opens_count',
                'clicks_count',
            ]);
            $table->dropIndex(['status']);
            $table->dropIndex(['scheduled_at']);
        });

        Schema::table('newsletters', function (Blueprint $table) {
            $table->enum('status', ['draft', 'sent'])->default('draft')->nullable(false)->change();
        });
    }
};

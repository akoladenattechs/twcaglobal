<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change ENUM column to string to support new statuses (Schema builder
        // is cross-driver; raw MODIFY COLUMN breaks on the SQLite test DB)
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->nullable(false)->change();
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            // Double opt-in fields
            $table->string('verification_token', 64)->nullable()->unique()->after('name');
            $table->timestamp('verified_at')->nullable()->after('subscribed_at');

            // Unsubscribe with signed token
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('verified_at');

            // Timestamps for status transitions (column does not exist yet)
            $table->timestamp('unsubscribed_at')->nullable()->after('unsubscribe_token');

            // Bounce / complaint tracking
            $table->timestamp('bounced_at')->nullable()->after('unsubscribed_at');
            $table->text('bounce_reason')->nullable()->after('bounced_at');
            $table->timestamp('complaint_at')->nullable()->after('bounce_reason');

            // Indexes for performance
            $table->index('verification_token');
            $table->index('unsubscribe_token');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn([
                'verification_token',
                'verified_at',
                'unsubscribe_token',
                'unsubscribed_at',
                'bounced_at',
                'bounce_reason',
                'complaint_at',
            ]);

            $table->dropIndex(['verification_token']);
            $table->dropIndex(['unsubscribe_token']);
            $table->dropIndex(['status']);
        });

        // Revert to original enum
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->enum('status', ['active', 'unsubscribed'])->default('active')->nullable(false)->change();
        });
    }
};

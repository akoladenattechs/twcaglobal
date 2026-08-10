<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_id')->constrained('newsletters')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained('newsletter_subscribers')->cascadeOnDelete();
            $table->string('event', 20); // 'open', 'click', 'bounce', 'complaint'
            $table->string('link_url', 2048)->nullable(); // for click events
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['newsletter_id', 'subscriber_id']);
            $table->index('event');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_tracking');
    }
};

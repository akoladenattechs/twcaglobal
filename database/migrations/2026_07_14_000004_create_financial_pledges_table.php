<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('church_members')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('financial_campaigns')->nullOnDelete();
            $table->decimal('pledge_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->text('payment_schedule')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->date('pledge_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_pledges');
    }
};

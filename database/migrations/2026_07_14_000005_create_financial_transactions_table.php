<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['inflow', 'outflow']);
            $table->enum('category', [
                'tithe', 'offering', 'special_offering', 'building_fund',
                'pledge', 'other_income',
                'ministry_expense', 'administrative', 'utilities',
                'salary', 'maintenance', 'missions', 'other_expense',
            ]);
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'mobile_money', 'other']);
            $table->foreignId('account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('fund_id')->nullable()->constrained('financial_funds')->nullOnDelete();
            $table->date('transaction_date');
            $table->string('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->foreignId('member_id')->nullable()->constrained('church_members')->nullOnDelete();
            $table->foreignId('recorded_by')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('type');
            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make `recorded_by` nullable on financial_transactions to allow
     * system-generated rows (e.g. online payment gateway contributions)
     * that have no associated admin user.
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('recorded_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('recorded_by')->nullable(false)->change();
        });
    }
};

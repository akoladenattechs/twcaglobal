<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offerings', function (Blueprint $table) {
            $table->id();
            $table->date('service_date');
            $table->enum('service_type', ['sunday_service', 'midweek_service', 'special_service', 'other']);
            $table->decimal('amount', 10, 2);
            $table->enum('offering_type', ['tithe', 'offering', 'special_offering', 'building_fund', 'other']);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'other']);
            $table->unsignedInteger('recorded_by');
            $table->text('notes');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offerings');
    }
};

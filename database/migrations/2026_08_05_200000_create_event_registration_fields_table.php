<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_registration_fields')) {
            Schema::create('event_registration_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
                $table->string('label', 150);
                $table->string('field_type', 30)->default('text'); // text, textarea, select, email, phone, number, date, checkbox
                $table->text('options')->nullable(); // JSON array for select/radio options
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_fields');
    }
};
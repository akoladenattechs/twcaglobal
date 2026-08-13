<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_registration_answers')) {
            Schema::create('event_registration_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('registration_id');
                $table->foreign('registration_id')->references('id')->on('event_registrations')->cascadeOnDelete();
                $table->unsignedBigInteger('field_id');
                $table->foreign('field_id')->references('id')->on('event_registration_fields')->cascadeOnDelete();
                $table->text('value')->nullable();
                $table->timestamps();

                $table->unique(['registration_id', 'field_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_answers');
    }
};
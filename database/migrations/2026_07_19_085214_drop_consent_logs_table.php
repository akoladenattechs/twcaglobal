<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('consent_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('preferences')->nullable()->comment('JSON string of consent preferences');
            $table->timestamp('consented_at')->nullable();
            $table->timestamps();
        });
    }
};

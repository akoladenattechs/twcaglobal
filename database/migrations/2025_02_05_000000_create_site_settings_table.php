<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 50);
            $table->text('setting_value');
            $table->string('setting_group', 50)->nullable();
            $table->timestamp('created_at');
            $table->unique(['setting_key', 'setting_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

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
        Schema::table('hero_settings', function (Blueprint $table) {
            $table->string('button_text')->nullable()->after('description')->comment('Text for the hero CTA button');
            $table->string('button_link')->nullable()->after('button_text')->comment('URL for the hero CTA button');
            $table->boolean('show_button')->default(true)->after('button_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_settings', function (Blueprint $table) {
            $table->dropColumn(['button_text', 'button_link', 'show_button']);
        });
    }
};

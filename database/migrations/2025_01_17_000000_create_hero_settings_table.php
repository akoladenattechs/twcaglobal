<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_settings', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->nullable()->comment('Text shown in the glass badge (e.g. "Worship With Us")');
            $table->string('prefix_text')->nullable()->comment('Text before the title (e.g. "Welcome to")');
            $table->string('suffix_text')->nullable()->comment('Text after the title (e.g. "Ministries")');
            $table->boolean('show_badge')->default(true);
            $table->boolean('show_description')->default(true);
            $table->boolean('show_buttons')->default(true);
            $table->boolean('show_deco_ring')->default(true);
            $table->timestamps();
        });

        // Insert default hero settings
        DB::table('hero_settings')->insert([
            'badge_text' => null,
            'prefix_text' => 'Welcome to',
            'suffix_text' => 'Ministries',
            'show_badge' => true,
            'show_description' => true,
            'show_deco_ring' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_settings');
    }
};

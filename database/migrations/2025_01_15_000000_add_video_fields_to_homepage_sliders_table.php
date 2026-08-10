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
        Schema::table('homepage_sliders', function (Blueprint $table) {
            // Add video_id FK (nullable) — references media table
            $table->unsignedInteger('video_id')->nullable()->after('image_id');
            // Add video_url for external URLs (YouTube, Vimeo, etc.)
            $table->string('video_url', 500)->nullable()->after('video_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_sliders', function (Blueprint $table) {
            $table->dropColumn(['video_id', 'video_url']);
        });
    }
};

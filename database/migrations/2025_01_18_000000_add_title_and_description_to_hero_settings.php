<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_settings', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id')->comment('Global hero title (replaces per-slider titles)');
            $table->text('description')->nullable()->after('suffix_text')->comment('Global hero description (replaces per-slider descriptions)');
        });

        // Copy the first published slider's title/description as defaults
        $slider = DB::table('homepage_sliders')->where('status', 'published')->orderBy('display_order')->first();
        if ($slider) {
            DB::table('hero_settings')->where('id', 1)->update([
                'title' => $slider->title,
                'description' => $slider->description,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('hero_settings', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};

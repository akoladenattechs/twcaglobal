<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_sliders', function (Blueprint $table) {
            $table->string('title', 100)->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('homepage_sliders', function (Blueprint $table) {
            $table->string('title', 100)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
};

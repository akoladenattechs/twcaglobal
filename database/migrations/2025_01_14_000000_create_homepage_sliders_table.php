<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('homepage_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('subtitle', 255)->nullable();
            $table->text('description');
            $table->string('button_text', 50)->nullable();
            $table->string('button_link', 255)->nullable();
            $table->unsignedInteger('image_id');
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->integer('display_order')->default(0);
            $table->enum('status', ['published', 'draft']);
            $table->timestamp('created_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('homepage_sliders');
    }
};

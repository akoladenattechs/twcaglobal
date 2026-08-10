<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 50)->unique();
            $table->string('title', 100);
            $table->string('subtitle', 255)->nullable();
            $table->text('content');
            $table->enum('column_layout', ['single', 'two-column', 'three-column', 'four-column']);
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->integer('display_order')->default(0);
            $table->enum('status', ['draft', 'published']);
            $table->timestamp('created_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};

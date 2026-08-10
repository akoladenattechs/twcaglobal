<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('lyrics');
            $table->string('composer', 255)->nullable();
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->enum('status', ['published', 'draft']);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->boolean('featured');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};

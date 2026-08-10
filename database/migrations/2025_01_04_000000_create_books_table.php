<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('author', 100);
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('purchase_link', 255)->nullable();
            $table->string('download_link', 255)->nullable();
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->enum('status', ['draft', 'published']);
            $table->boolean('available');
            $table->timestamp('created_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

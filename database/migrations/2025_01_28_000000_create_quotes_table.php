<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('author', 100);
            $table->string('title', 100)->nullable();
            $table->enum('status', ['draft', 'published']);
            $table->integer('display_order')->default(0);
            $table->timestamp('created_at');
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};

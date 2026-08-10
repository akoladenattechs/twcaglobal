<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('devotionals', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content');
            $table->string('scripture_reference', 100)->nullable();
            $table->text('scripture_text')->nullable();
            $table->text('prayer')->nullable();
            $table->text('reflection_questions')->nullable();
            $table->string('author', 100)->nullable();
            $table->date('devotional_date');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('devotionals');
    }
};

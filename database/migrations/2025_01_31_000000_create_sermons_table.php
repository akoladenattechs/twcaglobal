<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('description');
            $table->string('preacher', 100)->nullable();
            $table->date('sermon_date');
            $table->unsignedInteger('series_id')->nullable();
            $table->foreign('series_id')->references('id')->on('sermon_series')->nullOnDelete();
            $table->unsignedInteger('media_id');
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->integer('track_number')->default(0);
            $table->enum('status', ['draft', 'published']);
            $table->boolean('featured');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermons');
    }
};

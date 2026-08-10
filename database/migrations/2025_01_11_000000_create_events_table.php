<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('description');
            $table->date('event_date');
            $table->time('event_time');
            $table->string('location', 255)->nullable();
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
            $table->enum('status', ['draft', 'published']);
            $table->timestamp('created_at');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->timestamp('updated_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

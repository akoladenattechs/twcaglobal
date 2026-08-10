<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('sermon_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sermon_id');
            $table->foreign('sermon_id')->references('id')->on('sermons')->nullOnDelete();
            $table->unsignedInteger('media_id');
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->timestamp('created_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('sermon_media');
    }
};

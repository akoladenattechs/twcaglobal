<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('song_media', function (Blueprint $table) {
            $table->unsignedInteger('song_id');
            $table->foreign('song_id')->references('id')->on('songs')->nullOnDelete();
            $table->unsignedInteger('media_id');
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->primary(['song_id', 'media_id']);
            $table->timestamp('created_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('song_media');
    }
};

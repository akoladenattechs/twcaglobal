<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sermon_media') && ! Schema::hasColumn('sermon_media', 'track_order')) {
            Schema::table('sermon_media', function (Blueprint $table) {
                $table->unsignedSmallInteger('track_order')->default(0)->after('media_id');
            });
        }

        if (Schema::hasTable('song_media') && ! Schema::hasColumn('song_media', 'track_order')) {
            Schema::table('song_media', function (Blueprint $table) {
                $table->unsignedSmallInteger('track_order')->default(0)->after('media_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sermon_media') && Schema::hasColumn('sermon_media', 'track_order')) {
            Schema::table('sermon_media', function (Blueprint $table) {
                $table->dropColumn('track_order');
            });
        }

        if (Schema::hasTable('song_media') && Schema::hasColumn('song_media', 'track_order')) {
            Schema::table('song_media', function (Blueprint $table) {
                $table->dropColumn('track_order');
            });
        }
    }
};

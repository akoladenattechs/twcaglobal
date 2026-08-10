<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('events', function (Blueprint $table) {
            // Drop the foreign key constraint first, then the column
            if (Schema::hasColumn('events', 'image_id')) {
                $table->dropForeign(['image_id']);
                $table->dropColumn('image_id');
            }
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('image_id')->nullable()->after('location');
            $table->foreign('image_id')->references('id')->on('media')->nullOnDelete();
        });
    }
};

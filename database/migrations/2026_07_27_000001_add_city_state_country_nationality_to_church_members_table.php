<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_members', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('state');
            $table->string('nationality', 100)->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('church_members', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'country', 'nationality']);
        });
    }
};

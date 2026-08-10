<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the remember_token column required by Laravel's
     * "remember me" authentication cookie.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('remember_token', 100)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};

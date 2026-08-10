<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('church_members', function (Blueprint $table) {
            $table->string('other_name', 50)->nullable()->after('last_name');
            $table->foreignId('center_id')->nullable()->constrained('center_locations')->nullOnDelete()->after('notes');
        });

        // Update any existing 'other' gender records to 'male' before removing the option
        DB::table('church_members')->where('gender', 'other')->update(['gender' => 'male']);

        // Change gender enum to remove 'other' (Schema builder is cross-driver,
        // unlike raw MODIFY COLUMN which breaks on the SQLite test DB)
        Schema::table('church_members', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->default('male')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore gender enum to include 'other'
        Schema::table('church_members', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->default('male')->change();
        });

        Schema::table('church_members', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropColumn(['center_id', 'other_name']);
        });
    }
};

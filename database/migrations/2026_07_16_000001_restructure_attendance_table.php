<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Handle both scenarios:
        // 1. Fresh install — old columns exist, new columns don't
        // 2. Existing DB — partial state from previous failed migration

        // Step 1: Drop old columns if they exist (fresh install scenario)
        if (Schema::hasColumn('attendance', 'member_id')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->dropForeign(['member_id']);
            });
            Schema::table('attendance', function (Blueprint $table) {
                $table->dropColumn(['member_id', 'member_name', 'status']);
            });
        }

        // Step 2: Add new columns if they don't exist yet
        if (! Schema::hasColumn('attendance', 'center_id')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->foreignId('center_id')->nullable()->constrained('center_locations')->nullOnDelete()->after('id');
            });
        }
        if (! Schema::hasColumn('attendance', 'males')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->integer('males')->default(0)->after('service_type');
            });
        }
        if (! Schema::hasColumn('attendance', 'females')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->integer('females')->default(0)->after('males');
            });
        }
        if (! Schema::hasColumn('attendance', 'first_timers')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->integer('first_timers')->default(0)->after('females');
            });
        }
        if (! Schema::hasColumn('attendance', 'total')) {
            Schema::table('attendance', function (Blueprint $table) {
                $table->integer('total')->storedAs('males + females')->after('first_timers');
            });
        }
    }

    public function down(): void
    {
        // Remove new columns
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
        });
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['center_id', 'males', 'females', 'first_timers', 'total']);
        });

        // Restore old columns
        Schema::table('attendance', function (Blueprint $table) {
            $table->unsignedInteger('member_id')->nullable()->after('id');
            $table->foreign('member_id')->references('id')->on('church_members')->nullOnDelete();
            $table->string('member_name', 255)->nullable()->after('member_id');
            $table->enum('status', ['present', 'absent', 'excused'])->after('service_type');
        });
    }
};

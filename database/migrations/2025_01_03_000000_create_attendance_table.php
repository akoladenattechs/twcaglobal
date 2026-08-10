<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('church_members')->nullOnDelete();
            $table->string('member_name', 255)->nullable();
            $table->date('service_date');
            $table->string('service_type', 100)->nullable();
            $table->enum('status', ['present', 'absent', 'excused']);
            $table->unsignedInteger('recorded_by');
            $table->text('notes');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};

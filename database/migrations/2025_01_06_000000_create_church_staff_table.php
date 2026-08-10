<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id');
            $table->foreign('member_id')->references('id')->on('church_members')->nullOnDelete();
            $table->string('position', 100);
            $table->string('department', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive']);
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('responsibilities');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_staff');
    }
};

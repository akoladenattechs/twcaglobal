<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address');
            $table->date('date_of_birth');
            $table->date('date_joined');
            $table->enum('membership_status', ['active', 'inactive', 'deceased']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('occupation', 100)->nullable();
            $table->string('emergency_contact', 100)->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->text('notes');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_members');
    }
};

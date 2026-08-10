<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('message_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('message_id');
            $table->foreign('message_id')->references('id')->on('contact_messages')->nullOnDelete();
            $table->string('reply_subject', 255);
            $table->text('reply_message');
            $table->unsignedInteger('sent_by');
            $table->timestamp('sent_at');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('message_replies');
    }
};

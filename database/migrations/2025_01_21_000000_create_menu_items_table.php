<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('menu_id');
            $table->foreign('menu_id')->references('id')->on('menus')->nullOnDelete();
            $table->unsignedInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('menus')->nullOnDelete();
            $table->string('title', 100);
            $table->string('url', 255);
            $table->enum('target', ['_self', '_blank']);
            $table->integer('order_number')->default(0);
            $table->enum('status', ['active', 'inactive']);
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->boolean('is_cta');
        });
        Schema::enableForeignKeyConstraints();
}

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make created_at / updated_at nullable in menus and menu_items so that
     * existing rows without timestamps don't violate NOT NULL constraints
     * when Laravel tries to fill them.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable(false)->change();
            $table->timestamp('updated_at')->nullable(false)->change();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable(false)->change();
            $table->timestamp('updated_at')->nullable(false)->change();
        });
    }
};

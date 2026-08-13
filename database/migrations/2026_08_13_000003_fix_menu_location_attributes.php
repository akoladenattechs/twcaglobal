<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure all menu records (like 'Main' or 'Main Navigation') have location set to 'main_menu'
     * so that any menu created by the admin or seeder is recognized by the header composer.
     */
    public function up(): void
    {
        DB::table('menus')
            ->whereNull('location')
            ->orWhere('location', '')
            ->orWhere('name', 'Main')
            ->orWhere('name', 'Main Navigation')
            ->orWhere('name', 'LIKE', '%main%')
            ->update(['location' => 'main_menu']);
    }

    public function down(): void
    {
        // No revert needed
    }
};

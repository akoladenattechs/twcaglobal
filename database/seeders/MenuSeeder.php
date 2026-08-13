<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the default "Main Menu" navigation with the site's standard links.
 * Safe to run multiple times — will skip if a main_menu record already exists.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            $this->command->warn('MenuSeeder: menus/menu_items tables not found — skipping.');
            return;
        }

        // Get or create main_menu
        $menu = DB::table('menus')->where('location', 'main_menu')->first();
        if (!$menu) {
            $menuId = DB::table('menus')->insertGetId([
                'name'          => 'Main Navigation',
                'location'      => 'main_menu',
                'display_order' => 0,
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $this->command->info("MenuSeeder: Created main_menu (id={$menuId}).");
        } else {
            $menuId = $menu->id;
        }

        // Only seed items if no items exist for this menu
        $itemsCount = DB::table('menu_items')->where('menu_id', $menuId)->count();
        if ($itemsCount > 0) {
            $this->command->info('MenuSeeder: menu_items already populated — skipping.');
            return;
        }

        // Default nav items (mirrors the static fallback in app.blade.php)
        $items = [
            ['title' => 'Home',           'url' => '/',                  'order_number' => 1,  'is_cta' => 0],
            ['title' => 'About',          'url' => '/about',             'order_number' => 2,  'is_cta' => 0],
            ['title' => 'Teachings',      'url' => '/teachings',         'order_number' => 3,  'is_cta' => 0],
            ['title' => 'Songs',          'url' => '/songs',             'order_number' => 4,  'is_cta' => 0],
            ['title' => 'Books',          'url' => '/bookstore',         'order_number' => 5,  'is_cta' => 0],
            ['title' => 'Give',           'url' => '/partnership-giving','order_number' => 6,  'is_cta' => 0],
            ['title' => 'Contact',        'url' => '/contact',           'order_number' => 7,  'is_cta' => 0],
            ['title' => 'Partner With Us','url' => '/partnership-giving','order_number' => 8,  'is_cta' => 1],
        ];

        foreach ($items as $item) {
            DB::table('menu_items')->insert([
                'menu_id'      => $menuId,
                'parent_id'    => null,
                'title'        => $item['title'],
                'url'          => $item['url'],
                'target'       => '_self',
                'order_number' => $item['order_number'],
                'status'       => 'active',
                'is_cta'       => $item['is_cta'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('MenuSeeder: Seeded ' . count($items) . ' menu items.');
    }
}

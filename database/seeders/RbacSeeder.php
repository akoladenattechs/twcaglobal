<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * All permissions organized by module.
     */
    private const PERMISSIONS = [
        'User Management' => [
            'access_admin' => 'Can access the admin panel',
            'view_dashboard' => 'Can view the admin dashboard',
            'view_users' => 'Can view user list',
            'create_users' => 'Can create new users',
            'edit_users' => 'Can edit existing users',
            'delete_users' => 'Can delete users',
            'manage_roles' => 'Can manage roles and permissions',
        ],
        'Homepage Management' => [
            'view_sliders' => 'Can view sliders',
            'manage_sliders' => 'Can manage sliders',
            'view_sections' => 'Can view homepage sections',
            'manage_sections' => 'Can manage homepage sections',
        ],
        'Content Management' => [
            'view_pages' => 'Can view pages',
            'manage_pages' => 'Can create/edit/delete pages',
            'view_books' => 'Can view books',
            'manage_books' => 'Can manage books',
            'view_events' => 'Can view events',
            'manage_events' => 'Can manage events',
            'view_sermons' => 'Can view sermons',
            'manage_sermons' => 'Can manage sermons',
            'view_devotionals' => 'Can view devotionals',
            'manage_devotionals' => 'Can create/edit/delete devotionals',
            'view_songs' => 'Can view songs',
            'manage_songs' => 'Can manage songs',
            'view_quotes' => 'Can view quotes',
            'manage_quotes' => 'Can manage quotes',
        ],
        'Contact Messages' => [
            'view_inbox' => 'Can view received contact messages',
            'manage_inbox' => 'Can reply to and manage contact messages',
        ],
        'Church Management' => [
            'view_members' => 'Can view member list',
            'manage_members' => 'Can add/edit/delete members',
            'view_attendance' => 'Can view attendance records',
            'manage_attendance' => 'Can manage attendance records',
            'view_offerings' => 'Can view offering records',
            'manage_offerings' => 'Can manage offering records',
            'view_staff' => 'Can view staff list',
            'manage_staff' => 'Can manage staff records',
        ],
        'Newsletter Management' => [
            'view_newsletters' => 'Can view newsletter subscribers and history',
            'send_newsletters' => 'Can send newsletters to subscribers',
        ],
        'Settings' => [
            'view_settings' => 'Can view settings',
            'manage_settings' => 'Can manage settings',
            'view_menus' => 'Can view menus',
            'manage_menus' => 'Can manage menus',
        ],
        'Audit Logs' => [
            'view_activity_logs' => 'Can view the audit logs page',
        ],
    ];

    /**
     * Church Admin permission names — the subset assigned to the non-super-admin role.
     */
    private const CHURCH_ADMIN_PERMISSIONS = [
        // User Management
        'access_admin',
        'view_dashboard',
        // Content Management
        'view_books',
        'manage_books',
        'view_events',
        'manage_events',
        'view_sermons',
        'manage_sermons',
        'view_devotionals',
        'manage_devotionals',
        'view_songs',
        'manage_songs',
        'view_quotes',
        'manage_quotes',
        // Contact Messages
        'view_inbox',
        'manage_inbox',
        // Church Management
        'view_members',
        'manage_members',
        'view_attendance',
        'manage_attendance',
        'view_offerings',
        'manage_offerings',
        'view_staff',
        'manage_staff',
        // Newsletter
        'view_newsletters',
        'send_newsletters',
    ];

    public function run(): void
    {
        $now = now();

        // ── 1. Insert or update all permissions ─────────────────────────────
        $inserted = [];
        foreach (self::PERMISSIONS as $module => $perms) {
            foreach ($perms as $name => $description) {
                $inserted[$name] = Permission::firstOrCreate(
                    ['name' => $name],
                    [
                        'description' => $description,
                        'module' => $module,
                        'created_at' => $now,
                    ]
                )->id;
            }
        }

        $this->command->info('✓ '.count($inserted).' permissions synced.');

        // ── 1b. Remove orphaned permissions (no longer defined) ────────────
        // Keeps the DB in sync with the PERMISSIONS const so removed features
        // (e.g. widgets, themes, media library, sermon series) don't linger.
        $definedNames = array_keys(array_merge(...array_values(self::PERMISSIONS)));
        $orphans = Permission::whereNotIn('name', $definedNames)->get();
        if ($orphans->isNotEmpty()) {
            foreach ($orphans as $orphan) {
                $orphan->roles()->detach();
                $orphan->delete();
            }
            $this->command->info('✓ Removed '.$orphans->count().' orphaned permissions: '.$orphans->pluck('name')->implode(', '));
        } else {
            $this->command->info('✓ No orphaned permissions to remove.');
        }

        // ── 2. Insert or update roles ───────────────────────────────────────
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            [
                'description' => 'Has full access to all features and can manage other users',
                'is_super_admin' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $churchAdmin = Role::firstOrCreate(
            ['name' => 'Church Admin'],
            [
                'description' => 'Can manage church members, staff, attendance, offerings, and content',
                'is_super_admin' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $accountantOfficer = Role::firstOrCreate(
            ['name' => 'Accountant Officer'],
            [
                'description' => 'Can view and manage financial records (offerings, transactions, campaigns, pledges)',
                'is_super_admin' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✓ Roles synced.');

        // ── 3. Sync role-permission assignments ─────────────────────────────
        // Super Admin gets ALL permissions
        $allPermissionIds = Permission::pluck('id', 'name');
        $superAdmin->permissions()->sync($allPermissionIds->values());

        // Church Admin gets the defined subset
        $churchAdminPermIds = collect(self::CHURCH_ADMIN_PERMISSIONS)
            ->map(fn ($name) => $allPermissionIds[$name] ?? null)
            ->filter()
            ->values()
            ->all();

        $churchAdmin->permissions()->sync($churchAdminPermIds);

        // Accountant Officer gets financial (offerings) access
        $accountantPermIds = collect(['access_admin', 'view_dashboard', 'view_offerings', 'manage_offerings'])
            ->map(fn ($name) => $allPermissionIds[$name] ?? null)
            ->filter()
            ->values()
            ->all();

        $accountantOfficer->permissions()->sync($accountantPermIds);

        $this->command->info('✓ '.count($churchAdminPermIds).' permissions assigned to Church Admin.');
        $this->command->info('✓ '.count($accountantPermIds).' permissions assigned to Accountant Officer.');
        $this->command->info('✓ RBAC seeding complete.');
    }
}

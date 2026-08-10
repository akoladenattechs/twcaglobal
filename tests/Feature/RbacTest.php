<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Insert a role (+ optional permissions) directly via the query builder.
     * See AdminAuthTest::makeRole() for the reasoning.
     */
    private function makeRole(string $name, bool $superAdmin = false, array $permissionNames = []): int
    {
        $roleId = DB::table('roles')->insertGetId([
            'name' => $name,
            'description' => $name,
            'is_super_admin' => $superAdmin ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($permissionNames as $permissionName) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $permissionName,
                'description' => $permissionName,
                'module' => 'test',
                'created_at' => now(),
            ]);
            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }

        return $roleId;
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $roleId = $this->makeRole('Super Admin', true);
        $user = User::factory()->create(['role_id' => $roleId]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_user_with_permission_can_access_dashboard(): void
    {
        $roleId = $this->makeRole('Editor', false, ['view_dashboard']);
        $user = User::factory()->create(['role_id' => $roleId]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        // Has a permission, just not view_dashboard.
        $roleId = $this->makeRole('Viewer', false, ['view_sermons']);
        $user = User::factory()->create(['role_id' => $roleId]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_inactive_user_is_denied_even_with_permission(): void
    {
        $roleId = $this->makeRole('Editor', false, ['view_dashboard']);
        $user = User::factory()->create(['role_id' => $roleId, 'status' => 'inactive']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }
}

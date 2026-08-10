<?php

namespace Tests\Feature;

use App\Mail\TwoFactorCodeMail;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Insert a role (+ optional permissions) directly via the query builder.
     * The Role/Permission models declare $timestamps = false while the tables
     * have NOT NULL timestamp columns, so Eloquent create() would fail on a
     * strict (SQLite) test database.
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

    private function makeSuperAdminUser(): User
    {
        $roleId = $this->makeRole('Super Admin', true);

        return User::factory()->create([
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'role_id' => $roleId,
        ]);
    }

    public function test_super_admin_login_requires_two_factor_and_logs_code_sent(): void
    {
        Mail::fake();

        $user = $this->makeSuperAdminUser();

        $this->get(route('admin.login'));

        $response = $this->post(route('admin.login.post'), [
            'username' => 'superadmin',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('admin.two-factor.verify'));
        $response->assertSessionHas('pending_2fa_user_id', $user->id);
        $response->assertSessionHas('pending_2fa_email', $user->email);
        $response->assertSessionHas('pending_2fa_remember', true);

        // Login must NOT be complete until the code is verified.
        $this->assertGuest();

        Mail::assertSent(TwoFactorCodeMail::class);

        $this->assertDatabaseHas('two_factor_codes', ['user_id' => $user->id]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => '2fa_sent',
        ]);
    }

    public function test_two_factor_verify_with_valid_code_completes_login_and_keeps_remember(): void
    {
        Mail::fake();

        $user = $this->makeSuperAdminUser();

        $this->post(route('admin.login.post'), [
            'username' => 'superadmin',
            'password' => 'password',
            'remember' => '1',
        ]);

        // Capture the plaintext OTP from the (faked) mailable.
        $otp = null;
        Mail::assertSent(TwoFactorCodeMail::class, function (TwoFactorCodeMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $this->assertNotNull($otp, 'OTP should be captured from the sent mail');

        $response = $this->post(route('admin.two-factor.verify.post'), ['otp' => $otp]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);

        // The remember-me flag ticked at login must survive the 2FA step —
        // a remember_web_* cookie should be issued on the verify response.
        $rememberCookies = collect($response->headers->getCookies())
            ->filter(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_'));

        $this->assertCount(1, $rememberCookies, 'Remember-me cookie should be issued after 2FA when remember was checked');
    }

    public function test_two_factor_verify_with_invalid_code_is_rejected_and_logged(): void
    {
        Mail::fake();

        $user = $this->makeSuperAdminUser();

        TwoFactorCode::create([
            'user_id' => $user->id,
            'token' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $pendingSession = [
            'pending_2fa_user_id' => $user->id,
            'pending_2fa_email' => $user->email,
            'pending_2fa_remember' => false,
        ];

        $this->withSession($pendingSession)->get(route('admin.two-factor.verify'));

        $response = $this->withSession($pendingSession)
            ->post(route('admin.two-factor.verify.post'), ['otp' => '999999']);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => '2fa_failed',
        ]);
    }

    public function test_login_with_invalid_credentials_is_rejected_and_logged(): void
    {
        $this->get(route('admin.login'));

        $response = $this->post(route('admin.login.post'), [
            'username' => 'ghost',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'action' => 'login_failed',
        ]);
    }

    public function test_non_super_admin_login_requires_two_factor(): void
    {
        Mail::fake();

        $roleId = $this->makeRole('Church Admin', false, ['access_admin', 'view_dashboard']);
        $user = User::factory()->create([
            'username' => 'churchadmin',
            'email' => 'churchadmin@example.com',
            'role_id' => $roleId,
        ]);

        $this->get(route('admin.login'));

        $response = $this->post(route('admin.login.post'), [
            'username' => 'churchadmin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.two-factor.verify'));
        $this->assertGuest();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewUserWelcomeMail;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    // ─── USERS ────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'add') {
                $validated = $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                ]);

                $plainPassword = $validated['password'];

                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'username' => $request->input('username'),
                    'email' => $validated['email'],
                    'password' => Hash::make($plainPassword),
                    'role_id' => $request->input('role_id') ?: null,
                    'status' => $request->input('status', 'active'),
                ]);

                // ── Send welcome email with login credentials ──
                try {
                    $settings = SiteSetting::getAllSettings();
                    $siteTitle = $settings['site_title'] ?? config('app.name');
                    $userName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->username;
                    $loginUrl = url('admin/login');

                    Mail::to($user->email)->send(new NewUserWelcomeMail(
                        $userName,
                        $siteTitle,
                        $user->username,
                        $plainPassword,
                        $loginUrl
                    ));
                } catch (\Exception $e) {
                    logger()->error('Failed to send new user welcome email: '.$e->getMessage());
                }
            } elseif ($action === 'edit' && $request->input('id')) {
                $user = User::findOrFail($request->input('id'));

                $validated = $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'username' => 'nullable|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email,'.$user->id,
                    'password' => 'nullable|string|min:8',
                ]);

                $data = [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'role_id' => $request->input('role_id') ?: null,
                    'status' => $request->input('status', 'active'),
                ];
                if (! empty($validated['password'])) {
                    $data['password'] = Hash::make($validated['password']);
                }
                $user->update($data);
            } elseif ($action === 'delete' && $request->input('id')) {
                User::destroy($request->input('id'));
            }

            return redirect()->route('admin.users');
        }

        $userToEdit = null;
        if ($request->input('action') === 'edit' && $request->input('id')) {
            $userToEdit = User::findOrFail($request->input('id'));
        }

        $users = User::with('role')->orderBy('first_name', 'asc')->get();
        $roles = Role::orderBy('name', 'asc')->get();

        return view('admin.users', compact('users', 'roles', 'userToEdit'));
    }

    // ─── ROLES ────────────────────────────────────────────────────────────────

    public function roles(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'add') {
                $request->validate(['name' => 'required|string|max:255']);
                $role = Role::create([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                ]);
                if ($request->has('permissions')) {
                    $role->permissions()->sync($request->input('permissions'));
                }
            } elseif ($action === 'edit' && $request->input('id')) {
                $role = Role::findOrFail($request->input('id'));
                $role->update([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                ]);
                $role->permissions()->sync($request->input('permissions', []));
            } elseif ($action === 'delete' && $request->input('id')) {
                $role = Role::withCount('users')->findOrFail($request->input('id'));
                if ($role->users_count > 0) {
                    return redirect()->route('admin.roles')->with('error', 'Cannot delete role "' . $role->name . '" because it has ' . $role->users_count . ' user(s) assigned. Reassign or remove those users first.');
                }
                $role->permissions()->detach();
                $role->delete();
            }

            return redirect()->route('admin.roles');
        }

        $roles = Role::with('permissions')->withCount(['users', 'permissions'])->orderBy('name', 'asc')->get();
        $permissions = Permission::orderBy('name', 'asc')->get();
        $permissionsByModule = $permissions->groupBy(function ($perm) {
            $parts = explode('-', $perm->slug ?? $perm->name);

            return $parts[0] ?? 'general';
        });

        $rolePermissionIds = $roles->mapWithKeys(function ($role) {
            return [$role->id => $role->permissions->pluck('id')->toArray()];
        });

        return view('admin.roles', compact('roles', 'permissionsByModule', 'rolePermissionIds'));
    }

    // ─── MENUS ────────────────────────────────────────────────────────────────

    public function menus(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'add_menu') {
                Menu::create([
                    'name' => $request->input('name'),
                    'location' => $request->input('location'),
                    'display_order' => (int) $request->input('display_order', 0),
                    'status' => $request->input('status', 'active'),
                ]);
            } elseif ($action === 'edit_menu' && $request->input('id')) {
                $menu = Menu::findOrFail($request->input('id'));
                $menu->update([
                    'name' => $request->input('name'),
                    'location' => $request->input('location'),
                    'display_order' => (int) $request->input('display_order', 0),
                    'status' => $request->input('status', 'active'),
                ]);
            } elseif ($action === 'delete_menu' && $request->input('id')) {
                MenuItem::where('menu_id', $request->input('id'))->delete();
                Menu::destroy($request->input('id'));
            } elseif ($action === 'add_item' && $request->input('menu_id')) {
                MenuItem::create([
                    'menu_id' => $request->input('menu_id'),
                    'parent_id' => $request->input('parent_id') ?: null,
                    'title' => $request->input('title'),
                    'url' => $request->input('url'),
                    'target' => $request->input('target', '_self'),
                    'order_number' => (int) $request->input('order_number', 0),
                    'status' => $request->input('status', 'active'),
                    'is_cta' => $request->boolean('is_cta'),
                ]);
            } elseif ($action === 'edit_item' && $request->input('item_id')) {
                $item = MenuItem::findOrFail($request->input('item_id'));
                $item->update([
                    'menu_id' => $request->input('menu_id'),
                    'parent_id' => $request->input('parent_id') ?: null,
                    'title' => $request->input('title'),
                    'url' => $request->input('url'),
                    'target' => $request->input('target', '_self'),
                    'order_number' => (int) $request->input('order_number', 0),
                    'status' => $request->input('status', 'active'),
                    'is_cta' => $request->boolean('is_cta'),
                ]);
            } elseif ($action === 'delete_item' && $request->input('item_id')) {
                MenuItem::destroy($request->input('item_id'));
            }

            return redirect()->route('admin.menus');
        }

        $menus = Menu::with('menuItems')->orderBy('name', 'asc')->get();
        $allMenuItems = MenuItem::with('children')->orderBy('parent_id')->orderBy('order_number')->get();

        return view('admin.menus', compact('menus', 'allMenuItems'));
    }

    // ─── SITE SETTINGS ────────────────────────────────────────────────────────

    public function siteSettings(Request $request)
    {
        if ($request->isMethod('POST')) {
            // Validate uploaded logo/favicon files before they are stored in the public webroot.
            // Without this, arbitrary file types (e.g. SVG, HTML, PHP) could be served to visitors.
            $request->validate([
                'appearance.logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'appearance.favicon_file' => 'nullable|mimes:ico,png,jpg,jpeg,gif,webp|max:512',
                'appearance.devotional_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'seo.og_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Handle file uploads for logo and favicon
            $appearanceData = $request->input('appearance', []);

            if ($request->hasFile('appearance.logo_file') && $request->file('appearance.logo_file')->isValid()) {
                $file = $request->file('appearance.logo_file');
                $filename = uniqid().'_logo.'.$file->getClientOriginalExtension();
                $path = Storage::disk('r2')->putFileAs('logos', $file, $filename, 'public');
                $appearanceData['logo'] = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
            }

            if ($request->hasFile('appearance.favicon_file') && $request->file('appearance.favicon_file')->isValid()) {
                $file = $request->file('appearance.favicon_file');
                $filename = uniqid().'_favicon.'.$file->getClientOriginalExtension();
                $path = Storage::disk('r2')->putFileAs('logos', $file, $filename, 'public');
                $appearanceData['favicon'] = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
            }

            if ($request->hasFile('appearance.devotional_logo_file') && $request->file('appearance.devotional_logo_file')->isValid()) {
                $file = $request->file('appearance.devotional_logo_file');
                $filename = uniqid().'_devo_logo.'.$file->getClientOriginalExtension();
                $path = Storage::disk('r2')->putFileAs('logos', $file, $filename, 'public');
                $appearanceData['devotional_logo'] = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
            }

            // Remove file inputs from the data to avoid saving them as settings
            unset($appearanceData['logo_file'], $appearanceData['favicon_file'], $appearanceData['devotional_logo_file']);

            // Handle SEO OG image file upload if provided
            $seoData = $request->input('seo', []);
            if ($request->hasFile('seo.og_image_file') && $request->file('seo.og_image_file')->isValid()) {
                $file = $request->file('seo.og_image_file');
                $filename = uniqid().'_og_image.'.$file->getClientOriginalExtension();
                $path = Storage::disk('r2')->putFileAs('seo', $file, $filename, 'public');
                $seoData['og_image'] = rtrim(config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
            }
            unset($seoData['og_image_file']);

            // Save SEO group data
            if (! empty($seoData) && is_array($seoData)) {
                foreach ($seoData as $key => $value) {
                    SiteSetting::updateOrCreate(
                        ['setting_key' => $key, 'setting_group' => 'seo'],
                        ['setting_value' => $value ?? '']
                    );
                }
            }

            // Groups that can be saved
            $groups = ['general', 'contact', 'social', 'typography', 'currency', 'mail'];

            foreach ($groups as $group) {
                $groupData = $request->input($group);
                if (! empty($groupData) && is_array($groupData)) {
                    // Skip secret fields that are managed via .env for security
                    if ($group === 'mail') {
                        unset($groupData['smtp_password'], $groupData['resend_api_key']);
                    }
                    if ($group === 'general') {
                        unset(
                            $groupData['paystack_secret_key'],
                            $groupData['flutterwave_secret_key'],
                            $groupData['stripe_secret_key']
                        );
                    }
                    foreach ($groupData as $key => $value) {
                        SiteSetting::updateOrCreate(
                            ['setting_key' => $key, 'setting_group' => $group],
                            ['setting_value' => $value ?? '']
                        );
                    }
                }
            }

            // Save appearance separately (since we modified it)
            foreach ($appearanceData as $key => $value) {
                SiteSetting::updateOrCreate(
                    ['setting_key' => $key, 'setting_group' => 'appearance'],
                    ['setting_value' => $value ?? '']
                );
            }

            // ── Send test email (also saves settings first) ──
            if ($request->has('send_test_email')) {
                $testEmail = $request->input('test_email');

                if (! filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                    return redirect()->route('admin.site-settings')->with('error', 'Invalid email address.');
                }

                try {
                    Mail::raw(
                        'This is a test email to verify your SMTP settings.',
                        function ($message) use ($testEmail) {
                            $message->to($testEmail)
                                ->subject('Test Email from '.(SiteSetting::getSettingsByGroup('general')['site_title'] ?? config('app.name')));
                        }
                    );

                    return redirect()->route('admin.site-settings')->with('success', 'Test email sent successfully!');
                } catch (\Exception $e) {
                    return redirect()->route('admin.site-settings')->with('error', 'Failed to send test email: '.$e->getMessage());
                }
            }

            return redirect()->route('admin.site-settings')->with('success', 'Settings updated successfully!');
        }

        // Fetch all settings, grouped: ['general' => ['site_title' => '...', ...], ...]
        $settings = SiteSetting::all()->groupBy('setting_group')->map(function ($items) {
            return $items->pluck('setting_value', 'setting_key')->toArray();
        })->toArray();

        return view('admin.site-settings', compact('settings'));
    }
}

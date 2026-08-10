<?php
// Temporary script to create Superadmin user account.
// DELETE THIS FILE IMMEDIATELY AFTER USE!

$basePath = file_exists(__DIR__ . '/../artisan') ? dirname(__DIR__) : __DIR__;
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$request = Illuminate\Http\Request::capture();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required.';
    } else {
        try {
            $superAdminRole = \App\Models\Role::where('is_super_admin', true)->first();
            if (! $superAdminRole) {
                // Ensure roles are seeded
                $kernel->call('db:seed', ['--force' => true]);
                $superAdminRole = \App\Models\Role::where('is_super_admin', true)->first();
            }

            $user = \App\Models\User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => \Illuminate\Support\Facades\Hash::make($password),
                    'role_id' => $superAdminRole->id ?? 1,
                    'status' => 'active',
                    'is_active' => true,
                ]
            );

            $success = true;
            $message = "Superadmin account '{$username}' created successfully! You can now log into the Admin Panel.";
        } catch (\Throwable $e) {
            $message = 'Error creating user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Superadmin Account</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width: 100%; max-width: 420px; }
        h2 { margin-top: 0; color: #38bdf8; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-error { background: #7f1d1d; color: #fca5a5; }
        .alert-success { background: #064e3b; color: #6ee7b7; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.85rem; color: #94a3b8; }
        input { width: 100%; padding: 0.65rem 0.8rem; border: 1px solid #334155; background: #0f172a; color: #fff; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; }
        input:focus { border-color: #38bdf8; outline: none; }
        button { width: 100%; padding: 0.75rem; background: #0284c7; color: white; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; }
        button:hover { background: #0369a1; }
        .warning { font-size: 0.8rem; color: #fbbf24; margin-top: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Superadmin</h2>
        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (! $success): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Super Admin" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="admin" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="admin@twcaglobal.org" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••••••" required>
                </div>
                <button type="submit">Create Superadmin Account</button>
            </form>
        <?php else: ?>
            <p><a href="/admin/login" style="color: #38bdf8;">Go to Admin Login →</a></p>
        <?php endif; ?>
        <div class="warning">⚠️ Delete this file (`public/create-admin.php`) from cPanel immediately after creating your account!</div>
    </div>
</body>
</html>

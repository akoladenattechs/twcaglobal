<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ $siteSettings['site_title'] ?? config('app.name') }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/admin.css') }}?v={{ filemtime(public_path('admin/assets/css/admin.css')) }}">
    @php
        $pc = $siteSettings['primary_color'] ?? '#ce0f3d';
        $sc = $siteSettings['secondary_color'] ?? '#343a40';
        $pcLight = $pc . '18';
        $pcLight50 = $pc . '80';
    @endphp
    <style>
        :root {
            --primary-color: {{ $pc }};
            --secondary-color: {{ $sc }};
            --primary-color-light: {{ $pcLight }};
            --primary-color-light-50: {{ $pcLight50 }};
        }
    </style>
    @php $faviconPath = $siteSettings['favicon'] ?? ''; @endphp
    @if($faviconPath)
        <link rel="icon" type="image/png" href="{{ \App\Helpers\HtmlHelper::assetUrl($faviconPath) }}">
    @endif
</head>
<body class="login-page">
    <div class="container login-container">
        <div class="card">
            <div class="card-header">
                @php $logoPath = $siteSettings['logo'] ?? ''; @endphp
                <img src="{{ $logoPath ? \App\Helpers\HtmlHelper::assetUrl($logoPath) : asset('admin/logo.png') }}" alt="{{ $siteSettings['site_title'] ?? config('app.name') }} Logo" class="logo">
                <h4 class="mb-0">Set New Password</h4>
                <p class="mb-0 text-muted forgot-subtitle">Choose a strong password for your account</p>
            </div>
            <div class="card-body">
                {{-- Step indicator --}}
                <div class="step-indicator">
                    <span class="step-dot done" title="Step 1: Email"></span>
                    <span class="step-dot done" title="Step 2: OTP"></span>
                    <span class="step-dot active" title="Step 3: Reset"></span>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.forgot-password.reset') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    {{-- Honeypot field — must remain empty (anti-bot) --}}
                    <div class="honeypot-field">
                        <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password" class="text-muted forgot-subtitle">New Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Min. 8 characters" required minlength="8"
                                   autocomplete="new-password">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="form-text text-muted">
                            Use at least 8 characters with a mix of letters, numbers &amp; symbols.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="text-muted forgot-subtitle">Confirm New Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                   placeholder="Repeat your password" required minlength="8"
                                   autocomplete="new-password">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login btn-block text-white">
                        <i class="fas fa-save mr-2"></i>Reset Password
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('admin.login') }}" class="text-muted forgot-subtitle">
                        <i class="fas fa-sign-in-alt mr-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Simple password strength indicator
            $('#password').on('input', function() {
                var val = $(this).val();
                var strength = 0;
                var bar = $('#passwordStrength');

                if (val.length >= 8) strength += 25;
                if (val.match(/[a-z]/) && val.match(/[A-Z]/)) strength += 25;
                if (val.match(/\d/)) strength += 25;
                if (val.match(/[^a-zA-Z0-9]/)) strength += 25;

                bar.css('width', strength + '%');
                if (strength < 25) {
                    bar.css('background', '#dc3545');
                } else if (strength < 50) {
                    bar.css('background', '#ffc107');
                } else if (strength < 75) {
                    bar.css('background', '#17a2b8');
                } else {
                    bar.css('background', '#28a745');
                }
            });
        });
    </script>
</body>
</html>

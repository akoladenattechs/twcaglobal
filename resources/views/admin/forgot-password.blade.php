<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - {{ $siteSettings['site_title'] ?? config('app.name') }}</title>
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
                <h4 class="mb-0">Forgot Password</h4>
                <p class="mb-0 text-muted forgot-subtitle">Enter your email to receive a reset OTP</p>
            </div>
            <div class="card-body">
                {{-- Step indicator --}}
                <div class="step-indicator">
                    <span class="step-dot active" title="Step 1: Email"></span>
                    <span class="step-dot" title="Step 2: OTP"></span>
                    <span class="step-dot" title="Step 3: Reset"></span>
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

                <form method="POST" action="{{ route('admin.forgot-password.send') }}">
                    @csrf
                    {{-- Honeypot field — must remain empty (anti-bot) --}}
                    <div class="honeypot-field">
                        <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="email" class="text-muted forgot-subtitle">Email Address</label>
                        <div class="position-relative">
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Enter your email address" required autofocus autocomplete="email">
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login btn-block text-white">
                        <i class="fas fa-paper-plane mr-2"></i>Send OTP
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('admin.login') }}" class="text-muted forgot-subtitle">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

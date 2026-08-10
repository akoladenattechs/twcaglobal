<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - {{ $siteSettings['site_title'] ?? config('app.name') }}</title>
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
                <h4 class="mb-0">Admin Login - {{ $siteSettings['site_title'] ?? config('app.name') }}</h4>
            </div>
            <div class="card-body">
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

                <form method="POST" action="{{ route('admin.login.post') }}">
                    @csrf
                    <div class="form-group">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Username" required autofocus>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <div class="form-group d-flex align-items-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember" name="remember" value="1">
                            <label class="custom-control-label text-muted" for="remember">Remember me</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login btn-block text-white">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.forgot-password') }}" class="text-muted text-size-sm">
                            <i class="fas fa-key mr-1"></i> Forgot Password?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification - {{ $siteSettings['site_title'] ?? config('app.name') }}</title>
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
                <h4 class="mb-0">Two-Factor Verification</h4>
                <p class="mb-0 text-muted forgot-subtitle">Enter the 6-digit code sent to your email</p>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <p class="text-muted text-center mb-3 forgot-small">
                    Verification code sent to <strong>{{ $email }}</strong>
                </p>

                <form method="POST" action="{{ route('admin.two-factor.verify.post') }}" id="verifyForm">
                    @csrf
                    {{-- Honeypot field — must remain empty (anti-bot) --}}
                    <div class="honeypot-field">
                        <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="otp" class="text-muted forgot-subtitle">One-Time Password (OTP)</label>
                        <input type="text" class="form-control text-center otp-input" id="otp" name="otp"
                               placeholder="Enter 6-digit code" required autofocus
                               maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                               autocomplete="one-time-code">
                    </div>
                    <button type="submit" id="verifyBtn" class="btn btn-login btn-block text-white">
                        <i class="fas fa-check-circle mr-2"></i>Verify &amp; Sign In
                    </button>
                </form>

                <div class="text-center mt-3">
                    <form method="POST" action="{{ route('admin.two-factor.resend') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm text-muted p-0 forgot-small">
                            <i class="fas fa-redo mr-1"></i> Resend Code
                        </button>
                    </form>
                    <span class="mx-2 text-muted">|</span>
                    <a href="{{ route('admin.login') }}" class="text-muted forgot-small">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit when 6 digits are entered, with a guard that prevents
        // duplicate submissions (auto-submit racing with button click/Enter).
        $(document).ready(function() {
            var submitting = false;

            function lockForm() {
                if (submitting) return;
                submitting = true;
                $('#otp').prop('readonly', true);
                $('#verifyBtn').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-2"></i>Verifying...');
            }

            $('#verifyForm').on('submit', function(e) {
                if (submitting) {
                    e.preventDefault();
                    return false;
                }
                lockForm();
            });

            $('#otp').on('input', function() {
                var val = $(this).val().replace(/\D/g, '');
                $(this).val(val);
                if (val.length === 6 && !submitting) {
                    // Let the submit handler lock the form. Calling lockForm()
                    // here would set submitting=true first, causing the submit
                    // handler to cancel the request before it leaves the page.
                    $(this).closest('form').submit();
                }
            });
        });
    </script>
</body>
</html>

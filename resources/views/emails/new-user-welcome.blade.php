<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Created</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 520px; margin: 30px auto; padding: 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 30px; }
        .detail-box { background: #f8f9fa; border-radius: 6px; padding: 16px; margin: 16px 0; font-size: 14px; }
        .detail-box strong { display: inline-block; min-width: 100px; }
        .btn { display: inline-block; padding: 12px 24px; font-size: 15px; color: #fff; background: {{ $primaryColor }}; text-decoration: none; border-radius: 5px; margin: 8px 0; }
        .btn:hover { background: {{ $secondaryColor }}; }
        .warning { font-size: 13px; color: #888; margin-top: 16px; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; }
        .footer a { color: {{ $primaryColor }}; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ $siteTitle }}</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $userName }}</strong>,</p>
            <p>An admin account has been created for you at <strong>{{ $siteTitle }}</strong>. You can now log in using the credentials below.</p>

            <div class="detail-box">
                <p><strong>Login URL:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
                <p><strong>Username:</strong> {{ $username }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
            </div>

            <p style="text-align: center;">
                <a class="btn" href="{{ $loginUrl }}">Log In to Your Account</a>
            </p>

            <p class="warning">
                <strong>Security Tip:</strong> For your security, please change your password after your first login. If you did not request this account, please contact the site administrator immediately.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 520px; margin: 30px auto; padding: 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 30px; }
        .detail-box { background: #f8f9fa; border-radius: 6px; padding: 16px; margin: 16px 0; font-size: 14px; }
        .detail-box strong { display: inline-block; min-width: 100px; }
        .warning { font-size: 13px; color: #888; margin-top: 16px; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; }
        .footer a { color: {{ $primaryColor }}; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Changed Successfully</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $userName }}</strong>,</p>
            <p>The password for your admin account at <strong>{{ $siteTitle }}</strong> was just changed successfully.</p>

            <div class="detail-box">
                <p><strong>Account:</strong> {{ $siteTitle }}</p>
                <p><strong>Date &amp; Time:</strong> {{ $timestamp }}</p>
                <p><strong>IP Address:</strong> {{ $ipAddress }}</p>
            </div>

            <p>If you initiated this change, no further action is needed. You can now log in with your new password.</p>

            <p class="warning">
                <strong>Didn't request this?</strong> If you did not change your password, your account may have been compromised. Please contact the site administrator immediately and secure your account.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</body>
</html>

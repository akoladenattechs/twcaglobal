<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Login Notification</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 520px; margin: 30px auto; padding: 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .header { color: #fff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 30px; }
        .detail-box { background: #f8f9fa; border-radius: 6px; padding: 16px; margin: 16px 0; font-size: 14px; }
        .detail-box strong { display: inline-block; min-width: 100px; }
        .warning { font-size: 13px; color: #888; margin-top: 16px; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; }

    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: {{ $primaryColor }};">
            <h1>New Login to Your Account</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $userName }}</strong>,</p>
            <p>Your admin account at <strong>{{ $siteTitle }}</strong> was just accessed via a new login.</p>

            <div class="detail-box">
                <p><strong>Account:</strong> {{ $siteTitle }}</p>
                <p><strong>Date &amp; Time:</strong> {{ $timestamp }}</p>
                <p><strong>IP Address:</strong> {{ $ipAddress }}</p>
            </div>

            <p>If this was you, no further action is needed. You can safely ignore this email.</p>

            <p class="warning">
                <strong>Didn't recognize this?</strong> If you did not log into your account, someone else may have accessed it. Please contact the site administrator immediately and change your password.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</body>
</html>

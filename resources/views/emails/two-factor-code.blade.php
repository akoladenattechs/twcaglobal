<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Verification - {{ $siteTitle }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 520px; margin: 30px auto; padding: 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 30px; }
        .otp-box { background: #f8f9fa; border: 2px dashed {{ $primaryColor }}; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .otp-code { font-size: 36px; font-weight: 800; letter-spacing: 6px; color: {{ $primaryColor }}; font-family: 'Courier New', monospace; }
        .warning { font-size: 13px; color: #888; margin-top: 16px; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; }
        .footer a { color: {{ $primaryColor }}; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Login Verification</h1>
        </div>
        <div class="body">
            <p>Hello,</p>
            <p>A sign-in to your admin account at <strong>{{ $siteTitle }}</strong> was just attempted.</p>
            <p>Use the One-Time Password (OTP) below to complete the login. This code is valid for <strong>10 minutes</strong>.</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <p>If this wasn't you, someone may have your password. Please sign in and change your password immediately.</p>

            <p class="warning">
                <strong>Security Notice:</strong> Never share this OTP with anyone. Our team will never ask for your password or OTP.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</body>
</html>

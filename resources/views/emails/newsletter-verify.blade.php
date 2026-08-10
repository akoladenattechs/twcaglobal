<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm your subscription</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 25px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 25px 20px; background: #f9f9f9; text-align: center; }
        .button { display: inline-block; padding: 14px 32px; background: {{ $primaryColor }}; color: #fff !important; text-decoration: none; border-radius: 5px; font-size: 16px; margin: 20px 0; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
        .note { font-size: 13px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirm your subscription</h1>
        </div>
        <div class="body">
            <h2>Thanks for subscribing!</h2>
            <p>You're almost there! Please confirm your email address by clicking the button below to start receiving our newsletter.</p>

            <a href="{{ $verificationUrl }}" class="button">Confirm Subscription</a>

            <p class="note">If you didn't subscribe to our newsletter, you can safely ignore this email.</p>
            <p class="note">This link expires in 48 hours.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $siteName ?? config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

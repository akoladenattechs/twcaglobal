<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Contacting Us</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 520px; margin: 30px auto; padding: 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 30px; }
        .body p { margin: 0 0 14px; }
        .acknowledgement { background: #f8f9fa; border-left: 4px solid {{ $primaryColor }}; padding: 16px 20px; margin: 20px 0; border-radius: 4px; }
        .original-message { background: #f8f9fa; border-radius: 6px; padding: 16px; margin: 20px 0; font-size: 13px; color: #555; }
        .original-message strong { color: #333; }
        .footer { text-align: center; padding: 20px 30px; font-size: 12px; color: #aaa; border-top: 1px solid #eee; }
        .footer a { color: {{ $primaryColor }}; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thank You for Contacting Us</h1>
        </div>
        <div class="body">
            <p>Dear <strong>{{ $contactMessage->name }}</strong>,</p>

            <p>Thank you for reaching out to <strong>{{ $siteTitle }}</strong>.</p>

            <div class="acknowledgement">
                <p><strong>Your message has been received.</strong></p>
                <p>We appreciate you taking the time to write to us. A member of our team will review your message and get back to you as soon as possible. Please allow up to 24–48 hours for a response during business days.</p>
            </div>

            <p>If your matter is urgent, please contact the church office directly during office hours.</p>

            <div class="original-message">
                <p><strong>Your original message:</strong></p>
                <p><strong>Subject:</strong> {{ $contactMessage->subject ?: '(No subject)' }}</p>
                <p>{!! nl2br(e($contactMessage->message)) !!}</p>
            </div>

            <p>Blessings,<br>
            <strong>{{ $siteTitle }}</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</body>
</html>

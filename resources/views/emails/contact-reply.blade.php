<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $replySubject }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 20px; background: #f9f9f9; }
        .original-message { background: #fff; border-left: 4px solid #ccc; padding: 15px; margin-top: 20px; font-size: 13px; color: #666; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $replySubject }}</h1>
        </div>
        <div class="body">
            <p>Dear {{ $originalMessage->name }},</p>
            
            {!! nl2br(e($replyMessage)) !!}
            
            <p>Blessings,<br>
            {{ $siteTitle }}</p>
            
            <div class="original-message">
                <p><strong>Your original message:</strong></p>
                <p><strong>Subject:</strong> {{ $originalMessage->subject }}</p>
                <p>{!! nl2br(e($originalMessage->message)) !!}</p>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
        </div>
    </div>
</body>
</html>

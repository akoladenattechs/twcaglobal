<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $processedSubject }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $primaryColor }}; color: #fff; padding: 25px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 25px 20px; background: #f9f9f9; }
        .body h2 { color: {{ $primaryColor }}; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; }
        .unsubscribe { margin-top: 20px; padding: 10px; background: #eee; border-radius: 4px; text-align: center; font-size: 12px; color: #666; }
        .unsubscribe a { color: {{ $primaryColor }}; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $processedSubject }}</h1>
        </div>
        <div class="body">
            @if($subscriber->name)
            <p>Dear {{ $subscriber->name }},</p>
            @else
            <p>Hello,</p>
            @endif

            {!! $processedContent !!}

            <p>Blessings,<br>
            {{ $siteTitle ?? config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $siteTitle ?? config('app.name') }}. All rights reserved.</p>
            <div class="unsubscribe">
                You are receiving this email because you subscribed to our newsletter.
                <br><a href="{{ $unsubscribeUrl ?? '#' }}">Unsubscribe</a>
            </div>
        </div>
    </div>
    {{-- Tracking pixel: always injected automatically --}}
    @if($newsletter->id && $subscriber->id)
    @php
        $trackPublicBase = rtrim(config('app.newsletter_public_url', config('app.url')), '/');
        $trackPixelSrc = $trackPublicBase . '/newsletter/track-open/' . $newsletter->id . '/' . $subscriber->id;
    @endphp
    <img src="{{ $trackPixelSrc }}" alt="" width="1" height="1" style="display:none;border:0;outline:none;" />
    @endif
</body>
</html>

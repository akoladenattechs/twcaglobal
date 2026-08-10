<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Maintenance Mode' }} - {{ config('app.name', 'TWCA Church') }}</title>
    @if(!empty($favicon))
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }
        .bg-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            pointer-events: none;
        }
        .maintenance-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }
        .icon-box {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px auto;
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.4);
            animation: pulse 3s infinite;
            overflow: hidden;
        }
        .icon-box i {
            font-size: 38px;
            color: #ffffff;
        }
        .icon-box .favicon-img {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(2, 132, 199, 0); }
            100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(14, 165, 233, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .badge .dot {
            width: 8px;
            height: 8px;
            background-color: #38bdf8;
            border-radius: 50%;
            display: inline-block;
            animation: blink 1.5s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 16px;
            line-height: 1.3;
        }
        p {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        .footer-note {
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 24px;
            margin-top: 10px;
        }
        .admin-link {
            color: #38bdf8;
            text-decoration: none;
            transition: color 0.2s;
        }
        .admin-link:hover {
            color: #7dd3fc;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="maintenance-card">
        <div class="icon-box">
            @if(!empty($favicon))
                <img src="{{ $favicon }}" alt="App Favicon" class="favicon-img">
            @elseif(!empty($logo))
                <img src="{{ $logo }}" alt="App Logo" class="favicon-img">
            @else
                <i class="fas fa-tools"></i>
            @endif
        </div>

        <div class="badge">
            <span class="dot"></span>
            Scheduled Maintenance
        </div>

        <h1>{{ $title ?? 'We\'ll Be Back Soon!' }}</h1>

        <p>{{ $message ?? 'Our site is currently undergoing scheduled maintenance to improve your experience. Please check back shortly.' }}</p>

        <div class="footer-note">
            &copy; {{ date('Y') }} {{ config('app.name', 'TWCA Church') }}. All rights reserved.
        </div>
    </div>
</body>
</html>

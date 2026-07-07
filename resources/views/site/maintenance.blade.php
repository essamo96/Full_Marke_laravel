@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?: 'FULL MARK ACADEMY' }}</title>
  <link rel="icon" type="image/png" href="{{ asset('site/images/logo_v2_gold.png') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('site/css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/gold.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}">

  <style>
    :root {
      --gold-main: #c5a880;
      --gold-light: #e8d0ad;
      --bg-dark: #0a0a0a;
      --panel-bg: rgba(20, 20, 20, 0.7);
    }
    body {
      margin: 0;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at top, #1a1a1a 0%, var(--bg-dark) 100%);
      color: #f5f0e6;
      font-family: 'Inter', sans-serif;
      text-align: center;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
    }
    [dir="rtl"] body { font-family: 'Tajawal', sans-serif; }
    
    /* Background effects */
    .bg-blur {
      position: absolute;
      width: 400px;
      height: 400px;
      background: var(--gold-main);
      filter: blur(150px);
      opacity: 0.15;
      border-radius: 50%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 0;
      animation: pulse 6s infinite alternate;
    }
    
    @keyframes pulse {
      0% { transform: translate(-50%, -50%) scale(1); opacity: 0.1; }
      100% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.2; }
    }

    .maintenance-card {
      position: relative;
      z-index: 1;
      max-width: 650px;
      width: 100%;
      background: var(--panel-bg);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(197, 168, 128, 0.15);
      border-radius: 24px;
      padding: 4rem 2rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .logo-wrapper {
      margin-bottom: 2rem;
    }

    .maintenance-card img {
      height: 100px;
      object-fit: contain;
      filter: drop-shadow(0 0 10px rgba(197, 168, 128, 0.2));
    }

    .icon-wrapper {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: rgba(197, 168, 128, 0.1);
      color: var(--gold-main);
      font-size: 2.5rem;
      margin-bottom: 1.5rem;
      border: 1px solid rgba(197, 168, 128, 0.2);
      animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    .maintenance-card h1 {
      font-size: 2.2rem;
      font-weight: 800;
      margin-bottom: 1rem;
      background: linear-gradient(to right, var(--gold-light), var(--gold-main));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      line-height: 1.3;
    }

    .message-content {
      font-size: 1.1rem;
      opacity: 0.85;
      line-height: 1.8;
      max-width: 500px;
      margin: 0 auto;
      color: #e0e0e0;
    }
    
    .message-content p {
      margin-bottom: 0;
    }

    /* Small decorative line */
    .divider {
      width: 50px;
      height: 3px;
      background: var(--gold-main);
      margin: 2rem auto;
      border-radius: 2px;
      opacity: 0.5;
    }
  </style>
</head>
<body>
  <div class="bg-blur"></div>
  <div class="maintenance-card">
    <div class="logo-wrapper">
      <img src="{{ asset('site/images/logo_v2_gold.png') }}" alt="FULL MARK ACADEMY">
    </div>
    
    <div class="icon-wrapper">
      <i class="bi bi-gear-fill"></i>
    </div>
    
    <h1>{{ $title ?: ($isRtl ? 'الموقع تحت الصيانة حاليًا' : 'Site Under Maintenance') }}</h1>
    
    <div class="divider"></div>
    
    <div class="message-content">
      {!! $message ?: ($isRtl ? '<p>نعمل حاليًا على إجراء بعض التحديثات الهامة للارتقاء بتجربة المستخدم. يرجى زيارتنا مرة أخرى بعد قليل.</p>' : '<p>We are currently performing scheduled maintenance to improve your experience. Please check back shortly.</p>') !!}
    </div>
  </div>
</body>
</html>

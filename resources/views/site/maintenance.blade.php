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
    body {
      margin: 0;
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--bg-primary, #060606);
      color: var(--text-primary, #f5f0e6);
      font-family: var(--font-en, 'Inter', sans-serif);
      text-align: center;
      padding: 2rem;
    }
    [dir="rtl"] body { font-family: var(--font-ar, 'Tajawal', sans-serif); }
    .maintenance-card { max-width: 560px; }
    .maintenance-card img { height: 90px; margin-bottom: 1.5rem; }
    .maintenance-card i { font-size: 2.5rem; color: var(--accent-color, #c5a880); margin-bottom: 1rem; }
    .maintenance-card h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: .75rem; }
    .maintenance-card p { opacity: .8; line-height: 1.7; }
  </style>
</head>
<body>
  <div class="maintenance-card">
    <img src="{{ asset('site/images/logo_v2_gold.png') }}" alt="FULL MARK ACADEMY">
    <i class="bi bi-tools"></i>
    <h1>{{ $title ?: ($isRtl ? 'الموقع تحت الصيانة حاليًا' : 'Site Under Maintenance') }}</h1>
    <p>{{ $message ?: ($isRtl ? 'نعمل حاليًا على تحسين الموقع. يرجى زيارتنا مرة أخرى بعد قليل.' : 'We are currently performing scheduled maintenance. Please check back shortly.') }}</p>
  </div>
</body>
</html>

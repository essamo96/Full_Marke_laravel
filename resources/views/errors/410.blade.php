<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>غير متاح</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: Tahoma, Arial, sans-serif; background: #f5f7fb; color: #1f2937; }
        .box { max-width: 460px; margin: 24px; padding: 32px; background: #fff; border-radius: 14px; box-shadow: 0 6px 24px rgba(15, 23, 42, .08); text-align: center; }
        h1 { font-size: 20px; margin: 0 0 12px; }
        p { margin: 0 0 20px; line-height: 1.8; color: #4b5563; }
        a { display: inline-block; padding: 10px 22px; border-radius: 8px; background: #2563eb; color: #fff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <h1>المحتوى غير متاح</h1>
        <p>{{ $exception->getMessage() ?: 'هذا المحتوى لم يعد متاحاً.' }}</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">رجوع</a>
    </div>
</body>
</html>

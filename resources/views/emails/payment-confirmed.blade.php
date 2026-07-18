<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding: 40px 20px;
      direction: rtl;
    }
    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .header {
      background-color: #f8fafc;
      padding: 30px;
      text-align: center;
      border-bottom: 1px solid #e2e8f0;
    }
    .header img {
      height: 60px;
      margin-bottom: 15px;
    }
    .header h2 {
      margin: 0;
      color: #0f172a;
      font-size: 24px;
    }
    .content {
      padding: 40px 30px;
      color: #334155;
      line-height: 1.6;
      font-size: 16px;
      text-align: right;
    }
    .content p {
      margin: 0 0 20px 0;
    }
    .highlight-box {
      background-color: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
      text-align: center;
    }
    .highlight-box p {
      margin: 0;
      color: #166534;
      font-size: 18px;
      font-weight: bold;
    }
    .footer {
      background-color: #f8fafc;
      padding: 20px;
      text-align: center;
      font-size: 14px;
      color: #64748b;
      border-top: 1px solid #e2e8f0;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <img src="{{ asset('site/images/logo_v2_blue.png') }}" alt="Full Mark Academy Logo">
      <h2>أكاديمية فل مارك</h2>
    </div>
    
    <div class="content">
      <p>مرحباً <strong>{{ app()->getLocale() == 'ar' ? $payment->student->full_name_ar : $payment->student->full_name_en }}</strong>،</p>
      
      <div class="highlight-box">
        <p>تم تأكيد استلام دفعتك بنجاح ✅</p>
      </div>

      <p>{{ __('app.mail_payment_confirmed_body', ['number' => $payment->payment_number, 'amount' => number_format($payment->amount, 2)]) }}</p>

      <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px solid #e2e8f0;">
        <p style="margin: 0 0 15px 0; font-size: 18px; color: #0f172a; font-weight: bold;">بيانات الدخول لحسابك:</p>
        <p style="margin: 0 0 10px 0;"><strong>اسم المستخدم (البريد الإلكتروني):</strong> <span style="color: #2563eb; direction: ltr; display: inline-block;">{{ $payment->student->email }}</span></p>
        <p style="margin: 0;"><strong>كلمة المرور:</strong> كلمة المرور الخاصة بك (التي قمت باختيارها عند التسجيل).</p>
      </div>

      <p>نحن سعداء بانضمامك لنا، ونتمنى لك التوفيق والنجاح الدائم في مسيرتك التعليمية.</p>
    </div>
    
    <div class="footer">
      <p>&copy; {{ date('Y') }} Full Mark Academy. جميع الحقوق محفوظة.</p>
    </div>
  </div>
</body>
</html>

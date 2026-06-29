<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:32px;">
  <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px;">
    <h2 style="color:#1e40af;">FULL MARK ACADEMY — Admin Notification</h2>
    <p>A new payment <strong>{{ $payment->payment_number }}</strong> for {{ number_format($payment->total_amount, 2) }} from
       {{ $payment->student->full_name_en }} is awaiting review.</p>
  </div>
</body>
</html>

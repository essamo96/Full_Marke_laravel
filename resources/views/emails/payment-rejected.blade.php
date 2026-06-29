<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:32px;">
  <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px;">
    <h2 style="color:#ef4444;">FULL MARK ACADEMY</h2>
    <p>{{ __('app.mail_payment_rejected_body', ['number' => $payment->payment_number]) }}</p>
    <p><strong>{{ __('app.rejection_reason') }}:</strong> {{ $payment->rejection_reason }}</p>
  </div>
</body>
</html>

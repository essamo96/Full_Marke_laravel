<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:32px;">
  <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px;">
    <h2 style="color:#1e40af;">FULL MARK ACADEMY</h2>
    <p>{{ __('app.mail_payment_received_body', ['number' => $payment->payment_number, 'amount' => number_format($payment->amount, 2)]) }}</p>
    <p style="color:#94a3b8; font-size:13px;">{{ __('app.mail_payment_pending_note') }}</p>
  </div>
</body>
</html>

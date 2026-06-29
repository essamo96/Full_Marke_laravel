<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:32px;">
    <div style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px; text-align:center;">
        <h2 style="color:#1e40af; margin-bottom:8px;">FULL MARK ACADEMY</h2>
        <p style="color:#475569; margin-bottom:24px;">رمز تحقق البريد الإلكتروني / Email Verification Code</p>
        <div style="font-size:32px; font-weight:bold; letter-spacing:8px; color:#1e40af; background:#eff6ff; padding:16px; border-radius:8px; display:inline-block;">
            {{ $code }}
        </div>
        <p style="color:#94a3b8; margin-top:24px; font-size:13px;">
            هذا الرمز صالح لمدة 10 دقائق. This code is valid for 10 minutes.
        </p>
    </div>
</body>
</html>

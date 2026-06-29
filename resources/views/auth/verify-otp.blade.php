@extends('layouts.student-auth')

@section('title', 'Verify Email | FULL MARK ACADEMY')

@section('content')
          <p class="card-subtitle text-center mb-4">
            <span data-en="We sent a 6-digit code to" data-ar="تم إرسال رمز مكوّن من 6 أرقام إلى">We sent a 6-digit code to</span>
            <strong>{{ $email }}</strong>
          </p>

          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <form method="POST" action="{{ route($userType.'.verify.submit') }}">
            @csrf
            <div class="d-flex justify-content-center gap-2 mb-5" dir="ltr">
              @for ($i = 0; $i < 6; $i++)
                <input type="text" inputmode="numeric" maxlength="1" class="lux-input text-center otp-cell" style="width:48px; height:56px; font-size:1.4rem;"
                       oninput="this.value=this.value.replace(/[^0-9]/,''); if(this.value && this.nextElementSibling) this.nextElementSibling.focus();">
              @endfor
            </div>
            <input type="hidden" name="code" id="otpCodeInput">

            <button type="submit" class="btn btn-luxury w-100 py-3" id="verifySubmitBtn" data-en="Verify My Account" data-ar="تحقق من حسابي">Verify My Account</button>
          </form>

          <form method="POST" action="{{ route($userType.'.verify.resend') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" id="resendBtn" class="btn btn-link" data-en="Resend Code" data-ar="إعادة إرسال الرمز">Resend Code</button>
            <span id="resendCountdown" class="text-muted"></span>
          </form>

          <script>
            const cells = document.querySelectorAll('.otp-cell');
            const hidden = document.getElementById('otpCodeInput');
            document.querySelector('form').addEventListener('submit', () => {
              hidden.value = Array.from(cells).map(c => c.value).join('');
            });
            cells.forEach((cell, i) => {
              cell.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !cell.value && cells[i - 1]) cells[i - 1].focus();
              });
            });

            let seconds = 60;
            const resendBtn = document.getElementById('resendBtn');
            const countdownEl = document.getElementById('resendCountdown');
            resendBtn.disabled = true;
            const timer = setInterval(() => {
              seconds--;
              countdownEl.textContent = seconds > 0 ? `(00:${String(seconds).padStart(2, '0')})` : '';
              if (seconds <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
              }
            }, 1000);
          </script>
@endsection

/**
 * Teacher dashboard extras. Sidebar behavior is in assets/js/dashboard-sidebar.js
 */
document.addEventListener('DOMContentLoaded', () => {
  window.openStitchModal = function (title, content) {
    let modal = document.getElementById('stitchModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'stitchModal';
      modal.className = 'stitch-modal-overlay';
      modal.innerHTML = `
        <div class="stitch-modal-content glass-panel tilt-card">
          <div class="stitch-modal-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0" style="color: var(--accent-color);" id="stitchModalTitle"></h5>
            <button class="btn btn-glass icon-btn rounded-circle" onclick="closeStitchModal()"><i class="bi bi-x-lg"></i></button>
          </div>
          <div class="stitch-modal-body" id="stitchModalBody"></div>
        </div>
      `;
      document.body.appendChild(modal);
    }
    document.getElementById('stitchModalTitle').innerHTML = title;
    document.getElementById('stitchModalBody').innerHTML = content;

    requestAnimationFrame(() => {
      modal.classList.add('active');
    });
  };

  window.closeStitchModal = function () {
    const modal = document.getElementById('stitchModal');
    if (modal) {
      modal.classList.remove('active');
      setTimeout(() => { modal.remove(); }, 300);
    }
  };

  if (!sessionStorage.getItem('welcomeAlertShown')) {
    sessionStorage.setItem('welcomeAlertShown', 'true');

    const swalScript = document.createElement('script');
    swalScript.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    swalScript.onload = () => {
      const isTeacher = window.location.pathname.includes('/teacher/');
      const userName = document.querySelector('.dashboard-header span.fw-semibold')?.textContent || (isTeacher ? 'Teacher' : 'Student');
      const role = isTeacher ? 'Teacher' : 'Student';
      const roleAr = isTeacher ? 'المعلم' : 'الطالب';
      const isRtl = document.documentElement.dir === 'rtl';
      const title = isRtl ? `مرحباً بعودتك، ${userName}!` : `Welcome back, ${userName}!`;
      const text = isRtl ? `تم تسجيل الدخول بنجاح إلى بوابة ${roleAr}` : `Successfully logged into the ${role} portal`;

      const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
      audio.volume = 0.5;
      audio.play().catch(() => {});

      Swal.fire({
        toast: true,
        position: isRtl ? 'top-start' : 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        title: title,
        text: text,
        icon: 'success',
        background: 'var(--bg-secondary)',
        color: 'var(--text-primary)',
        customClass: {
          popup: 'glass-panel border border-accent',
          title: 'fw-bold',
        },
      });
    };
    document.body.appendChild(swalScript);
  }
});

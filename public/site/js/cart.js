/* cart.js */

(function () {
  const STORAGE_KEY = 'full-mark-academy-cart';

  // Read language dynamically
  function getLang() {
    return document.documentElement.lang === 'ar' ? 'ar' : 'en';
  }

  // Get items
  function getCartItems() {
    const data = localStorage.getItem(STORAGE_KEY);
    return data ? JSON.parse(data) : [];
  }

  // Save items
  function saveCartItems(items) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    updateCartBadges();
  }

  // Update navbar/mobile badge counters
  function updateCartBadges() {
    const items = getCartItems();
    const count = items.length;
    
    document.querySelectorAll('#cartCountBadge, #cartCountBadgeMobile').forEach(badge => {
      if (count > 0) {
        badge.innerText = count;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    });
  }

  // Subjects the student already registered in (set by the layout for
  // logged-in students) — never allowed into the cart.
  function isAlreadyRegistered(subjectId) {
    const ids = window.registeredSubjectIds || [];
    return ids.includes(String(subjectId));
  }

  // Add Item
  function addItem(item) {
    const items = getCartItems();
    const exists = items.some(i => i.id === item.id);

    const lang = getLang();

    if (isAlreadyRegistered(item.id)) {
      const msg = lang === 'ar'
        ? `أنت مسجل بالفعل في مساق "${item.name.ar}" ولا يمكن إضافته مرة أخرى.`
        : `You are already registered in "${item.name.en}".`;
      showNotification(msg, 'warning');
      return;
    }

    if (exists) {
      const msg = lang === 'ar' 
        ? `عذراً، مساق "${item.name.ar}" مضاف بالفعل في سلتك.` 
        : `"${item.name.en}" is already added to your cart.`;
      showNotification(msg, 'warning');
      return;
    }
    
    items.push(item);
    saveCartItems(items);
    
    const msg = lang === 'ar' 
      ? `تمت إضافة مساق "${item.name.ar}" إلى السلة بنجاح!` 
      : `"${item.name.en}" has been added to your cart!`;
    showNotification(msg, 'success');
  }

  // Remove Item
  function removeItem(id) {
    let items = getCartItems();
    const itemToRemove = items.find(i => i.id === id);
    items = items.filter(i => i.id !== id);
    saveCartItems(items);
    
    const lang = getLang();
    if (itemToRemove) {
      const msg = lang === 'ar'
        ? `تمت إزالة مساق "${itemToRemove.name.ar}" من السلة.`
        : `Removed "${itemToRemove.name.en}" from cart.`;
      showNotification(msg, 'info');
    }
    
    renderCartModalContent();
  }

  // Parse price helper (e.g. "120.50 JOD" -> 120.50)
  function parsePrice(priceStr) {
    if (typeof priceStr === 'number') return priceStr;
    const num = parseFloat(String(priceStr).replace(/[^0-9.]/g, ''));
    return isNaN(num) ? 0 : num;
  }

  // Theme-aware toast — background/text come from the active theme's CSS
  // variables so the message stays readable in dark, light and gold alike;
  // only the border/icon carry the status color.
  let toastHideTimer = null;
  function showNotification(message, type = 'success') {
    let toast = document.getElementById('cart-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'cart-toast';
      document.body.appendChild(toast);
    }

    const statusColors = {
      success: '#10b981',
      warning: '#eab308',
      info: '#3b82f6',
    };
    const statusColor = statusColors[type] || 'var(--accent-color)';
    const statusIcons = {
      success: 'bi-check-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
      info: 'bi-info-circle-fill',
    };

    toast.style.cssText = `
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%) translateY(120px);
      background: var(--bg-secondary);
      border: 1px solid ${statusColor};
      border-inline-start-width: 5px;
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
      color: var(--text-primary);
      padding: 14px 22px;
      border-radius: var(--radius-md, 12px);
      z-index: 2000;
      font-weight: 600;
      font-size: .95rem;
      line-height: 1.5;
      max-width: min(92vw, 480px);
      transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: flex;
      align-items: center;
      gap: 10px;
    `;

    const iconHtml = statusIcons[type]
      ? `<i class="bi ${statusIcons[type]}" style="color: ${statusColor}; font-size: 1.3rem; flex-shrink: 0;"></i>`
      : '';

    toast.innerHTML = `${iconHtml}<span>${message}</span>`;

    // Restart the slide-in even when a toast is already visible
    if (toastHideTimer) clearTimeout(toastHideTimer);
    requestAnimationFrame(() => {
      toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    toastHideTimer = setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(120px)';
    }, 4000);
  }

  // Dynamically inject the Modal DOM structure
  function injectModalMarkup() {
    if (document.getElementById('cartModal')) return;

    const lang = getLang();
    const closeBtnPos = lang === 'ar' ? 'left: 20px; right: auto;' : 'right: 20px; left: auto;';

    const modal = document.createElement('div');
    modal.id = 'cartModal';
    modal.style.cssText = `
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(5,4,2,0.75);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 1500;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      transition: opacity 0.3s ease;
    `;
    
    modal.innerHTML = `
      <div id="cartModalPanel" class="glass-panel" style="
        width: 100%;
        max-width: 650px;
        max-height: 85vh;
        overflow-y: auto;
        padding: 30px;
        position: relative;
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid var(--glass-border);
        box-shadow: 0 20px 50px rgba(0,0,0,0.8);
      ">
        <!-- Close Button -->
        <button id="cartCloseBtn" onclick="toggleCartModal()" style="
          position: absolute;
          top: 20px;
          ${closeBtnPos}
          background: transparent;
          border: none;
          color: var(--text-primary);
          font-size: 24px;
          line-height: 1;
          cursor: pointer;
          transition: color 0.2s;
        " onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-primary)'">
          <i class="bi bi-x-lg"></i>
        </button>

        <div id="cartModalInner">
          <!-- Dynamically populated content -->
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    // Close when clicking outside the panel
    modal.addEventListener('click', (e) => {
      if (e.target === modal) toggleCartModal();
    });
  }

  // Render the Cart Modal internal contents
  function renderCartModalContent() {
    const container = document.querySelector('#cartModalInner');
    if (!container) return;

    const items = getCartItems();
    const lang = getLang();
    
    if (items.length === 0) {
      container.innerHTML = `
        <div class="text-center py-12">
          <i class="bi bi-bag-x-fill text-5xl text-gold opacity-50 mb-4 block"></i>
          <h3 class="text-2xl font-bold mb-2" style="color: var(--text-primary);" 
              data-en="Your Cart is Empty" data-ar="سلتك فارغة حالياً">
            ${lang === 'ar' ? 'سلتك فارغة حالياً' : 'Your Cart is Empty'}
          </h3>
          <p class="opacity-70 text-sm mb-6" style="color: var(--text-secondary);"
             data-en="Browse our academic programs and select subjects to register."
             data-ar="تصفح برامجنا الأكاديمية واختر المواد التي ترغب بالتسجيل فيها للتأهل.">
            ${lang === 'ar' ? 'تصفح برامجنا الأكاديمية واختر المواد التي ترغب بالتسجيل فيها للتأهل.' : 'Browse our academic programs and select subjects to register.'}
          </p>
          <button class="btn btn-luxury px-6 py-2 rounded-lg" onclick="toggleCartModal()">
            ${lang === 'ar' ? 'متابعة التصفح' : 'Continue Browsing'}
          </button>
        </div>
      `;
      return;
    }

    // Calculations
    let sumFee = 0;
    let sumTotal = 0;
    let itemsHtml = `<div class="cart-items-list mb-6" style="max-height: 250px; overflow-y: auto; padding-right: 5px;">`;

    items.forEach(item => {
      const parsedFee = parsePrice(item.fee);
      const parsedTotal = parsePrice(item.totalFee);
      sumFee += parsedFee;
      sumTotal += parsedTotal;

      const programTag = item.program[lang];
      const subjectName = item.name[lang];
      const removeText = lang === 'ar' ? 'حذف' : 'Remove';

      itemsHtml += `
        <div class="d-flex align-items-center justify-content-between p-3 mb-3" style="
          background: rgba(255, 255, 255, 0.02);
          border: 1px solid var(--separator-color);
          border-radius: var(--radius-md);
        ">
          <div class="d-flex align-items-center gap-3">
            <img src="${item.image}" alt="${subjectName}" class="rounded-md" style="width: 55px; height: 55px; object-fit: cover; border: 1px solid var(--glass-border);">
            <div>
              <span class="text-xs text-gold font-semibold uppercase block" style="font-size: 0.7rem;">${programTag}</span>
              <h4 class="text-base font-bold mb-0" style="color: var(--text-primary);">${subjectName}</h4>
              <div class="d-flex gap-2 align-items-center mt-1">
                <span class="text-xs opacity-70">${item.fee}</span>
                ${item.discount ? `<span class="text-xs" style="color: #ef4444; font-weight: bold;">(${item.discount} ${lang === 'ar' ? 'خصم' : 'OFF'})</span>` : ''}
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-4">
            <div class="text-end">
              <span class="text-xs text-muted block" style="font-size: 0.65rem;">${lang === 'ar' ? 'الإجمالي' : 'Total'}</span>
              <span class="text-base font-bold text-gold">${item.totalFee}</span>
            </div>
            <button onclick="window.CartSystem.removeItem('${item.id}')" class="btn btn-sm btn-glass text-danger p-2" title="${removeText}" style="border-radius: var(--radius-sm); border-color: rgba(239, 68, 68, 0.2);">
              <i class="bi bi-trash3-fill"></i>
            </button>
          </div>
        </div>
      `;
    });

    itemsHtml += `</div>`;

    const totalSaved = sumFee - sumTotal; // If positive, shows how much is saved (or we can summarize sumTotal directly)
    const fmtFee = sumFee.toFixed(2);
    const fmtTotal = sumTotal.toFixed(2);
    const summaryHtml = `
      <div class="p-3 mb-6" style="
        background: rgba(197, 168, 128, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-md);
      ">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-sm opacity-85">${lang === 'ar' ? 'مجموع الرسوم الأساسية:' : 'Base Registration Fee:'}</span>
          <span class="text-sm font-semibold">${fmtFee} JOD</span>
        </div>
        <div class="d-flex justify-content-between pt-2 border-t" style="border-color: var(--separator-color) !important;">
          <span class="text-base font-bold" style="color: var(--text-primary);">${lang === 'ar' ? 'المجموع النهائي المطلوب:' : 'Total Payable Registration:'}</span>
          <span class="text-lg font-extrabold text-gold">${fmtTotal} JOD</span>
        </div>
      </div>
    `;

    // Checkout Form
    const formHtml = window.isStudentLoggedIn ? `
      <div class="mt-4">
        <button type="button" onclick="window.CartSystem.checkoutLoggedIn()" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
          <span>${lang === 'ar' ? 'المتابعة لإجراء الدفع والتسجيل' : 'Proceed to Payment & Registration'}</span>
          <i class="bi bi-credit-card ms-2"></i>
        </button>
      </div>
    ` : `
      <div class="mt-4 text-center">
        <p class="opacity-75 mb-4" style="color: var(--text-secondary);">
          ${lang === 'ar' ? 'يجب عليك إنشاء حساب أو تسجيل الدخول لإتمام التسجيل في المساقات.' : 'You must create an account or sign in to complete your subject registration.'}
        </p>
        <a href="${window.studentRegisterUrl || '/student/register'}" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
          <span>${lang === 'ar' ? 'سجل حساباً للمتابعة' : 'Sign Up to Continue'}</span>
          <i class="bi bi-arrow-right ms-2 rtl:rotate-180"></i>
        </a>
      </div>
    `;

    container.innerHTML = `
      <h3 class="text-2xl font-bold mb-4 d-flex align-items-center gap-2" style="color: var(--text-primary);">
        <i class="bi bi-bag-check-fill text-gold"></i>
        <span>${lang === 'ar' ? 'سلة المواد المختارة' : 'Student Selected Subjects'}</span>
      </h3>
      ${itemsHtml}
      ${summaryHtml}
      ${formHtml}
    `;
    
    // Fix alignment label in RTL dynamically
    if (lang === 'ar') {
      document.querySelectorAll('#cartModalPanel .floating-input-group label').forEach(lbl => {
        lbl.style.left = 'auto';
        lbl.style.right = '16px';
      });
      // Adjust floating focus styles
      document.querySelectorAll('#cartModalPanel .floating-input-group input').forEach(input => {
        input.addEventListener('focus', () => {
          const lbl = input.nextElementSibling;
          if (lbl && lbl.tagName === 'LABEL') {
            lbl.style.right = '12px';
            lbl.style.left = 'auto';
          }
        });
      });
    }
  }

  // Toggle Modal visibility
  function toggleCartModal() {
    injectModalMarkup();
    const modal = document.getElementById('cartModal');
    const panel = document.getElementById('cartModalPanel');
    
    if (!modal || !panel) return;

    if (modal.style.display === 'none' || modal.style.display === '') {
      // Close mobile drawer menu if open
      const mobileNavMenu = document.getElementById('mobileNavMenu');
      const mobileBackdrop = document.getElementById('mobileMenuBackdrop');
      if (mobileNavMenu && mobileNavMenu.classList.contains('active')) {
        mobileNavMenu.classList.remove('active');
        if (mobileBackdrop) mobileBackdrop.classList.remove('active');
      }

      renderCartModalContent();
      modal.style.display = 'flex';
      setTimeout(() => {
        modal.style.opacity = '1';
        panel.style.transform = 'scale(1)';
      }, 50);
      document.body.style.overflow = 'hidden'; // lock scrolling
    } else {
      modal.style.opacity = '0';
      panel.style.transform = 'scale(0.9)';
      setTimeout(() => {
        modal.style.display = 'none';
      }, 300);
      document.body.style.overflow = ''; // unlock scrolling
    }
  }

  // Handle Checkout submission
  function handleCheckout(e) {
    e.preventDefault();
    const name = document.getElementById('cartName').value;
    const email = document.getElementById('cartEmail').value;
    const phone = document.getElementById('cartPhone').value;
    const items = getCartItems();

    const lang = getLang();

    // Replace modal view with success animation
    const container = document.querySelector('#cartModalInner');
    if (container) {
      container.innerHTML = `
        <div class="text-center py-12 reveal-scale">
          <div class="w-20 h-20 bg-success bg-opacity-20 text-success rounded-full d-flex align-items-center justify-content-center text-4xl mx-auto mb-4" style="
            border: 2px solid #22c55e;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
          ">
            <i class="bi bi-patch-check-fill"></i>
          </div>
          <h3 class="text-3xl font-extrabold mb-3 text-success">
            ${lang === 'ar' ? 'تم تقديم طلبك بنجاح!' : 'Registration Request Submitted!'}
          </h3>
          <p class="text-lg leading-relaxed mb-6" style="color: var(--text-secondary);">
            ${lang === 'ar' 
              ? `شكرًا لك يا <strong>${name}</strong>. لقد تم استلام طلبك للتسجيل في <strong>${items.length}</strong> مساقات بنجاح.` 
              : `Thank you, <strong>${name}</strong>. Your request to register in <strong>${items.length}</strong> subjects has been received.`}
          </p>
          <div class="p-4 mb-6 text-start" style="
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            max-width: 450px;
            margin: 0 auto;
          ">
            <p class="text-sm mb-2"><strong>${lang === 'ar' ? 'الاسم:' : 'Name:'}</strong> ${name}</p>
            <p class="text-sm mb-2"><strong>${lang === 'ar' ? 'البريد الإلكتروني:' : 'Email:'}</strong> ${email}</p>
            <p class="text-sm mb-0"><strong>${lang === 'ar' ? 'الهاتف:' : 'Phone:'}</strong> ${phone}</p>
          </div>
          <p class="opacity-70 text-sm mb-6" style="color: var(--text-secondary);">
            ${lang === 'ar' 
              ? 'سيقوم منسق القبول لدينا بمراجعة طلبك والتواصل معك على الفور لتأكيد وتفعيل الحساب.'
              : 'Our admission advisor will review your request and get in touch with you shortly to complete enrollment.'}
          </p>
          <button class="btn btn-luxury px-6 py-2.5 rounded-lg" onclick="toggleCartModal()">
            ${lang === 'ar' ? 'إغلاق النافذة' : 'Close window'}
          </button>
        </div>
      `;
    }

    // Clear cart in storage
    saveCartItems([]);
  }

  function checkoutLoggedIn() {
    const items = getCartItems();
    if (items.length === 0) return;

    const lang = getLang();
    const btn = document.querySelector('#cartModalInner button[onclick]');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${lang === 'ar' ? 'جاري التحضير للدفع...' : 'Preparing checkout...'}`;
    }

    fetch('/student/cart/sync', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.csrfToken || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        items: items.map(i => ({ subject_id: i.id, group_id: i.group_id || null }))
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        saveCartItems([]);
        window.location.href = data.redirect;
      } else {
        showNotification(data.message || (lang === 'ar' ? 'حدث خطأ' : 'Error'), 'warning');
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = lang === 'ar' ? 'المتابعة لإجراء الدفع والتسجيل' : 'Proceed to Payment &amp; Registration';
        }
      }
    })
    .catch(err => {
      console.error('Cart sync error:', err);
      showNotification(lang === 'ar' ? 'حدث خطأ أثناء الاتصال' : 'An error occurred', 'warning');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = lang === 'ar' ? 'المتابعة لإجراء الدفع والتسجيل' : 'Proceed to Payment &amp; Registration';
      }
    });
  }

  // Load badge counts and listeners on load
  document.addEventListener('DOMContentLoaded', () => {
    // Prune any cart items the student has since registered in
    const items = getCartItems();
    const cleaned = items.filter(i => !isAlreadyRegistered(i.id));
    if (cleaned.length !== items.length) saveCartItems(cleaned);

    injectModalMarkup();
    updateCartBadges();
  });

  // Attach global API
  window.CartSystem = {
    addItem,
    removeItem,
    getItems: getCartItems,
    clear: () => saveCartItems([]),
    handleCheckout,
    showNotification,
    checkoutLoggedIn
  };
  window.toggleCartModal = toggleCartModal;

})();

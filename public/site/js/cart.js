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

  // Add Item
  function addItem(item) {
    const items = getCartItems();
    const exists = items.some(i => i.id === item.id);
    
    const lang = getLang();
    
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

  // Parse price helper (e.g. "120 JOD" -> 120)
  function parsePrice(priceStr) {
    const num = parseInt(priceStr.replace(/[^0-9]/g, ''), 10);
    return isNaN(num) ? 0 : num;
  }

  // Ingest beautiful notification/toast
  function showNotification(message, type = 'success') {
    let toast = document.getElementById('cart-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'cart-toast';
      toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: var(--glass-bg);
        border: 1px solid var(--accent-color);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        color: var(--text-primary);
        padding: 15px 30px;
        border-radius: var(--radius-md);
        z-index: 2000;
        font-weight: 600;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
      `;
      document.body.appendChild(toast);
    }
    
    let iconHtml = '';
    
    // Type colors
    if (type === 'success') {
      toast.style.borderColor = '#10b981'; // Green
      toast.style.boxShadow = '0 10px 30px rgba(16, 185, 129, 0.25)';
      toast.style.background = 'rgba(5, 15, 10, 0.9)'; // Dark green tint glass
      iconHtml = `<i class="bi bi-check-circle-fill" style="color: #10b981; margin-inline-end: 10px; font-size: 1.25rem; display: inline-block; vertical-align: middle;"></i>`;
    } else if (type === 'warning') {
      toast.style.borderColor = '#eab308'; // Amber/Yellow
      toast.style.boxShadow = '0 10px 30px rgba(234, 179, 8, 0.2)';
      toast.style.background = 'rgba(15, 15, 5, 0.9)';
      iconHtml = `<i class="bi bi-exclamation-triangle-fill" style="color: #eab308; margin-inline-end: 10px; font-size: 1.25rem; display: inline-block; vertical-align: middle;"></i>`;
    } else if (type === 'info') {
      toast.style.borderColor = '#3b82f6'; // Blue
      toast.style.boxShadow = '0 10px 30px rgba(59, 130, 246, 0.2)';
      toast.style.background = 'rgba(5, 10, 20, 0.9)';
      iconHtml = `<i class="bi bi-info-circle-fill" style="color: #3b82f6; margin-inline-end: 10px; font-size: 1.25rem; display: inline-block; vertical-align: middle;"></i>`;
    } else {
      toast.style.borderColor = 'var(--accent-color)';
      toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.5)';
      toast.style.background = 'var(--glass-bg)';
    }

    toast.innerHTML = `${iconHtml}<span style="vertical-align: middle; display: inline-block;">${message}</span>`;
    toast.style.transform = 'translateX(-50%) translateY(0)';
    
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(100px)';
    }, 4000);
  }

  // Dynamically inject the Modal DOM structure
  function injectModalMarkup() {
    if (document.getElementById('cartModal')) return;

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
          right: 20px;
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
    const summaryHtml = `
      <div class="p-3 mb-6" style="
        background: rgba(197, 168, 128, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-md);
      ">
        <div class="d-flex justify-content-between mb-2">
          <span class="text-sm opacity-85">${lang === 'ar' ? 'مجموع الرسوم الأساسية:' : 'Base Registration Fee:'}</span>
          <span class="text-sm font-semibold">${sumFee} JOD</span>
        </div>
        <div class="d-flex justify-content-between pt-2 border-t" style="border-color: var(--separator-color) !important;">
          <span class="text-base font-bold" style="color: var(--text-primary);">${lang === 'ar' ? 'المجموع النهائي المطلوب:' : 'Total Payable Registration:'}</span>
          <span class="text-lg font-extrabold text-gold">${sumTotal} JOD</span>
        </div>
      </div>
    `;

    // Checkout Form
    const formHtml = `
      <h4 class="text-lg font-bold mb-3 border-b pb-2" style="color: var(--text-primary); border-color: var(--separator-color) !important;">
        ${lang === 'ar' ? 'معلومات تأكيد حجز مقعدك' : 'Confirm Your Registration'}
      </h4>
      <form id="cartCheckoutForm" onsubmit="window.CartSystem.handleCheckout(event)">
        <div class="row">
          <div class="col-md-6">
            <div class="floating-input-group mb-4">
              <input type="text" id="cartName" placeholder=" " required style="background: var(--input-bg); border-color: var(--input-border); color: var(--text-primary);">
              <label for="cartName">${lang === 'ar' ? 'الاسم الكامل' : 'Full Name'}</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="floating-input-group mb-4">
              <input type="email" id="cartEmail" placeholder=" " required style="background: var(--input-bg); border-color: var(--input-border); color: var(--text-primary);">
              <label for="cartEmail">${lang === 'ar' ? 'البريد الإلكتروني' : 'Email Address'}</label>
            </div>
          </div>
        </div>
        <div class="floating-input-group mb-4">
          <input type="tel" id="cartPhone" placeholder=" " required style="background: var(--input-bg); border-color: var(--input-border); color: var(--text-primary);">
          <label for="cartPhone">${lang === 'ar' ? 'رقم الهاتف' : 'Phone Number'}</label>
        </div>
        <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
          <span>${lang === 'ar' ? 'تأكيد وحجز المساقات المحددة' : 'Submit Registration Request'}</span>
          <i class="bi bi-arrow-right ms-2 rtl:rotate-180"></i>
        </button>
      </form>
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

  // Load badge counts and listeners on load
  document.addEventListener('DOMContentLoaded', () => {
    injectModalMarkup();
    updateCartBadges();
  });

  // Attach global API
  window.CartSystem = {
    addItem,
    removeItem,
    getItems: getCartItems,
    clear: () => saveCartItems([]),
    handleCheckout
  };
  window.toggleCartModal = toggleCartModal;

})();

/* animations.js */
document.addEventListener('DOMContentLoaded', () => {
  // 1. 3D Tilt Card Effect
  const tiltCards = document.querySelectorAll('.tilt-card');
  
  tiltCards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left; // x coordinate inside the card
      const y = e.clientY - rect.top;  // y coordinate inside the card
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      // Calculate rotation degree (max 10 degrees)
      const rotateX = ((centerY - y) / centerY) * 10;
      const rotateY = ((x - centerX) / centerX) * 10;
      
      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
    });
    
    card.addEventListener('mouseleave', () => {
      card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
    });
  });

  // 2. Mouse Parallax for Hero Elements
  const heroSection = document.getElementById('hero');
  const parallaxLayers = document.querySelectorAll('.parallax-layer');
  
  if (heroSection && parallaxLayers.length > 0) {
    heroSection.addEventListener('mousemove', (e) => {
      const width = window.innerWidth;
      const height = window.innerHeight;
      
      const mouseX = e.clientX - width / 2;
      const mouseY = e.clientY - height / 2;
      
      parallaxLayers.forEach(layer => {
        const speed = layer.getAttribute('data-parallax-speed') || 0.05;
        const xOffset = mouseX * speed;
        const yOffset = mouseY * speed;
        
        layer.style.transform = `translate3d(${xOffset}px, ${yOffset}px, 0)`;
      });
    });
  }

  // 3. Stats Counter Animation
  const counters = document.querySelectorAll('.counter-number');
  
  function animateCounter(counter) {
    const target = parseInt(counter.getAttribute('data-target'), 10) || 0;
    const duration = 2000; // ms
    const startTime = performance.now();
    
    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Easing function (easeOutQuad)
      const ease = progress * (2 - progress);
      const currentVal = Math.floor(ease * target);
      
      counter.innerText = currentVal;
      
      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        counter.innerText = target;
      }
    }
    
    requestAnimationFrame(update);
  }
  
  // Set up intersection observer for counters
  const counterObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.5
  });
  
  counters.forEach(counter => {
    counterObserver.observe(counter);
  });
});

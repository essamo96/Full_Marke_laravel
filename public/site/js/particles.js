/* particles.js — starts only after the hero intro videos finish */
function __initParticlesCanvas() {
  const canvas = document.getElementById('particles-canvas');
  if (!canvas || canvas.dataset.inited === '1') return;
  canvas.dataset.inited = '1';
  canvas.classList.add('is-on');

  const ctx = canvas.getContext('2d');
  let particlesArray = [];
  const numberOfParticles = 80;
  
  // Mouse coordinates
  let mouse = {
    x: null,
    y: null,
    radius: 120
  };

  window.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
  });

  window.addEventListener('mouseout', () => {
    mouse.x = null;
    mouse.y = null;
  });

  // Resize canvas
  function resizeCanvas() {
    canvas.width = canvas.parentElement.offsetWidth;
    canvas.height = canvas.parentElement.offsetHeight;
    initParticles();
  }
  
  window.addEventListener('resize', resizeCanvas);
  
  // Particle constructor
  class Particle {
    constructor(x, y, directionX, directionY, size, color) {
      this.x = x;
      this.y = y;
      this.directionX = directionX;
      this.directionY = directionY;
      this.size = size;
      this.color = color;
    }
    
    // Draw single particle
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2, false);
      ctx.fillStyle = this.color;
      ctx.fill();
    }
    
    // Update particle position and interactions
    update() {
      // Keep inside bounds
      if (this.x > canvas.width || this.x < 0) {
        this.directionX = -this.directionX;
      }
      if (this.y > canvas.height || this.y < 0) {
        this.directionY = -this.directionY;
      }
      
      // Mouse push effect
      if (mouse.x !== null && mouse.y !== null) {
        let dx = mouse.x - this.x;
        let dy = mouse.y - this.y;
        let distance = Math.sqrt(dx * dx + dy * dy);
        if (distance < mouse.radius + this.size) {
          if (mouse.x < this.x && this.x < canvas.width - this.size * 10) {
            this.x += 2;
          }
          if (mouse.x > this.x && this.x > this.size * 10) {
            this.x -= 2;
          }
          if (mouse.y < this.y && this.y < canvas.height - this.size * 10) {
            this.y += 2;
          }
          if (mouse.y > this.y && this.y > this.size * 10) {
            this.y -= 2;
          }
        }
      }
      
      // Move particles
      this.x += this.directionX;
      this.y += this.directionY;
      
      this.draw();
    }
  }

  // Get color based on theme
  function getParticleColor() {
    const isDark = document.documentElement.classList.contains('theme-dark');
    const isGold = document.documentElement.classList.contains('theme-gold');
    if (isGold) return 'rgba(197, 168, 128, 0.4)';
    if (isDark) return 'rgba(0, 240, 255, 0.4)';
    return 'rgba(2, 132, 199, 0.3)';
  }

  // Initialize particle array
  function initParticles() {
    particlesArray = [];
    const color = getParticleColor();
    
    for (let i = 0; i < numberOfParticles; i++) {
      let size = (Math.random() * 3) + 1;
      let x = (Math.random() * ((canvas.width - size * 2) - (size * 2)) + size * 2);
      let y = (Math.random() * ((canvas.height - size * 2) - (size * 2)) + size * 2);
      let directionX = (Math.random() * 0.4) - 0.2;
      let directionY = (Math.random() * 0.4) - 0.2;
      
      particlesArray.push(new Particle(x, y, directionX, directionY, size, color));
    }
  }

  // Animation Loop
  function animate() {
    requestAnimationFrame(animate);
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    for (let i = 0; i < particlesArray.length; i++) {
      particlesArray[i].update();
    }
  }

  // Listen to theme change to update particle color
  window.addEventListener('themechanged', () => {
    const color = getParticleColor();
    particlesArray.forEach(p => p.color = color);
  });

  // Start particles
  resizeCanvas();
  animate();
}

// Kick off only after the hero intro completes (video sequence ended + still shown).
window.addEventListener('hero:complete', () => __initParticlesCanvas(), { once: true });
// Fallback: if the hero script is disabled, still init after DOM is ready.
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => __initParticlesCanvas(), 30000); // very late safety net
});

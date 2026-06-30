/* admin-login-particles.js — particle effect used on the admin login screen
   (behind the small logo and behind the brand panel logo), reacting to
   Metronic's light/dark mode (data-bs-theme). */
(function () {
  function isDark() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark';
  }

  function colorFor(canvas) {
    var dark = isDark();
    if (canvas.dataset.variant === 'brand') {
      return dark ? 'rgba(0, 240, 255, 0.6)' : 'rgba(197, 168, 128, 0.6)';
    }
    return dark ? 'rgba(0, 240, 255, 0.5)' : 'rgba(2, 132, 199, 0.35)';
  }

  function setup(canvas) {
    var ctx = canvas.getContext('2d');
    var particles = [];
    var count = parseInt(canvas.dataset.count, 10) || 28;

    function build() {
      particles = [];
      var c = colorFor(canvas);
      for (var i = 0; i < count; i++) {
        particles.push({
          x: Math.random() * canvas.width,
          y: Math.random() * canvas.height,
          dx: (Math.random() * 0.5) - 0.25,
          dy: (Math.random() * 0.5) - 0.25,
          size: (Math.random() * 2) + 0.5,
          color: c,
        });
      }
    }

    function resize() {
      var rect = canvas.parentElement.getBoundingClientRect();
      canvas.width = rect.width;
      canvas.height = rect.height;
      build();
    }

    function animate() {
      requestAnimationFrame(animate);
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particles.forEach(function (p) {
        if (p.x > canvas.width || p.x < 0) p.dx = -p.dx;
        if (p.y > canvas.height || p.y < 0) p.dy = -p.dy;
        p.x += p.dx;
        p.y += p.dy;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2, false);
        ctx.fillStyle = p.color;
        ctx.fill();
      });
    }

    window.addEventListener('resize', resize);

    resize();
    animate();

    return {
      refreshColor: function () {
        var c = colorFor(canvas);
        particles.forEach(function (p) { p.color = c; });
      },
    };
  }

  function init() {
    var canvases = [];

    var single = document.getElementById('admin-login-particles');
    if (single) canvases.push(single);

    document.querySelectorAll('.admin-particles-canvas').forEach(function (c) {
      c.dataset.variant = 'brand';
      c.dataset.count = '60';
      canvases.push(c);
    });

    if (!canvases.length) return;

    var instances = canvases.map(setup);

    // Metronic's theme-mode menu sets data-bs-theme on <html> when an option
    // is clicked — observe that attribute instead of relying on a custom event.
    new MutationObserver(function () {
      instances.forEach(function (inst) { inst.refreshColor(); });
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
  }

  document.addEventListener('DOMContentLoaded', init);
})();

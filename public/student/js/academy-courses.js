
document.addEventListener('DOMContentLoaded', () => {
  const rings = document.querySelectorAll('.course-ring');
  setTimeout(() => {
    rings.forEach(ring => {
      const val = ring.getAttribute('data-value');
      const offset = 251.2 - (251.2 * val) / 100;
      ring.style.strokeDashoffset = offset;
    });
  }, 100);
});

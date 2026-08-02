/**
 * Single canonical moving watermark (student name + photo) used by every
 * secure viewer — video, PDF/document, and image — so a leaked screenshot,
 * recording, or reshare always traces back to the exact student who viewed
 * it, and looks/behaves identically no matter which attachment type it's
 * layered on. Previously each viewer had its own slightly-drifted copy of
 * this function (different opacity, no lock phase, different timing); this
 * is the one implementation all of them now call.
 */
(function (global) {
  'use strict';

  // How long the watermark roams the frame before it locks into its
  // compact static position in the top-left corner.
  var STATIC_AFTER_MS = 4000;

  function mountSecureWatermark(container, studentName, studentPhotoUrl, opts) {
    opts = opts || {};
    var className = opts.className || 'secure-watermark-overlay';

    var canvas = document.createElement('canvas');
    canvas.className = className;
    canvas.style.position = 'absolute';
    canvas.style.inset = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    canvas.style.zIndex = '9999';
    container.appendChild(canvas);

    var ctx = canvas.getContext('2d');
    var raf = null;
    var photo = null;
    var startedAt = Date.now();

    if (studentPhotoUrl) {
      photo = new Image();
      photo.crossOrigin = 'anonymous';
      photo.src = studentPhotoUrl;
    }

    function resize() {
      var rect = container.getBoundingClientRect();
      canvas.width = rect.width;
      canvas.height = rect.height;
    }

    function draw() {
      var width = canvas.width;
      var height = canvas.height;
      if (width && height) {
        ctx.clearRect(0, 0, width, height);

        var elapsed = Date.now() - startedAt;

        if (elapsed < STATIC_AFTER_MS) {
          // Roaming phase: one full sweep across the frame during the window above.
          var t = (elapsed / STATIC_AFTER_MS) * Math.PI * 2;
          var wave = Math.cos(t) * -0.5 + 0.5;
          var x = width * (0.15 + 0.7 * wave);
          var y = height * 0.15;

          var photoSize = Math.max(28, Math.round(height * 0.09));

          ctx.globalAlpha = 0.55;
          if (photo && photo.complete && photo.naturalWidth) {
            ctx.save();
            ctx.beginPath();
            ctx.arc(x, y - photoSize * 0.75, photoSize / 2, 0, Math.PI * 2);
            ctx.clip();
            ctx.drawImage(photo, x - photoSize / 2, y - photoSize * 0.75 - photoSize / 2, photoSize, photoSize);
            ctx.restore();
          }

          var fontSize = Math.max(13, Math.round(height * 0.038));
          ctx.font = '700 ' + fontSize + 'px "Segoe UI", Tahoma, sans-serif';
          ctx.textAlign = 'center';
          ctx.lineWidth = Math.max(3, fontSize * 0.2);
          ctx.strokeStyle = '#000000';
          ctx.fillStyle = '#ffffff';
          ctx.shadowColor = 'rgba(0,0,0,0.6)';
          ctx.shadowBlur = 4;
          ctx.strokeText(studentName, x, y + fontSize * 0.3);
          ctx.fillText(studentName, x, y + fontSize * 0.3);
        } else {
          // Locked phase: small, thin, clean text pinned to the top-left corner.
          var pad = Math.max(10, Math.round(width * 0.015));
          var staticFontSize = Math.max(11, Math.round(height * 0.022));
          ctx.globalAlpha = 0.5;
          ctx.font = '400 ' + staticFontSize + 'px "Segoe UI", Tahoma, sans-serif';
          ctx.textAlign = 'left';
          ctx.lineWidth = Math.max(1, staticFontSize * 0.12);
          ctx.strokeStyle = 'rgba(0,0,0,0.55)';
          ctx.fillStyle = '#ffffff';
          ctx.shadowColor = 'rgba(0,0,0,0.45)';
          ctx.shadowBlur = 2;
          ctx.strokeText(studentName, pad, pad + staticFontSize);
          ctx.fillText(studentName, pad, pad + staticFontSize);
        }
      }
      raf = requestAnimationFrame(draw);
    }

    var resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);
    resize();
    draw();

    return function destroy() {
      if (raf) cancelAnimationFrame(raf);
      resizeObserver.disconnect();
      canvas.remove();
    };
  }

  global.mountSecureWatermark = mountSecureWatermark;
})(window);

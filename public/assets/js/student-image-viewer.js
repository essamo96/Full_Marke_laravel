/**
 * Secure image viewer: fetches an image attachment as raw bytes over an
 * authenticated request (never a plain <img src> whose native "save image
 * as" context-menu entry would hand out the untouched original), draws it
 * to a canvas, and overlays the same moving name+photo watermark used for
 * videos/PDFs so a reshared copy is traceable back to whoever viewed it.
 */
(function (global) {
  'use strict';

  function mountWatermark(container, studentName, studentPhotoUrl) {
    var canvas = document.createElement('canvas');
    canvas.className = 'img-watermark-overlay';
    canvas.style.position = 'absolute';
    canvas.style.inset = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    container.appendChild(canvas);

    var ctx = canvas.getContext('2d');
    var raf = null;
    var photo = null;

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

        var t = (Date.now() % 14000) / 14000 * Math.PI * 2;
        var wave = Math.cos(t) * -0.5 + 0.5;
        var x = width * (0.15 + 0.7 * wave);
        var y = height * 0.15;

        var photoSize = Math.max(24, Math.round(width * 0.05));

        ctx.globalAlpha = 0.4;
        if (photo && photo.complete && photo.naturalWidth) {
          ctx.save();
          ctx.beginPath();
          ctx.arc(x, y - photoSize * 0.75, photoSize / 2, 0, Math.PI * 2);
          ctx.clip();
          ctx.drawImage(photo, x - photoSize / 2, y - photoSize * 0.75 - photoSize / 2, photoSize, photoSize);
          ctx.restore();
        }

        var fontSize = Math.max(12, Math.round(width * 0.02));
        ctx.font = '700 ' + fontSize + 'px "Segoe UI", Tahoma, sans-serif';
        ctx.textAlign = 'center';
        ctx.lineWidth = Math.max(3, fontSize * 0.2);
        ctx.strokeStyle = '#000000';
        ctx.fillStyle = '#ffffff';
        ctx.strokeText(studentName, x, y + fontSize * 0.3);
        ctx.fillText(studentName, x, y + fontSize * 0.3);
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

  function applyDeterrents(container) {
    container.oncontextmenu = function () { return false; };
    container.style.userSelect = 'none';
    return function destroy() {};
  }

  /**
   * @param {Object} opts
   * @param {HTMLElement} opts.container - must be position:relative
   * @param {string} opts.fileUrl - route('student.resources.file', resource)
   * @param {string} opts.studentName
   * @param {string} [opts.studentPhotoUrl]
   * @param {function():void} [opts.onLoaded]
   * @param {function(string):void} [opts.onError]
   */
  function mountSecureImageViewer(opts) {
    var cleanupFns = [];
    var destroyed = false;
    var objectUrl = null;

    var canvas = document.createElement('canvas');
    canvas.className = 'img-viewer-canvas';
    canvas.style.display = 'block';
    canvas.style.width = '100%';
    canvas.style.height = 'auto';
    canvas.style.borderRadius = '8px';
    opts.container.appendChild(canvas);

    fetch(opts.fileUrl, { credentials: 'same-origin' })
      .then(function (res) {
        if (!res.ok) throw new Error('تعذّر تحميل الصورة');
        return res.blob();
      })
      .then(function (blob) {
        if (destroyed) return;
        objectUrl = URL.createObjectURL(blob);
        var img = new Image();
        img.onload = function () {
          if (destroyed) return;
          canvas.width = img.naturalWidth;
          canvas.height = img.naturalHeight;
          canvas.getContext('2d').drawImage(img, 0, 0);
          cleanupFns.push(mountWatermark(opts.container, opts.studentName, opts.studentPhotoUrl));
          cleanupFns.push(applyDeterrents(opts.container));
          if (opts.onLoaded) opts.onLoaded();
        };
        img.onerror = function () {
          if (opts.onError) opts.onError('تعذّر عرض الصورة');
        };
        img.src = objectUrl;
      })
      .catch(function (err) {
        if (opts.onError) opts.onError(err.message || 'حدث خطأ أثناء تحميل الصورة');
      });

    return function destroy() {
      destroyed = true;
      cleanupFns.forEach(function (fn) { fn(); });
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      canvas.remove();
    };
  }

  global.mountSecureImageViewer = mountSecureImageViewer;
})(window);

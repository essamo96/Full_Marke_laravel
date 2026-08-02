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
    return global.mountSecureWatermark(container, studentName, studentPhotoUrl, { className: 'img-watermark-overlay' });
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

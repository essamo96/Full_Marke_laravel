/**
 * Secure document viewer: fetches a PDF as raw bytes over an authenticated
 * request (never a plain <a href> the browser can hand to its own PDF
 * plugin), renders every page to a <canvas> via pdf.js, and overlays the
 * same moving name+photo watermark used on videos so a leaked screenshot or
 * photo of the screen can be traced back to whoever viewed it. This cannot
 * stop a screenshot or phone photo — nothing client-side can — but it removes
 * the trivial "right click > save" / native toolbar "download" escape hatch
 * and makes casual redistribution traceable.
 */
(function (global) {
  'use strict';

  if (global.pdfjsLib) {
    global.pdfjsLib.GlobalWorkerOptions.workerSrc = '/assets/vendor/pdfjs/pdf.worker.min.js';
  }

  function mountWatermark(container, studentName, studentPhotoUrl) {
    var canvas = document.createElement('canvas');
    canvas.className = 'doc-watermark-overlay';
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

        ctx.globalAlpha = 0.32;
        if (photo && photo.complete && photo.naturalWidth) {
          ctx.save();
          ctx.beginPath();
          ctx.arc(x, y - photoSize * 0.75, photoSize / 2, 0, Math.PI * 2);
          ctx.clip();
          ctx.drawImage(photo, x - photoSize / 2, y - photoSize * 0.75 - photoSize / 2, photoSize, photoSize);
          ctx.restore();
        }

        var fontSize = Math.max(12, Math.round(width * 0.018));
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

    function blockKeys(e) {
      // Best-effort: block the obvious "save this page" / print shortcuts while the
      // viewer has focus. Trivially bypassed by a determined user, same caveat as video.
      var key = (e.key || '').toLowerCase();
      if ((e.ctrlKey || e.metaKey) && (key === 's' || key === 'p')) {
        e.preventDefault();
      }
    }
    container.addEventListener('keydown', blockKeys);

    return function destroy() {
      container.removeEventListener('keydown', blockKeys);
    };
  }

  /**
   * @param {Object} opts
   * @param {HTMLElement} opts.container - scroll container the pages + watermark mount into (must be position:relative)
   * @param {string} opts.fileUrl - route('student.resources.file', resource)
   * @param {string} opts.studentName
   * @param {string} [opts.studentPhotoUrl]
   * @param {function():void} [opts.onLoaded]
   * @param {function(string):void} [opts.onError]
   */
  function mountSecureDocumentViewer(opts) {
    var cleanupFns = [];
    var destroyed = false;

    var pagesWrap = document.createElement('div');
    pagesWrap.className = 'doc-viewer-pages';
    opts.container.appendChild(pagesWrap);

    fetch(opts.fileUrl, { credentials: 'same-origin', headers: { Accept: 'application/pdf' } })
      .then(function (res) {
        if (!res.ok) throw new Error('تعذّر تحميل الملف');
        return res.arrayBuffer();
      })
      .then(function (buffer) {
        if (destroyed) return;
        if (!global.pdfjsLib) throw new Error('تعذّر تحميل عارض الملفات');

        return global.pdfjsLib.getDocument({ data: buffer }).promise.then(function (pdf) {
          var renderNext = function (pageNum) {
            if (destroyed || pageNum > pdf.numPages) {
              if (opts.onLoaded) opts.onLoaded();
              return;
            }
            return pdf.getPage(pageNum).then(function (page) {
              var viewport = page.getViewport({ scale: 1.5 });
              var canvas = document.createElement('canvas');
              canvas.className = 'doc-viewer-page';
              canvas.width = viewport.width;
              canvas.height = viewport.height;
              canvas.style.display = 'block';
              canvas.style.width = '100%';
              canvas.style.height = 'auto';
              canvas.style.marginBottom = '10px';
              canvas.style.borderRadius = '8px';
              pagesWrap.appendChild(canvas);

              return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise
                .then(function () { return renderNext(pageNum + 1); });
            });
          };
          return renderNext(1);
        });
      })
      .then(function () {
        if (destroyed) return;
        cleanupFns.push(mountWatermark(opts.container, opts.studentName, opts.studentPhotoUrl));
        cleanupFns.push(applyDeterrents(opts.container));
      })
      .catch(function (err) {
        if (opts.onError) opts.onError(err.message || 'حدث خطأ أثناء عرض الملف');
      });

    return function destroy() {
      destroyed = true;
      cleanupFns.forEach(function (fn) { fn(); });
      pagesWrap.remove();
    };
  }

  global.mountSecureDocumentViewer = mountSecureDocumentViewer;
})(window);

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
    return global.mountSecureWatermark(container, studentName, studentPhotoUrl, { className: 'doc-watermark-overlay' });
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

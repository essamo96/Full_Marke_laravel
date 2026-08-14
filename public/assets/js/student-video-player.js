/**
 * @deprecated Use student-secure-media-player.js (mountSecureMediaPlayer).
 * Thin shim kept so older cached pages still resolve the global.
 */
(function (global) {
  'use strict';
  if (!global.mountSecureMediaPlayer) {
    console.warn('Load student-secure-media-player.js before student-video-player.js');
  }
})(window);

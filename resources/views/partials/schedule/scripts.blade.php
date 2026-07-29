<script>
  /**
   * Weekly calendar day toggling.
   *
   * Only meaningful on phones, where each day collapses into an accordion.
   * Above that breakpoint every day is always visible, so the headers are
   * forced back to expanded and the click is ignored — otherwise a tap made on
   * a small screen would leave a day hidden after rotating to landscape.
   */
  (function () {
    const PHONE = window.matchMedia('(max-width: 767.98px)');
    const heads = document.querySelectorAll('.cal__head');
    if (!heads.length) return;

    heads.forEach(head => {
      head.addEventListener('click', () => {
        if (!PHONE.matches) return;
        head.setAttribute('aria-expanded', head.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
      });
    });

    function syncToViewport() {
      if (PHONE.matches) return; // keep whatever the user opened
      heads.forEach(h => h.setAttribute('aria-expanded', 'true'));
    }

    PHONE.addEventListener('change', syncToViewport);
    syncToViewport();
  })();
</script>

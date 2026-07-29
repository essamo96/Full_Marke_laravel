{{--
  Weekly calendar styling.

  Built on the theme custom properties so it renders correctly under the dark,
  gold and light themes with no per-theme overrides. The only fixed colours are
  the semantic status hues, which carry meaning rather than branding and are
  re-tinted for dark surfaces at the bottom of this file.
--}}
<style>
  .cal { --cal-gap: 14px; }

  .cal__grid {
    display: grid;
    gap: var(--cal-gap);
    /* Seven across on wide screens: the whole week at a glance. */
    grid-template-columns: repeat(7, minmax(0, 1fr));
  }

  /* Below ~1200px seven columns become unreadable slivers, so let them wrap
     into whatever fits rather than forcing a horizontal scroll. */
  @media (max-width: 1399.98px) {
    .cal__grid { grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
  }

  .cal__day {
    display: flex;
    flex-direction: column;
    border-radius: 16px;
    border: 1px solid var(--separator-color);
    background: var(--card-bg);
    overflow: hidden;
    transition: border-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
  }
  .cal__day:hover { border-color: var(--accent-color); }

  .cal__day.is-today {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 1px var(--accent-color), 0 10px 30px -12px var(--accent-glow);
  }
  .cal__day.is-empty { opacity: 0.72; }

  /* ---- Day header ---- */
  .cal__head {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 12px 14px;
    background: var(--bg-tertiary);
    border: 0;
    border-bottom: 1px solid var(--separator-color);
    color: var(--text-primary);
    font: inherit;
    text-align: start;
    cursor: default; /* only a real control on phones */
  }
  .cal__head-main { display: flex; align-items: center; gap: 8px; min-width: 0; }
  .cal__dayname { font-weight: 800; font-size: 0.86rem; }
  .cal__day.is-today .cal__dayname { color: var(--accent-color); }

  .cal__today-chip {
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.62rem;
    font-weight: 800;
    background: var(--accent-color);
    color: var(--bg-primary);
    white-space: nowrap;
  }

  .cal__head-meta { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
  .cal__count {
    min-width: 22px;
    padding: 1px 7px;
    border-radius: 999px;
    background: var(--input-bg);
    border: 1px solid var(--separator-color);
    color: var(--text-muted);
    font-size: 0.68rem;
    font-weight: 800;
    text-align: center;
    font-variant-numeric: tabular-nums;
  }
  .cal__chev { display: none; font-size: 0.7rem; color: var(--text-muted); transition: transform 0.25s ease; }

  /* ---- Sessions ---- */
  .cal__body { display: flex; flex-direction: column; gap: 8px; padding: 12px; flex: 1 1 auto; }

  .cal__session {
    position: relative;
    display: block;
    padding: 11px 12px;
    border-radius: 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--separator-color);
    border-inline-start: 3px solid var(--accent-color);
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }
  a.cal__session:hover {
    transform: translateY(-2px);
    border-color: var(--accent-color);
    box-shadow: var(--shadow-sm);
  }
  .cal__session.is-upcoming { border-inline-start-color: #f59e0b; }
  .cal__session.is-ended { border-inline-start-color: var(--text-muted); opacity: 0.7; }

  .cal__session-title {
    font-weight: 800;
    font-size: 0.8rem;
    color: var(--text-primary);
    line-height: 1.35;
    margin-bottom: 3px;
    overflow-wrap: anywhere;
  }
  .cal__session-sub {
    font-size: 0.71rem;
    color: var(--text-muted);
    margin-bottom: 3px;
    overflow-wrap: anywhere;
  }
  .cal__session-time {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.71rem;
    color: var(--text-secondary);
    font-variant-numeric: tabular-nums;
    margin-top: 5px;
  }

  .cal__status {
    display: inline-block;
    margin-top: 7px;
    padding: 2px 9px;
    border-radius: 999px;
    font-size: 0.63rem;
    font-weight: 800;
    background: rgba(34, 197, 94, 0.15);
    color: #16a34a;
  }
  .cal__session.is-upcoming .cal__status { background: rgba(245, 158, 11, 0.18); color: #b45309; }
  .cal__session.is-ended .cal__status { background: var(--input-bg); color: var(--text-muted); }

  /* ---- Live indicator ---- */
  .cal__session.is-live {
    border-color: #22c55e;
    box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.5);
  }
  .cal__live {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 6px;
    font-size: 0.63rem;
    font-weight: 800;
    color: #16a34a;
  }
  .cal__live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e;
    animation: calPulse 1.6s ease-in-out infinite;
  }
  @keyframes calPulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
    50% { opacity: 0.65; box-shadow: 0 0 0 5px rgba(34, 197, 94, 0); }
  }

  .cal__none {
    margin: 0;
    padding: 14px 6px;
    text-align: center;
    font-size: 0.72rem;
    color: var(--text-muted);
  }

  /* ════ Phones: stacked accordion, today first and open ════ */
  @media (max-width: 767.98px) {
    .cal__grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }

    /* Reorder so today leads, then the rest of the week in sequence. */
    .cal__day { order: var(--cal-order, 0); }

    .cal__head { cursor: pointer; padding: 14px; }
    .cal__chev { display: inline-block; }
    .cal__head[aria-expanded="true"] .cal__chev { transform: rotate(180deg); }

    /* Collapsed by default; today's panel ships expanded. Plain show/hide
       rather than a height transition — animating to intrinsic height needs
       measured values, and a half-working animation is worse than none. */
    .cal__head[aria-expanded="false"] + .cal__body { display: none; }
    .cal__head[aria-expanded="true"] + .cal__body { display: flex; flex-direction: column; }

    .cal__session { padding: 12px 13px; }
    .cal__session-title { font-size: 0.85rem; }
  }

  /* Semantic hues need the lighter end of each scale on dark surfaces. */
  .theme-dark .cal__status,
  .theme-gold .cal__status { color: #4ade80; }
  .theme-dark .cal__session.is-upcoming .cal__status,
  .theme-gold .cal__session.is-upcoming .cal__status { color: #fbbf24; }
  .theme-dark .cal__live,
  .theme-gold .cal__live { color: #4ade80; }

  @media (prefers-reduced-motion: reduce) {
    .cal__day, .cal__session, .cal__body, .cal__chev { transition: none; }
    .cal__live-dot { animation: none; }
  }
</style>

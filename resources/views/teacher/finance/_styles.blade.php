{{--
  Finance section styling.

  Built entirely on the theme custom properties (--accent-color, --card-bg,
  --text-*, --separator-color …) so the same markup renders correctly under the
  dark, gold and light themes without per-theme overrides. The only fixed
  colours are the semantic status hues (green = collected, amber = pending,
  red = outstanding), which carry meaning rather than branding; each is used at
  a tint that stays legible on both dark and light surfaces.
--}}
<style>
  .finance-layout {
    display: grid;
    grid-template-columns: 232px minmax(0, 1fr);
    gap: 20px;
    align-items: start;
  }
  @media (max-width: 991.98px) {
    .finance-layout { grid-template-columns: minmax(0, 1fr); }
  }

  /* ---- Section nav ---- */
  .finance-nav { position: sticky; top: 20px; }
  @media (max-width: 991.98px) { .finance-nav { position: static; } }

  .finance-nav__title {
    font-weight: 800;
    font-size: 0.82rem;
    color: var(--text-muted);
    letter-spacing: 0.04em;
    padding: 6px 10px 12px;
    border-bottom: 1px solid var(--separator-color);
    margin-bottom: 8px;
  }
  .finance-nav__list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  @media (max-width: 991.98px) {
    .finance-nav__list {
      flex-direction: row;
      overflow-x: auto;
      gap: 6px;
      scrollbar-width: none;
    }
    .finance-nav__list::-webkit-scrollbar { display: none; }
    .finance-nav__item { white-space: nowrap; }
  }

  .finance-nav__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 12px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.86rem;
    font-weight: 600;
    border: 1px solid transparent;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
  }
  .finance-nav__item i { font-size: 1rem; opacity: 0.85; }
  .finance-nav__item:hover {
    color: var(--text-primary);
    background: var(--input-bg);
  }
  .finance-nav__item.active {
    color: var(--accent-color);
    background: var(--accent-glow);
    border-color: var(--accent-color);
  }

  /* ---- Stat tiles ---- */
  .finance-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
  }
  .finance-stat {
    padding: 16px 18px;
    border-radius: 16px;
    border: 1px solid var(--card-border);
    background: var(--card-bg);
    box-shadow: var(--shadow-sm);
  }
  .finance-stat__label {
    font-size: 0.72rem;
    color: var(--text-muted);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
  }
  .finance-stat__value {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--text-primary);
    font-variant-numeric: tabular-nums;
    line-height: 1.15;
  }
  .finance-stat__unit { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); }
  .finance-stat__hint { font-size: 0.7rem; color: var(--text-muted); margin-top: 6px; }

  .finance-stat--collected { border-inline-start: 3px solid #22c55e; }
  .finance-stat--outstanding { border-inline-start: 3px solid #ef4444; }
  .finance-stat--pending { border-inline-start: 3px solid #f59e0b; }
  .finance-stat--expected { border-inline-start: 3px solid var(--accent-color); }

  .finance-amount-positive { color: #22c55e; }
  .finance-amount-negative { color: #ef4444; }
  .finance-amount-pending { color: #f59e0b; }

  /* ---- Table ---- */
  .finance-card {
    border-radius: 18px;
    border: 1px solid var(--card-border);
    background: var(--card-bg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
  }
  .finance-card__head {
    padding: 16px 18px;
    border-bottom: 1px solid var(--separator-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }
  .finance-card__title {
    font-weight: 800;
    font-size: 0.95rem;
    color: var(--text-primary);
    margin: 0;
  }

  /* Wide tables scroll inside their own container so the page never does. */
  .finance-table-wrap { overflow-x: auto; }
  .finance-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
    font-size: 0.84rem;
  }
  .finance-table th {
    text-align: start;
    padding: 11px 16px;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--bg-tertiary);
    white-space: nowrap;
    border-bottom: 1px solid var(--separator-color);
  }
  .finance-table td {
    padding: 12px 16px;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--separator-color);
    vertical-align: middle;
  }
  .finance-table tbody tr:last-child td { border-bottom: 0; }
  .finance-table tbody tr:hover td { background: var(--input-bg); }
  .finance-table .num {
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    font-weight: 700;
  }
  .finance-table .name { color: var(--text-primary); font-weight: 700; }

  /* ---- Progress ---- */
  .finance-progress {
    height: 6px;
    min-width: 84px;
    border-radius: 999px;
    background: var(--separator-color);
    overflow: hidden;
  }
  .finance-progress__fill {
    height: 100%;
    border-radius: 999px;
    background: #22c55e;
    transition: width 0.3s ease;
  }
  .finance-progress__fill.is-partial { background: #f59e0b; }
  .finance-progress__fill.is-none { background: #ef4444; }

  /* ---- Badges ---- */
  .finance-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
  }
  .finance-badge--settled { background: rgba(34, 197, 94, 0.14); color: #16a34a; }
  .finance-badge--due { background: rgba(239, 68, 68, 0.14); color: #dc2626; }
  .finance-badge--pending { background: rgba(245, 158, 11, 0.16); color: #d97706; }

  /* The light theme's white surfaces need the darker end of each hue; the dark
     and gold themes need the lighter end to stay readable. */
  .theme-dark .finance-badge--settled,
  .theme-gold .finance-badge--settled { color: #4ade80; }
  .theme-dark .finance-badge--due,
  .theme-gold .finance-badge--due { color: #f87171; }
  .theme-dark .finance-badge--pending,
  .theme-gold .finance-badge--pending { color: #fbbf24; }
  .theme-dark .finance-amount-positive,
  .theme-gold .finance-amount-positive { color: #4ade80; }
  .theme-dark .finance-amount-negative,
  .theme-gold .finance-amount-negative { color: #f87171; }
  .theme-dark .finance-amount-pending,
  .theme-gold .finance-amount-pending { color: #fbbf24; }

  .finance-empty {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
    font-size: 0.88rem;
  }

  .finance-link { color: var(--accent-color); text-decoration: none; font-weight: 700; }
  .finance-link:hover { text-decoration: underline; }
</style>

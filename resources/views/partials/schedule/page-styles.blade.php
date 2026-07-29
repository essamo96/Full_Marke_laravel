{{--
  Chrome around the calendar (header, summary tiles, group cards). Shared by
  the teacher and student schedule pages; theme-variable driven throughout.
--}}
<style>
  .sched-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 22px;
  }
  .sched-title {
    font-size: clamp(1.25rem, 2.4vw, 1.6rem);
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 4px;
  }
  .sched-subtitle { margin: 0; font-size: 0.82rem; color: var(--text-muted); }

  .sched-summary { display: flex; gap: 10px; flex-wrap: wrap; }
  .sched-summary__item {
    min-width: 84px;
    padding: 10px 16px;
    border-radius: 14px;
    background: var(--card-bg);
    border: 1px solid var(--separator-color);
    text-align: center;
  }
  .sched-summary__value {
    display: block;
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--accent-color);
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
  }
  .sched-summary__label { display: block; font-size: 0.67rem; color: var(--text-muted); margin-top: 3px; }

  .sched-section-title {
    font-size: 1.02rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 32px 0 14px;
  }

  /* Auto-fitting cards: no breakpoint guessing, they simply reflow. */
  .sched-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
  }

  .sched-card {
    display: block;
    padding: 16px 18px;
    border-radius: 16px;
    background: var(--card-bg);
    border: 1px solid var(--separator-color);
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }
  .sched-card:hover {
    transform: translateY(-3px);
    border-color: var(--accent-color);
    box-shadow: var(--shadow-md);
  }
  .sched-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 6px;
  }
  .sched-card__title {
    font-size: 0.92rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    overflow-wrap: anywhere;
  }
  .sched-card__subject { font-size: 0.76rem; color: var(--text-muted); margin: 0 0 10px; }

  .sched-card__days { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 10px; }
  .sched-daychip {
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.64rem;
    font-weight: 700;
    background: var(--input-bg);
    border: 1px solid var(--separator-color);
    color: var(--text-secondary);
  }
  .sched-daychip.is-today {
    background: var(--accent-glow);
    border-color: var(--accent-color);
    color: var(--accent-color);
  }

  .sched-card__meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.74rem;
    color: var(--text-secondary);
    margin-top: 5px;
    font-variant-numeric: tabular-nums;
  }
  .sched-card__meta i { color: var(--text-muted); }

  .sched-pill {
    flex-shrink: 0;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 800;
    white-space: nowrap;
  }
  .sched-pill--active { background: rgba(34, 197, 94, 0.15); color: #16a34a; }
  .sched-pill--upcoming { background: rgba(245, 158, 11, 0.18); color: #b45309; }
  .sched-pill--ended { background: var(--input-bg); color: var(--text-muted); }

  .theme-dark .sched-pill--active,
  .theme-gold .sched-pill--active { color: #4ade80; }
  .theme-dark .sched-pill--upcoming,
  .theme-gold .sched-pill--upcoming { color: #fbbf24; }

  .sched-empty {
    grid-column: 1 / -1;
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
    border-radius: 16px;
    background: var(--card-bg);
    border: 1px dashed var(--separator-color);
  }

  @media (max-width: 575.98px) {
    .sched-header { align-items: flex-start; }
    .sched-summary { width: 100%; }
    .sched-summary__item { flex: 1 1 0; min-width: 0; padding: 10px 8px; }
    .sched-cards { grid-template-columns: minmax(0, 1fr); }
  }

  @media (prefers-reduced-motion: reduce) {
    .sched-card { transition: none; }
  }
</style>

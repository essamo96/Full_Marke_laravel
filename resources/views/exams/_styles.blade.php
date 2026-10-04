{{-- Theme-aware action buttons (.tbtn) and exam preview cards (.xp-*).
     Colours come from the page CSS variables (teacher dark/light/gold, guest
     layout) and fall back to Metronic-friendly light values. --}}
@once
<style>
  .tbtn {
    --tb: var(--accent-color, #0284c7);
    display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
    min-height: 40px; padding: .45rem .95rem; border-radius: 10px;
    border: 1px solid color-mix(in srgb, var(--tb) 45%, transparent);
    background: color-mix(in srgb, var(--tb) 12%, transparent);
    color: var(--tb); font-size: .875rem; font-weight: 600; line-height: 1;
    text-decoration: none; white-space: nowrap; cursor: pointer;
    transition: background-color .18s ease, border-color .18s ease, box-shadow .18s ease, color .18s ease;
  }
  .tbtn:hover { background: var(--tb); border-color: var(--tb); color: #fff; box-shadow: 0 6px 16px -6px var(--tb); }
  .tbtn:focus-visible { outline: 3px solid color-mix(in srgb, var(--tb) 55%, transparent); outline-offset: 2px; }
  .tbtn:active { box-shadow: none; }
  .tbtn i { font-size: 1.05rem; line-height: 1; }
  .tbtn--solid { background: var(--tb); border-color: var(--tb); color: #fff; }
  .tbtn--solid:hover { filter: brightness(1.08); }
  .tbtn--preview { --tb: #16a34a; }
  .tbtn--pdf { --tb: #dc2626; }
  .tbtn--edit { --tb: #7c3aed; }
  .tbtn--neutral { --tb: var(--text-secondary, #64748b); }
  .tbtn--share { --tb: #d97706; }
  .tbtn--danger { --tb: #dc2626; }
  .tbtn--ok { --tb: #16a34a; }
  .tbtn--icon { min-width: 40px; padding: .45rem; }
  .tbtn--sm { min-height: 36px; padding: .35rem .7rem; font-size: .8rem; }
  .tbtn-group { display: flex; flex-wrap: wrap; gap: .5rem; }
  @media (max-width: 575.98px) { .tbtn:not(.tbtn--icon) { flex: 1 1 calc(50% - .5rem); } }
  @media (prefers-reduced-motion: reduce) { .tbtn { transition: none; } }

  .xp-card { background: var(--card-bg, #fff); color: var(--text-primary, #212529);
    border: 1px solid var(--card-border, #e4e6ef); border-radius: 14px; padding: 1.25rem; margin-bottom: 1rem;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(15,23,42,.04)); }
  .xp-opt { display: flex; align-items: center; gap: .6rem; padding: .7rem .9rem; margin-bottom: .5rem;
    border-radius: 10px; border: 1px solid var(--card-border, #e4e6ef); }
  .xp-opt--ok { background: color-mix(in srgb, #22c55e 16%, transparent); border-color: color-mix(in srgb, #22c55e 55%, transparent); font-weight: 700; }
  .xp-opt--ok .xp-mark { color: #16a34a; }
  [data-theme="dark"] .xp-opt--ok .xp-mark, .theme-dark .xp-opt--ok .xp-mark { color: #4ade80; }
  .xp-mark { color: var(--text-muted, #94a3b8); font-size: 1.1rem; }
  .xp-count { margin-inline-start: auto; font-size: .75rem; font-weight: 700; padding: .2rem .55rem; border-radius: 999px; color: #fff; }
  .xp-count--ok { background: #16a34a; } .xp-count--bad { background: #dc2626; }
  .xp-pts { background: color-mix(in srgb, var(--accent-color, #0284c7) 14%, transparent); color: var(--accent-color, #0284c7);
    font-weight: 700; font-size: .8rem; padding: .25rem .6rem; border-radius: 999px; }
</style>
@endonce

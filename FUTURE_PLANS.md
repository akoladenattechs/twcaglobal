# Future Plans & Roadmap

> Tracked ideas and planned enhancements that are **not yet implemented**.
> Nothing in this file is live code — entries are added here when we decide to defer a feature.

---

## 📝 Future Plan: Frontend Language Translation System

**Status:** Planned (not started) · **Decided:** 2026-08-03

### Goal

Let visitors view the public frontend in **English**, **French**, or **Yoruba** via a
language switcher in the navigation, with browser auto-detection on first visit.

### Current State (as of 2026-08-03)

- No `lang/` directory exists — zero `__()` / `trans()` / `@lang` usage in app code or views.
- All 59 Blade views contain hardcoded English UI strings.
- `config/app.php` has `locale` / `fallback_locale` set to `en` but they are unused.
- Site settings (DB) store several content headings (e.g. `home_sermons_heading`,
  `radio_heading`) — these are DB content, not code strings.

### Decisions Locked In

1. **Scope:** UI chrome only — menu items, buttons, labels, section headings, form
   placeholders, empty states, footer headings. **Not** sermon/devotional/event content.
2. **Languages:** French (`fr`) and Yoruba (`yo`), with English (`en`) as default + fallback.
3. **Switching:** Nav language switcher (EN | FR | YO) **+** browser `Accept-Language`
   auto-detect on first visit (anything other than fr/yo → English).
4. **DB-stored headings (option b):** Empty the default heading values in `site_settings`
   so views fall back to translated strings (`DB value ?: __('messages.key')`).
   Admin-entered custom headings still **win** over translations. Reversible, no schema change.
5. **Admin panel stays 100% English** — staff-only, not translated.

### Implementation Sketch

| Step | Work |
| --- | --- |
| 1 | Create `lang/en/messages.php`, `lang/fr/messages.php`, `lang/yo/messages.php` (~45–60 keys each) |
| 2 | `Localization` middleware: auto-detect via `Accept-Language`, persist choice in session + cookie |
| 3 | `GET /language/{locale}` route + EN/FR/YO dropdown in `layouts/app.blade.php` nav |
| 4 | Convert ~15–20 frontend views to `{{ __('messages.key') }}` |
| 5 | DB-heading fallback: `setting value ?: __('messages.key')` + clear default heading values in `site_settings` |
| 6 | JS string bridge: `window.__translations` / `data-i18n` for JS-rendered labels (search, filters) |
| 7 | Validate: `view:clear && view:cache`, lint, browser smoke test in EN/FR/YO |

### Effort Estimate

~2.5–3.5 hours in one focused session. No migrations, fully reversible.

### Caveats

- **Yoruba requires native-speaker review** before launch (church terminology sensitivity).
  English remains the fallback, so it never blocks deployment.
- Out of scope for this phase: translated DB *content* (sermons, devotionals, events),
  URL prefixes (`/fr/...`), hreflang/SEO per-language indexing — possible later phase.

---

## ✍️ Adding to this file

- Keep entries concise and factual; record the decision date.
- Mark an entry `**Status:** In progress` when implementation begins, then move it to
  the README/DEPLOY docs once shipped.

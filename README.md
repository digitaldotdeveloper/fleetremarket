# Fleet Remarketing — Website Design Concepts

Live preview dashboard for the new **Fleet Remarketing** website. Clients open the dashboard and choose between design directions, each a full, interactive static site.

🔗 **Live:** https://digitaldotdeveloper.github.io/fleetremarket/

---

## Concepts

| # | Name | Theme | Status |
|---|------|-------|--------|
| **01** | The 300% Guarantee | Light · Green, signature car-on-a-road scrollbar | ✅ Live |
| **02** | Premium Dark Fintech | Dark glassmorphism, neon-green + amber, light/dark toggle | ✅ Live · Recommended |
| **03** | Blue Direction | Blue variant with an FAQ section | 🔒 Under construction (locked in dashboard) |

**Direct links**
- Dashboard — [`index.html`](https://digitaldotdeveloper.github.io/fleetremarket/)
- Concept 1 — [`concept-1.html`](https://digitaldotdeveloper.github.io/fleetremarket/concept-1.html)
- Concept 2 — [`concept-2/`](https://digitaldotdeveloper.github.io/fleetremarket/concept-2/index.html)

---

## Highlights (Concept 2)

- Full **light / dark theme** system with a persistent toggle
- Glass cards, subtle grid + glow background, animated hero
- **Neon-green primary + amber accent** color system
- Scroll-linked vehicle slider (cards move as you scroll)
- Fully **mobile-optimized** (compact hero, form-first, readable over photos)

---

## Project structure

```
.
├── index.html          # Client dashboard (concept chooser)
├── concept-1.html      # Concept 1 (light / green)
├── concept-2/          # Concept 2 (dark fintech) + its assets
├── concept-3/          # Concept 3 (blue) — locked / in progress
├── previews/           # Thumbnail screenshots used on the dashboard
└── assets/             # Shared images (logo, photos, badges, QR)
```

Every page is **self-contained HTML/CSS/JS** — no build step, no dependencies. Just static files served by GitHub Pages.

---

## Updating the site

```bash
git add -A
git commit -m "update"
git push
```

GitHub Pages redeploys automatically within ~1 minute.

---

© 2026 Fleet Remarketing — *Give us the VIN, we'll handle your selling nationwide.*

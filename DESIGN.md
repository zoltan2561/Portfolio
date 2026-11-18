# Design — pzoli.com portfolio

World: **Matrix phosphor terminal** (owner-pinned: "a matrixos stílus maradjon"). Near-black ground, green phosphor, digital rain canvas behind the page. Applies to the public portfolio (`/`, `/skills`); `/randi` and `/statistics` keep their own looks.

Source of truth: `public/portfolio-v2.css` (tokens on `.portfolio-v2`), `resources/views/portfolio/home.blade.php`, `public/script.js` (`setupMatrix`, `setupCareerStack`).

## Tokens

| Token | Value | Use |
|---|---|---|
| `--ground` | `#030906` | page, nav, inputs |
| `--ground-2` | `#07130c` | raised panels (stack, form, skill groups) |
| `--ground-3` | `#0c1f14` | hover fills |
| `--line` | `#17331f` | quiet rules |
| `--line-strong` | `#2b5a3c` | borders, timeline spine |
| `--ink` | `#ecfdf2` | headings, strong text |
| `--text` | `#c0d8c9` | body text |
| `--muted` | `#93b39f` | captions, secondary |
| `--phosphor` | `#3dff8a` | the one accent: primary button, lit layers, active marker, badges |
| `--phosphor-dim` | `#1f7a45` | "seen" layer borders, hover borders |
| `--phosphor-soft` | `#9cf6c0` | links, place names |
| `--phosphor-ink` | `#021409` | text on phosphor |

Colour strategy: restrained ground + one committed accent. Phosphor fills are solid, never glows or gradients.

## Type

- **JetBrains Mono** (variable, self-hosted in `public/fonts/`, OFL): headings, nav, buttons, labels, data, badges. H1 `clamp(2.7rem, 6.2vw, 5.4rem)` / 1.02 / -0.04em / 700.
- **Onest** (variable, self-hosted, OFL): reading text, 17px / 1.7, measure ≤ 68ch.
- Both ship latin + latin-ext subsets (Hungarian ő/ű).

## Shape and depth

2px radii everywhere; 1px rules; no shadows, no glass, no blur. Hierarchy comes from fill (phosphor vs. ground) and rules, not elevation. Portrait: grayscale luminosity-blended over deep green with 3px scanlines.

## Signature: the career stack

Five layers, top to bottom: Szoftver, Infrastruktúra, Üzemeltetés, Support, Hardver. Each timeline step (`.pz-step`, `data-layers`) names the layers it touched. On scroll, the step crossing 45% of the viewport becomes active: its layers get `.is-lit` (solid phosphor, nudged 6px), every layer touched so far gets `.is-seen`. When the closing note enters view, every seen layer lights. Desktop: sticky panel beside the timeline. ≤900px: sticky five-cell strip under the nav (labels hidden ≤420px). Reduced motion: no transitions, state still updates. Matrix canvas is hidden under reduced motion.

## Components

- Buttons: `.v2-button` (phosphor fill) and `.v2-button-secondary` (outline); 50px min height, mono 0.9rem, optional 18px icon.
- Badges: `.pz-badge` (outline) and `.pz-badge-now` (phosphor fill).
- Tags/chips: mono 0.72–0.74rem, 1px border, 2px radius; layer chips carry a 6px phosphor square.
- Icons: `x-portfolio.icon`, 24-grid stroke icons from `config/portfolio_icons.php`, 1.6 stroke.
- Lists over cards: knowledge areas and approach points are ruled rows, not boxed cards.

## Content rules

First person, informal Hungarian, plain and human. Career shown without dates. No invented metrics, clients or testimonials.

# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- Recruiters, hiring managers and tech leads who land on pzoli.com from LinkedIn, GitHub or a CV link and want to judge in under a minute who Papp Zoltán is, what he has done and whether he is worth a call.
- Small-business owners and prospective clients (mostly Hungarian) who want to know whether he can build or run their system; the commercial offer itself lives on SzoftLab (szoftlab.hu).
- Peers and acquaintances who want to get in touch.

## Product Purpose

Personal professional portfolio and digital business card of Papp Zoltán (pzoli.com). It shows a developer whose career runs across the whole IT stack — hardware, support, system administration, infrastructure and software development — and makes it easy to contact him. Success: a visitor understands the breadth of experience, sees the career path at a glance, and either writes, downloads the CV, or goes on to SzoftLab.

## Positioning

Not "a Laravel developer". A developer who started by keeping machines alive and supporting users, then ran infrastructure, then moved into software — so he builds systems thinking about how they run in production and how the people using them feel. The career path itself is the proof.

## Operating Context

- Laravel app; content lives in `resources/content/portfolio.json` (hu + en) and `resources/content/portfolio-references.json`; views in `resources/views/portfolio/`, layout `resources/views/layouts/portfolio.blade.php`; static CSS/JS in `public/`.
- Bilingual: Hungarian default, English via `?lang=en`.
- Pages: home (`/`), skills (`/skills`), statistics (password-protected, separate look), contact form (POST `/contact`). The `/randi` module is separate and out of scope.

## Capabilities and Constraints

- Must keep: contact form (CSRF, honeypot, throttle), language switch, SEO meta / JSON-LD / hreflang, CV PDF download (`public/cv.pdf`), links to SzoftLab and social profiles, visitor counter, statistics page.
- No build-step requirement for the public site today (plain CSS/JS in `public/`); keep it deployable on the current shared hosting.
- Career timeline is shown **without dates** (owner's decision); order only.

## Brand Commitments

- Name: Papp Zoltán, short brand "PZ". Company: SzoftLab.
- Voice: first person, informal Hungarian ("te"), plain and human, a little self-ironic, no buzzwords or corporate superlatives.

## Evidence on Hand

- Career order (from the site, owner-confirmed): interest in IT since age 6 → maths–IT secondary school (Nagydobrony) → system administrator, Hétvezér Iskola → IT supporter, Hoya → junior developer, Hoya → medior developer, Oxford One → lead developer, SzoftLab. Programtervező informatikus BSc, Nyíregyházi Egyetem. International work incl. a posting in Thailand. English: C1 (advanced).
- CV PDF: `public/cv.pdf` (older version; lists freelance frontend work and Hoya duties: PHP production systems, Oracle PL/SQL, Power BI/Automate, Docker, Linux/Samba, Meraki).
- Projects: FoodPRO (foodpro.shop), NapiInfo (napiinfo.com), SzervizPRO (szerviz.pro) plus references in `portfolio-references.json`; cover SVGs in `public/assets/portfolio/`.
- Portrait photos in `public/icons/profile-2026-*`.
- Absent, must not be fabricated: employment dates, client testimonials, client names beyond those listed, metrics or numbers other than "10 years in IT".

## Product Principles

1. The career path is the headline; the story supports it, never the reverse.
2. Breadth is shown, not claimed: each area is tied to a real role or project.
3. Talk like a person, not a CV generator.
4. One clear next step: write to Zoli (or SzoftLab for business work).

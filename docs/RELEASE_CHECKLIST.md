# Portfolio release checklist (final)

## 1) Production .env checklist
- Kötelezően ellenőrizd, hogy a következők szerepelnek-e a production `.env`-ben:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://...`
  - Mail:
    - `MAIL_MAILER`
    - `MAIL_HOST`
    - `MAIL_PORT`
    - `MAIL_USERNAME`
    - `MAIL_PASSWORD`
    - `MAIL_FROM_ADDRESS`
    - `MAIL_FROM_NAME`
  - `SESSION_DRIVER` (ajánlott: file vagy sharelt cache ha több szerver van)
  - `CACHE_STORE` (ajánlott: ugyanaz, mint a session strategy)
  - `STATISTICS_PASSWORD` (KÖTELEZŐ a használat előtt, production-only)
- `.env.example`-ban a kulcs legyen jelen dokumentáció célból, de `.env` értékét ne commitoljuk itt.

## 2) Deploy előtti ellenőrzések
- `git status` tiszta? (vagy csak célzott módosítások látszódnak)
- `.gitignore` ellenőrzés a nagy asset kivételekre:
  - `public/icons/originals/`
  - `public/icons/og-test-*.jpg`
- Biztonsági fejlécekterv és middleware kész (következő pont).
- Teszt környezetben futtasd:
  - `php -l app/Http/Middleware/SecurityHeaders.php bootstrap/app.php`
  - `php artisan test`
- Ha érintették a route-okat/HTTP stack-et:
  - `php artisan route:list`
- Cache/build döntés ellenőrzése:
  - `php artisan config:clear`
  - `php artisan cache:clear`

## 3) Deploy utáni parancsok
- `php artisan optimize:clear`
- `php artisan config:cache`
- `php artisan route:cache` (ha statikus route-okat használsz)
- `php artisan view:cache`
- Szükség esetén storage és permission ellenőrzés.

## 4) Manuális smoke test (élő ellenőrzés)
- Főoldal: `/`
- Nyelvi nézet: `/?lang=en`
- Skills: `/skills`
- Statisztika: `/statistics` (jelszó nélkül login képernyő)
- Ikonok és OG:
  - `/favicon.ico`
  - `/icons/og-1200x630.jpg`
- Kontakt űrlap:
  - `POST /contact` érvényes adatokkal
  - hibás mezőkkel validációs visszajelzés
  - honeypot/ rejtett mező visszautasítás (ha aktív)
  - túl gyors ismétléssel rate-limit reakció
- Statistikai bejelentkezés:
  - rossz jelszó -> visszautasítás
  - jó jelszó -> dashboard
  - rövid időn belüli túl sok próbálkozás -> throttle
- Social preview:
  - `og:image` URL és méretek ellenőrzése (1200x630)
  - `twitter:card` + twitter title/description
  - LinkedIn/Twitter share preview ellenőrzés
- SEO fájlok:
  - `/robots.txt`
  - `/sitemap.xml`

## 5) Biztonsági fejlécek ellenőrzése
- Ellenőrizd, hogy a válasz tartalmazza:
  - `X-Frame-Options: SAMEORIGIN`
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- CSP:
  - most nem kötelezően élesített; először report-only auditot készíts
    és csak majd azt követően vezess be szigorú szabályt.

## 6) Deployból kizárandó képfájlok
- `public/icons/originals/*`  (backup fájlok)
- `public/icons/og-test-*.jpg`  (teszt méretkísérleti képek)
- Ha bármelyik később mégis verziókövetésre kellene, csak explicit jóváhagyás után.

## 7) Gyors visszajelentés
- A home oldali copy jelenleg nem változott ebben a körben.
- Következő ajánlott apró lépés: biztonsági fejlécek működésének élő ellenőrzése (browser/network).

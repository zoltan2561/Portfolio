# Személyes randimeghívó – telepítés és használat

A `/randi` az adminfelületre irányít; belépés nélkül az adminbelépő jelenik meg. A magyar, mobilra készült névbekérő a védett `/randi/admin/new` útvonalon keresztnevet kér, majd egy 30 napig érvényes `/randi/{token}` linket hoz létre a külön SQLite-adatbázisban. Ezt küldd a címzettnek: a megszólításban megjelenik a neve, a válasza pedig a saját meghívójához kapcsolódik. Azonos nevű címzettek és új meghívók külön sorok maradnak. A mentés nélküli bemutató (`/randi/demo`) szintén adminbelépést igényel. Nyilvánosan csak az adminbelépő és a személyes meghívólinkek érhetők el. A meghívás → igen → időpont → program → összegzés → végleges beküldés folyamat végén a válasz külön SQLite-adatbázisba kerül. A fix elutasítás ugyanezen mentést használja, további adat nélkül. JavaScript nélkül szerveroldali űrlaplépések működnek.

## Ellenőrzött környezet és követelmények

- A meglévő Laravel **12.57.0**, PHP **8.4.21**, PDO SQLite környezetben készült és lett ellenőrizve. A projekt minimuma PHP 8.2; PHP 8.2/8.3 alatt ezt a munkát külön nem futtattuk. Az írási zárolás ezért explicit `BEGIN IMMEDIATE`-et használ, nem a csak újabb PHP-verzióban működő Laravel tranzakciómódra támaszkodik.
- Szükséges a projekt meglévő PHP-bővítménykészlete, különösen `pdo_sqlite`, `mbstring`, `openssl` és `fileinfo`. A mellékelt kép újragenerálásához `gd` és WebP-támogatás kell; az elkészült képek kiszolgálásához nem.
- Az ellenőrzött SQLite-verzió **3.51.3**. A `randi:backup` parancshoz SQLite 3.27 vagy újabb kell (`VACUUM INTO`). A használt SQLite-verzió a PHP PDO driveré, nem feltétlenül a gépen esetleg telepített `sqlite3` CLI-é.
- Egy írható, helyi privát adatkönyvtár kell. Az adatbázisfájl **és a könyvtára** legyen írható a PHP felhasználója számára, hogy a rollback journal is létrejöhessen. NFS/hálózati megosztás és több alkalmazásszerver közös SQLite-fájlja nincs ellenőrizve.
- Nincs új futásidejű Composer/npm függőség, Redis, Docker, queue worker, külső adatbázis vagy levélküldési követelmény. A modul helyi Blade + CSS + vanilla JavaScript, saját layouttal és követőkódok nélkül.

## Konfiguráció

A meglévő `.env` titkait és az alapértelmezett `DB_CONNECTION` / `DB_DATABASE` értékeit őrizd meg. A modul a **`randi`** nevű külön kapcsolaton működik.

```dotenv
RANDI_DB_PATH=/srv/pzoli-private/randi/randi.sqlite
RANDI_BASE_URL=https://pzoli.com
RANDI_ADMIN_PASSWORD_HASH='a_parancs_altal_generalt_teljes_hash'
```

- `RANDI_DB_PATH`: abszolút fájlútvonal. Ha nincs megadva: `storage/app/private/randi/randi.sqlite`. A kód elutasítja a relatív és `public/` alatti útvonalat, a meglévő symlinkek feloldását is ellenőrzi. `public/storage` alá se tedd. A valódi fájl nem kerül Gitbe.
- `RANDI_BASE_URL`: a kiküldhető linkek rögzített eredete. Fejlesztéskor például `http://127.0.0.1:8000`, élesben `https://pzoli.com`. A link nem a bejövő Host fejléc alapján készül.
- `RANDI_ADMIN_PASSWORD_HASH`: csak `password_hash()`-ból származó hash. Nincs alapértelmezett jelszó, regisztráció vagy query paraméteres belépés. Üres/hibás hash mellett nem lehet belépni. A hash változása a régi adminmunkameneteket is érvényteleníti.
- Éles HTTPS alatt `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`. A modul a saját session cookie-ján HTTPS-kéréskor kódból is bekapcsolja a Secure, HttpOnly és SameSite=Lax védelmet. TLS-t lezáró proxy mögött a Laravel trusted-proxy/HTTPS érzékelését a tárhelyhez helyesen kell beállítani.
- A meglévő fájlos `SESSION_DRIVER=file`, `CACHE_STORE=file` megfelelő. Sessionnél 120 perces inaktivitási élettartam ajánlott. Ha a környezet adatbázis-sessiont/cache-t használ, annak meglévő tábláit továbbra is a fő alkalmazás kezeli, nem ez a modul.

Privát könyvtárnál például `0750`, adatbázisnál `0640`, a PHP felhasználó tulajdonjogával és a tényleges üzemeltetői csoporttal. Meglévő fájl jogait az install parancs nem írja át. Windows alatt megfelelő NTFS ACL kell. Ne állíts `777`-et. A backup ugyanolyan személyes adatot tartalmaz, mint az eredeti adatbázis.

## Telepítés

1. Telepítsd a projekt meglévő Composer-függőségeit, ha hiányoznak: `composer install --no-dev --optimize-autoloader` (helyi teszteléshez dev-függőségekkel). A már meglévő `APP_KEY`-t ne generáld újra.
2. A projekten belül futtasd:

   ```sh
   php artisan randi:password
   ```

   A jelszó bevitele rejtett, legalább 12 karakter kell és kétszer egyeznie kell. A parancs kizárólag a hash konfigurációs sorát írja ki; nem módosítja a `.env` fájlt. Tedd a sort a privát `.env` fájlba, egyszeres idézőjelben, hogy a hash `$` karaktereit a dotenv ne értelmezze változóként. A jelszót ne add parancssori argumentumban és ne tedd a dokumentációba.

3. Állítsd be a fenti változókat. Új szerveren a webszerver dokumentumgyökere a **`public/`** legyen.
4. Futtasd:

   ```sh
   php artisan config:clear
   php artisan randi:install
   php artisan view:cache
   ```

   Az install létrehozza a hiányzó privát adatkönyvtárat és fájlt, ellenőrzi a kapcsolatot, majd kizárólag a `database/randi-migrations` verziózott migrációit futtatja a `randi` kapcsolaton. Ismételhető, a meglévő meghívókat nem törli. A fő alkalmazás adatbázisát nem migrálja. Ne használj `migrate:fresh`-t.

5. Éles konfiguráció véglegesítése után szokásos `php artisan config:cache`, szükség esetén `php artisan route:cache`. A modulhoz nincs frontend build; a `public/assets/randi/` fájljait is telepítsd. Publikus **`public/randi/` könyvtár ne legyen**, mert az felülírná a `/randi` route-ot a webszervernél.

A 2026. október 8-i éles bekötés a Hostinger fájlkezelőjén keresztül történt. A külön adatbázis az alapértelmezett `storage/app/private/randi/randi.sqlite` útvonalon működik; a meglévő `APP_URL=https://pzoli.com` adja a személyes linkek eredetét. A fő alkalmazás adatbázis-beállításai és a meglévő `.env` titkai megmaradtak. A kezdeti bekötéskor éles adminjelszóhash még nem volt beállítva.

### 2026. október 8-i ellenőrzés és kiadás

- A `/randi` névbekérő személyes linket készít, a név a meghívó megszólításában szerepel, és a válasz az adott meghívó azonosítójához kapcsolódik. Azonos nevek nem írják felül egymást. A demó a `/randi/demo` útvonalra került.
- Helyben 57 PHPUnit-teszt, 811 assertion sikeres; Pint, JavaScript-szintaxis, Blade-fordítás és diffellenőrzés rendben. A böngészőben 320, 390 és 1440 CSS-pixeles nézeteket ellenőriztünk. A névbekérő, linkmásolás, teljes elfogadás, elutasítás és újratöltés utáni mentett állapot működött a külön QA SQLite-fájlon. Nyolc emulált érintésnél a „Nem” gomb hét különböző, az „Igen”-nel nem fedő pozícióba került.
- Élesben személyes linkkészítést és elutasításmentést ellenőriztünk; újratöltés után is a mentett válasz jelent meg. Az adatbázis közvetlen HTTP-kérése 403 választ adott. Az „Ellenőrzés” nevű próbameghívó és elutasítása megmaradt, az admin beállítása után törölhető.
- A kód fájlkezelővel került élesbe, Git-push nélkül. A helyi változások és a szerver Git-munkakönyvtára még szinkronizálandó a következő Git-alapú telepítés előtt.
- A `public_html/randi-update.zip` újrahasználható, kizárólag kódot tartalmazó csomag. A `public_html/randi-code-rollback.zip` az előző kódot állítja vissza, adatbázist nem tartalmaz. A helyi `artifacts/randi/release-20261008/randi-first-install-only.zip` üres kezdeti adatbázist is tartalmaz: meglévő éles adatbázisra nem szabad újra kicsomagolni.

### Privát hozzáférés – 2026. október 8.

- A korábbi publikus névbekérő `/randi/admin/new` alá került, adminbelépést igényel. A `/randi` az adminra irányít; a demó minden olvasó és író útvonala szintén védett. Az adminban „Gyors meghívó” gomb nyitja a névbekérőt. A személyes tokenes meghívók és válaszaik továbbra is belépés nélkül használhatók.
- Helyben 58 PHPUnit-teszt és 863 assertion sikeres, a Blade-fordítás, Pint és diffellenőrzés sikeres. Az opcionális böngészős runner védett demótesztjei adminmunkamenetet kaptak; ebben a frissítésben csak a runner JavaScript-szintaxisát ellenőriztük, a teljes runnert nem futtattuk.
- Éles, cookie nélküli HTTP-kéréssel a `/randi`, `/randi/admin`, `/randi/admin/new` és `/randi/demo` 302-es választ adott az adminbelépésre; a login 200-as. Böngészőben ellenőriztük a publikus belépési oldalt és a belépett admin „Gyors meghívó” navigációját. Az adatbázist és a `.env` fájlt nem módosítottuk.
- Csak a `routes/web.php`, az admin és a login Blade-nézet frissült a Hostinger fájlkezelőjén keresztül, Git-push nélkül. A kódot tartalmazó `randi-private-access.zip` és `randi-private-access-rollback.zip` a `public_html` gyökerében van; egyik sem tartalmaz adatbázist vagy titkot.

## Helyi indítás

A fejlesztői `.env`-ben `APP_ENV=local`, `APP_DEBUG=false`, `APP_URL=http://127.0.0.1:8000`, `RANDI_BASE_URL=http://127.0.0.1:8000`, külön helyi adatbázis és helyi adminhash legyen. Konfigurációváltozás után `php artisan config:clear`.

```sh
php artisan randi:install
php artisan serve --host=127.0.0.1 --port=8000
```

Linkkészítő: `http://127.0.0.1:8000/randi/admin/new`. Demó: `http://127.0.0.1:8000/randi/demo`. Admin: `http://127.0.0.1:8000/randi/admin`. A linkkészítő és a demó is adminbelépést igényel. A `php artisan randi:demo-invite` parancs csak `local`/`testing` környezetben létrehoz egy egynapos személyes próbalinket. Ez **valódi próbasor**, külön fejlesztői adatbázison használd és utána töröld az adminban. A `/randi/demo` soha nem hoz létre meghívót vagy végleges válaszsort.

## Az első elküldhető link

A gyors folyamat: lépj be a **`https://pzoli.com/randi/admin`** oldalon, és kattints a **„Gyors meghívó”** linkre. Írd be a meghívott keresztnevét (például Anna), és kattints a **„Személyes link készítése”** gombra. Másold és küldd el a kapott linket. A meghívó Zoli nevében készül, 30 napig él. A névbekérő a `/randi/admin/new` útvonalon működik; adminhitelesítés és CSRF-védelem mellett IP-nként legfeljebb 5 alkalom/perc használható. Belépés nélküli linkkészítés nincs.

A válaszok megtekintéséhez és egyéni feladóhoz, bevezetőhöz vagy élettartamhoz a védett admin használható:

1. Beállítás és telepítés után nyisd meg a **`https://pzoli.com/randi/admin`** oldalt, és lépj be a választott adminjelszóval.
2. Az „Új meghívó” űrlapon add meg az opcionális keresztnevet, a feladó nevét (alapértelmezetten Zoli), az opcionális bevezetőt és az élettartamot (alapértelmezetten 30 nap; 1–90 nap választható).
3. Kattints a **„Személyes link készítése”** gombra. A következő oldalon a **„Link másolása”** gombbal másold a teljes `https://pzoli.com/randi/{token}` URL-t. Ha a böngésző nem engedi a vágólaphoz való hozzáférést, jelöld ki a mezőt és másold kézzel.
4. Mentsd el/küldd el ezen az oldalon a linket: csak a token SHA-256 lenyomata kerül adatbázisba, később az eredeti URL nem olvasható vissza. Ne a `/randi` linkkészítő vagy a `/randi/demo` bemutató címét küldd el.
5. A címzett „Mehet a randiterv 💌” végső gombja vagy fix elutasítása után az admin „Válasz és részletek” sorában láthatod a döntést. Nincs automatikus e-mail; a nap/idősáv javaslat, a részleteket egyeztetnetek kell.

Az elveszett linket vond vissza, majd készíts új meghívót. A régi válasz nem szerkeszthető és a lezárt meghívó nem nyitható újra.

## Mentés és visszaállítás

Konzisztens snapshotot a mellékelt parancs készít a SQLite `VACUUM INTO` műveletével, majd `PRAGMA integrity_check`-et futtat:

```sh
php artisan randi:backup /srv/pzoli-private/backups/randi-2026-10-02.sqlite
```

A célkönyvtár előre létezzen, a célfájlnév új legyen; a parancs nem ír felül mentést és nem fogad el publikus útvonalat. A sikeres parancs után a lezárt snapshotfájl másolható további, védett tárolóra. Ha a művelet megszakadt vagy hibás, a félkész fájlt ne tekintsd érvényes mentésnek. **Aktív adatbázis nyers fájlmásolása nem mentési eljárás**, különösen journal/WAL mellett. WAL-t a modul nem kapcsol be; `foreign_keys=ON`, `busy_timeout=5000` minden kapcsolaton beállított és ellenőrzött.

Visszaállításkor karbantartási időben:

1. Állítsd le az író kéréseket (`php artisan down`), és várd meg a futó PHP-kérések végét. CLI-írók se fussanak.
2. Készíts konzisztens biztonsági snapshotot a jelenlegi adatbázisról a fenti paranccsal; tartsd meg az eredeti fájlt is.
3. A **sikeresen ellenőrzött, lezárt** backupot másold egy **új**, privát adatbázisfájlnévre, például `/srv/pzoli-private/randi/restored-2026-10-02.sqlite`. Így nem kell az aktív fájlt vagy annak journalját felülírni. Állítsd be a tulajdonost/jogokat.
4. Ellenőrizd az új fájlt a szerver Bash parancssorában (PDO SQLite, nincs külön sqlite3 CLI-követelmény):

   ```sh
   php -r '$db = new PDO("sqlite:".$argv[1]); echo $db->query("PRAGMA integrity_check")->fetchColumn(), PHP_EOL;' /srv/pzoli-private/randi/restored-2026-10-02.sqlite
   ```

   Az eredmény kizárólag `ok` legyen. Ezután a `.env` `RANDI_DB_PATH` értékét állítsd az új fájlra, `php artisan config:clear`, `php artisan randi:install`, majd `php artisan config:cache`. Ellenőrizd a privát adminban a sorokat, végül `php artisan up`.

5. A meglévő session- és cache-fájlokat ne állítsd vissza a backupból. Visszaállítás régebbi válaszállapotokat is visszahozhat; az azóta elküldött/visszavont linkeket külön tekintsd át, mielőtt újra megnyitod a szolgáltatást.

## Adatvédelem és működési korlátok

- A link 32 kriptográfiai véletlen bájtból képzett, 43 karakteres URL-biztos token. **A link birtokosa válaszolhat; ez nem bizonyítja a címzett személyazonosságát.** A token nem adminhitelesítés. Ne oszd meg nyilvánosan.
- Egy meghívóhoz UNIQUE idegen kulccsal egyetlen végleges válasz tartozik. `BEGIN IMMEDIATE` zárolásban történik az aktív állapot ellenőrzése, a validáció és a beszúrás. Párhuzamos igen/nem esetén az első sikeres mentés nyer. Ugyanazon szerveren tárolt munkamenetkulcs és normalizált tartalom ismétlése idempotens; eltérő döntés/tartalom nem írja felül a lezárt választ.
- Újranyitáskor csak az eredeti, szerveroldalon ellenőrzött munkamenet olvashatja a saját végleges részleteit. Másik munkamenet csak „Erre a meghívóra már érkezett válasz.” üzenetet lát. Visszavont/lejárt link sem névvel, sem korábbi üzenettel nem válaszol.
- A klienspiszkozat meghívónként elkülönülő `sessionStorage`, kétórás lejárattal, végleges mentés/elutasítás után törlődik. A JavaScript nélküli piszkozat Laravel-sessionben él. Nincs megnyitáskövetés vagy pointerpróbálkozás-naplózás.
- A válasz dátuma magyar helyi `YYYY-MM-DD`, a technikai időbélyegek UTC értékek; az admin magyar időt jelenít meg. Mai naptól +60 napig lehet választani. A már teljesen elmúlt mai idősávokat a szerver is tiltja. Nincs kapacitásfoglalás vagy végleges találkozóígéret.
- Rate limit IP-nként, HMAC-lenyomattal és 60 másodperces érvényességgel: belépés 5/perc, válaszküldés + szerveroldali űrlaplépések együtt 30/perc, admin írások együtt 20/perc, gyors adminlinkkészítés 5/perc. Ezek ideiglenes visszaélésvédelmi kulcsok, nem analitika. A címzett/admin adatbázisa nem tárol IP-t vagy user agentet.
- Minden írás POST + Laravel CSRF. Az oldalakon `private, no-store`, `no-referrer`, `noindex, nofollow, noarchive` és helyi erőforrásokra szűkített CSP van. A modul nincs menüben vagy sitemapben. A megosztási metaadatok statikusak, nincs bennük címzettnév vagy randiterv.
- A `/randi` hibaválaszok és alkalmazás-hibajelentések nem teszik közzé az SQL-t, tokenes URL-t, személyes mezőket vagy stack trace-t. Ezzel szemben **a tárhely/webszerver hozzáférési naplója és a fejlesztői szerver konzolja tartalmazhatja az eredeti URL-t**. A proxy/tárhely naplózását a randimodulra maszkolással vagy kizárással külön kell beállítani; a rendszer nem ígéri, hogy a titkos URL sehol sem kerülhet naplóba. A védett megosztástól függetlenül minden felhasználói szöveg HTML/JSON escape-elt.
- Az adminban a „Végleges törlés” rész megnyitása és `TORLES` beírása után a meghívó és a válasza FK CASCADE-del törlődik. Időszakosan töröld a már nem szükséges sorokat, a fejlesztői próbákat és a régi backupokat; a lejárat magában nem törlés. A backupok a korábbi személyes adatot továbbra is őrzik. Fizikai törléshez a szabad SQLite-lapok és a mentések megőrzését is kezeld (ellenőrzött karbantartáskor VACUUM, illetve backupmegőrzési szabály).

## Webszerver-ellenőrzés

Apache alatt a meglévő `public/.htaccess` és `mod_rewrite` szükséges; Nginx alatt a Laravel szokásos `try_files $uri $uri/ /index.php?$query_string` beállítása kell, a `public/` dokumentumgyökérrel. Az assetek `/assets/randi/` alatt vannak, így nem ütköznek a `/randi` route-tal.

Élesítéskor ellenőrizd: belépés nélkül a `/randi`, `/randi/admin/new`, `/randi/demo` és `/randi/admin` az adminbelépésre irányít; az adminban a „Gyors meghívó” személyes linket készít, a demó mentés nélkül működik. A személyes meghívólink adminbelépés nélkül megnyitható, a hibás link semleges állapotoldal, az assetek 200-at adnak, a **`.env`, `storage/`, `database/`, `.git/`, `.sqlite`, journal/WAL/SHM és backupfájlok HTTP-n nem tölthetők le**. A közvetlen fájlvédelem webszerverfeladat; nem elég a Laravel adminauth. A tárhely teljes projektgyökérből történő kiszolgálásánál külön tiltsd a privát könyvtárakat, vagy állítsd át a dokumentumgyökeret `public/`-ra.

## Tesztek és bizonyítékok

A 2026. október 2-i végső helyi ellenőrzés: **45 PHPUnit-teszt, 370 assertion sikeres**, ebből 24 új randimodul-teszt; **10 Chromium/Playwright böngészős forgatókönyv sikeres**. A Blade-fordítás, route-listázás, PHP/Pint ellenőrzés, JavaScript szintaxis és `git diff --check` is hibamentes. A külön modulmigrációt helyben kétszer futtattuk; a második futás nem módosított adatot. A mentésparancs ideiglenes adatbázison integritásvizsgálattal tesztelt. Éles telepítés, valódi mobilkészülék és éles tárhely-hozzáférésvédelem ellenőrzése nem történt.

```sh
php artisan test
php artisan test --filter=RandiTest
php artisan route:list --path=randi
php artisan view:cache
node --check public/assets/randi/randi.js
node --check public/script.js
```

A backendtesztek valódi ideiglenes SQLite-fájlt használnak, a fő és a helyi meghívó-adatbázist nem módosítják. Lefedik a normál mentést, egyeztetést, sajátprogramot, elutasítást, magyar dátumhatárokat, éjfélt/évváltást, idempotenciát, **két külön PHP-folyamat valódi párhuzamos írását**, lezárt/lejárt/visszavont linket, CSRF-et, authot, XSS-escapinget, cascade törlést, read-only/zárolt SQLite hibáját, no-JS folyamatot, rate limitet és backupintegritást. A CSRF-teszt kifejezetten visszakapcsolja a Laravel tesztkörnyezetben egyébként kikapcsolt middleware-ellenőrzését.

Az opcionális `tests/browser/randi.mjs` Chromium/Playwright tesztfuttató kizárólag localhostra enged író tesztet. A szervert **külön QA SQLite-fájllal és ideiglenes QA adminhash-sel** indítsd; a fő `.env` titkait ne cseréld le hozzá. A Playwright egy meglévő telepítésből is használható a `PLAYWRIGHT_MODULE` teljes `index.mjs` útvonalával; nincs alkalmazás-függőségként felvéve. Más gépen opcionális teszteszközként `npm install --no-save --package-lock=false playwright`, majd `npx playwright install chromium` használható.

PowerShell-példa, már elindított helyi QA szerverhez:

```powershell
$env:RANDI_TEST_URL = 'http://127.0.0.1:8787'
# A külön QA szerver saját tesztjelszava, ne éles jelszó.
$env:RANDI_TEST_PASSWORD = 'a-valasztott-helyi-tesztjelszo'
# Ha meglévő Playwrightot használsz:
# $env:PLAYWRIGHT_MODULE = 'D:\teszteszkoz\node_modules\playwright-core\index.mjs'
node tests/browser/randi.mjs
```

A runner tesztmeghívókat hoz létre és válaszol meg a külön QA adatbázisban; ezeket az adminból törölheted. Az `artifacts/randi/` alatt írja a képernyőképeket és a JSON-eredményt, Gitből kizárva. Ellenőrzi a 320/390/430 px nézeteket, asztali és fekvő méretet, átméretezést, egérközelítést, emulált érintést és click-through védelmet, billentyűzetet, reduced-motion módot, radio csoportot, draft-frissítést, böngésző-visszalépést, teljes mentést, idegen sessiont, no-JS HTML űrlapokat, hiba utáni újrapróbálást, adminműveleteket és a gyors/lassú főoldal-betöltőt. **Valódi iOS/Android készüléken nem futott teszt**; a touch Chromium-emulációval lett ellenőrizve.

## A főoldal célzott változásai

A megvalósítás fájljai, csoportosítva:

- Backend: `app/Http/Controllers/RandiController.php`, `RandiAdminController.php`; `app/Http/Middleware/RandiAdmin.php`; `app/Services/Randi/{InviteStore,ResponseValidator,RandiConflict,ExceptionResponder}.php`.
- Telepítés/karbantartás: `app/Console/Commands/{RandiInstall,RandiPassword,RandiBackup,RandiDemo}.php`; `database/randi-migrations/2026_10_02_000001_create_randi_tables.php`; `config/randi.php`.
- Bekötés: `routes/web.php`, `config/database.php`, `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`, `app/Http/Middleware/SecurityHeaders.php`, `.env.example`, `.gitignore`.
- Felület: `resources/views/layouts/randi.blade.php`; `resources/views/randi/{invite,status,demo-complete,login,created,admin}.blade.php`; `resources/views/randi/partials/{cat,summary}.blade.php`; `public/assets/randi/{randi.css,randi.js}`.
- Főoldal: `app/Http/Controllers/PortfolioController.php`, `resources/views/layouts/portfolio.blade.php`, `resources/views/portfolio/home.blade.php`, `public/Style.css`, `public/script.js`, `public/icons/profile-2026-*`, `scripts/resize-profile.php`.
- Tesztek/átadás: `tests/Feature/RandiTest.php`, `tests/Support/randi-submit.php`, `tests/browser/randi.mjs`, `README.md`, ez a dokumentum. A felhasználói `PZOLI_RANDI_CODEX.md` változatlan maradt.

A csatolt fotóból 320/480/720 px WebP és 720 px JPEG készült (`public/icons/profile-2026-*`). A meglévő fekete-fehér alapmegjelenés/színes hover maradt; a fotó tartalmán nem történt retusálás. A reprodukálható középső négyzetes crop és méretezés: `php scripts/resize-profile.php <eredeti.jpeg>`.

A régi szöveges, mesterséges várakozással működő loader helyén szöveg nélküli spinner van. Alapállapotban rejtett, csak ha **700 ms után is tart a betöltés**, akkor látszik; `load` eseménykor azonnal eltűnik, legfeljebb 8 másodperc után mindenképp elenged. Gyors hálózaton nincs várakoztatás. JavaScript nélkül rejtve marad, reduced-motion módban nem forog. A főoldal tartalma, kapcsolati űrlapja, SEO-ja és meglévő route-jai nem lettek áttervezve.

Technikai alapok: [SQLite VACUUM INTO és konzisztens snapshot](https://www.sqlite.org/lang_vacuum.html), [Laravel 12 CSRF](https://laravel.com/docs/12.x/csrf), [PHP random_bytes](https://www.php.net/manual/en/function.random-bytes.php).

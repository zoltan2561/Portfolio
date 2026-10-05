# PZOLI / RANDI — Codex megvalósítási munkautasítás

## 0. Feladat és végeredmény

A meglévő pzoli.com projektbe készíts egy magyar nyelvű, mobilközpontú, személyes randimeghívó minialkalmazást a `/randi` útvonal alá. A cél nem egy üzleti landing page vagy hagyományos kérdőív, hanem egy rövid, vicces, aranyos interaktív élmény.

A teljes folyamat:

**Megnyitja a személyes linket → randimeghívás → játékos igen/nem → kedves reakció az igenre → időpontválasztás → programválasztás → összegzés és beküldés → aranyos köszönet.**

A végleges válaszokat valódi SQLite-adatbázisba mentsd. Készíts egyszerű, védett kezelőfelületet, ahol Zoli létrehozza a meghívókat és látja a válaszokat. Ne csak tervet vagy statikus demót adj: valósítsd meg a működő funkciót a repóban, teszteld és dokumentáld.

## 1. Először vizsgáld meg a meglévő projektet

Nézd át a projektutasításokat, a könyvtárszerkezetet, a routolást, a PHP-/framework-verziót, a frontendfelépítést, az esetleges admin/auth megoldást és a telepítési környezetet. A tényleges repó legyen a kiindulópont; ne feltételezz Laravel-, React- vagy más környezetet bizonyíték nélkül.

Ha egyszerű PHP-oldal, maradjon egyszerű PHP + PDO SQLite + helyi CSS + vanilla JavaScript. Ha Laravel, annak meglévő controller-, validációs-, session-, CSRF- és tesztmegoldásait használd, a modulhoz külön SQLite-kapcsolattal. Más meglévő stack esetén annak bevett SQLite-integrációját alkalmazd.

Ne költöztesd át az egész oldalt új keretrendszerre. Ne változtasd meg a főoldal dizájnját, kontaktűrlapját, SEO-ját, adatbázisát vagy hitelesítését. Ne állítsd át a teljes alkalmazás alapértelmezett adatbázis-kapcsolatát SQLite-ra. A módosítások a randimodulra korlátozódjanak; a közös konfigurációban csak a szükséges, indokolt változások legyenek.

Ne vezess be Docker-, Redis-, külső adatbázis-, háttérfolyamat-, fizetős szolgáltatás- vagy külön frontendalkalmazás-követelményt. Új függőséget csak valódi indokkal használj. A megoldás működjön a jelenlegi tárhely adottságai között.

## 2. Linkek és meghívók

A kiküldhető személyes URL formája:

```text
https://pzoli.com/randi/{token}
```

A token szerveroldalon, kriptográfiailag biztonságos véletlenből készüljön: legalább 32 véletlen bájt, URL-biztos kódolással. PHP-ban erre `random_bytes(32)` használható. [1]

Ne legyen találgatható számozás vagy név az URL-ben. A token a meghívó elérésére és megválaszolására ad jogot, nem adminbelépésre. A link birtoklása nem bizonyítja, hogy valóban az eredeti címzett nyitotta meg: ezt a README-ben röviden jelezd.

Az adatbázisba a token SHA-256 lenyomata kerüljön, ne a nyers token. A teljes linket létrehozáskor egyszer mutasd meg, jól működő „Link másolása” gombbal és kézi másolási lehetőséggel. Mondd ki az adminban, hogy később az eredeti titkos link nem olvasható vissza. Elveszett linknél lehessen a régi meghívót visszavonni és új meghívót készíteni.

Egy meghívóhoz opcionális keresztnév, feladónév — alapértelmezetten „Zoli” — és rövid egyedi bevezető tartozzon. A személyes adat ne kerüljön query paraméterbe.

A token nélküli `/randi` legyen egyértelműen jelölt, anonim bemutató ugyanazzal a vizuális élménnyel. A demó ne hozzon létre meghívót és ne mentsen éles választ; ezt backendoldalon is biztosítsd. Az adminból külön „Demó megnyitása” gomb vezessen ide. A címzettnek mindig a személyes linket kell küldeni.

Lejárt, visszavont vagy érvénytelen linknél rövid, barátságos, személyes adatot nem tartalmazó állapotoldal jelenjen meg:

> Ez a meghívó most nem elérhető. Kérj Zolitól egy új linket. 💌

## 3. Vizuális irány

Különálló, meghitt miniélmény legyen: ne jelenjen meg a céges főmenü, szolgáltatáslista vagy ajánlatkérő CTA. Ugyanakkor ne bontsd meg a főoldal működését.

**Hangulat:** meleg törtfehér háttér, barackos/rózsás kiemelések, sötét és jól olvasható szöveg, finom lekerekítések, visszafogott árnyékok. Legyen igényes és fiatalos, ne harsány szívecskeözön vagy sablonos esküvői oldal. Ne használj ferde panelekből álló elrendezést.

Középen egy megközelítőleg 480–560 px maximális szélességű tartalmi kártya legyen. Mobilon legyen kényelmes oldalsó tér, nagy gomb, jó sortörés; 320 px szélességtől se legyen vízszintes görgetés. Vedd figyelembe a telefonos safe-area inseteket, a változó viewportmagasságot és a megjelenő billentyűzetet. Ne vágd le a tartalmat rögzített képernyőmagassággal.

Javasolt kis vizuális motívum: saját, helyi SVG-ből megoldott cuki macska egy borítékkal. Nyitáskor félénk, az igen után örül. Más egyszerű, eredeti illusztráció is jó. Ne tölts be külső GIF-et, követőkódot vagy nagy képcsomagot.

Finom áttűnés/enyhe elmozdulás a lépések között; egyetlen rövid konfetti vagy szívecskeanimáció az igenre. Ne legyen automatikus hang vagy folyamatos animációs terhelés. A `prefers-reduced-motion` beállítást ténylegesen vedd figyelembe: ilyenkor statikus, teljes értékű változat legyen. [2]

A dátum–program–összegzés szakaszban jelenjen meg visszafogott lépésjelző. A visszalépés őrizze meg a beírt adatokat. Lépésváltáskor a fókusz kerüljön az új címhez; a háttérbe kerülő lépések ne maradjanak fókuszálhatók.

## 4. A teljes élmény és a konkrét szövegek

Az alábbi szövegek legyenek az alapértelmezett magyar változatok. A dinamikus neveket biztonságosan illeszd be. A hangvétel legyen személyes és önironikus, ne nyomulós, sértődött vagy bűntudatkeltő.

### 4.1. A meghívás

Személyre szabott felütés:

> Szia, {keresztnév}! Van egy fontos kérdésem… 👀

Keresztnév nélkül:

> Szia! Van egy fontos kérdésem… 👀

Nagy főcím:

> Eljössz velem egy randira? 💌

Alatta:

> Ígérem, élőben kevesebb gombot kell megnyomnod.

Két gomb:

- **Igen, menjünk 🥰** — a fő CTA, stabil pozícióval.
- **Nem 🙈** — a játékos, menekülő gomb.

A vicces „Nem” mellé kerüljön egy mindig látható, fix helyű, jól olvasható **„Most inkább kihagyom”** szöveges lehetőség. Ez valódi elutasítást küldjön, dátum- és programválasztás nélkül. Ne rejtsd el és ne legyen halvány, apró kibúvó.

### 4.2. A menekülő „Nem” gomb

Ez legyen az oldal egyik emlékezetes részlete. Egérrel közelítve a gomb kitér a kurzor elől; érintőképernyőn a megérintési kísérletre odébb ugrik. A játékos változat pointeres aktiválása ne küldjön választ és ne válassza ki az igent.

A megoldás ne csak `mouseover` eseményt használjon: kezeld külön az egeret és az érintést, például Pointer Events segítségével. [3] Figyelj a `pointerdown` után érkező `click` eseményre: ne történjen véletlen beküldés vagy click-through az „Igen”-re.

Legyen a gombnak kijelölt, korlátozott játéktere. Mindig teljes egészében maradjon azon belül, ne fedje le az „Igen”-t, a szövegeket, a fix elutasító linket vagy más vezérlőt. A célpozíciókat ezek ütközésvizsgálatával válaszd ki. Ablakméret- és orientációváltáskor számold újra a határokat. Ne pozicionáld korlátlanul a teljes oldalra. Amennyiben nincs biztonságos új hely, ne mozduljon másik vezérlőre.

A mozgásnak legyen rövid cooldownja, hogy egyetlen érintés vagy kurzormozgás ne váltson ki sok ugrást. A szöveg helye legyen fenntartva, ne ugráljon miatta a teljes kártya.

Kitérési kísérletek után sorban jelenhetnek meg ezek a rövid üzenetek:

> Hopp, ez arrébb ment. 😇
>
> Ez a gomb ma kicsit félénk. 🙈
>
> Technikai probléma. Teljesen véletlen. 👀
>
> Az igen legalább megvár. 😌

Ne nőjön az „Igen” a képernyőt kitöltőre, ne legyenek sértő szövegek, és a gombvadászat ne legyen kötelező a továbblépéshez. A megfogási kísérletek számát ne naplózd a backendbe.

Billentyűzetes használatkor, csökkentett mozgásnál és JavaScript nélküli változatban legyen normál, működő elutasítás; ne kerüljön a fókusz menekülő célpontra. A fix „Most inkább kihagyom” minden változatban működjön.

### 4.3. Kedves reakció az igenre

Az igen megnyomása után rövid örömanimáció, majd:

> Na jó, most mosolygok a telefonomra. 🥹
>
> Ez határozottan jó fordulat a napomban.

Gomb:

> Akkor találjunk egy időpontot →

Ne kényszeríts több másodperces várakozásra. Az első igen még csak a folyamat kezdete: ekkor ne rögzíts végleges elfogadott választ az adatbázisban.

### 4.4. Időpontválasztás

Főcím:

> Mikor lenne jó neked? 📅

Kísérőszöveg:

> Válassz egy napot. A többit kitaláljuk.

Legyen kényelmes dátumválasztó magyar dátumokkal. A mai naptól a következő 60 napig lehessen választani. A tartomány mindig az aktuális magyar dátumból számolódjon; ne legyen beégetett év, hónap vagy konkrét hétvége. Natív dátummezőre épülő, jól megjelenített megoldás megfelelő; ne építs indokolatlanul nagy saját naptárkönyvtárat.

Opcionális gyorsgombok: legközelebbi péntek, szombat, vasárnap. Ezek tényleges dátumot válasszanak, helyes hónap-/évváltással, és ne kerüljék meg a dátumvalidációt.

Dátum után négy idősáv közül lehessen egyet választani:

- Délután — 14:00–17:00.
- Kora este — 17:00–19:00.
- Este — 19:00–22:00.
- Az időpontban rugalmas vagyok.

A mai napon a teljesen elmúlt idősávokat tiltsd le a szerveren is. Ez személyes időpontjavaslat, nem szabad kapacitást mutató foglalási rendszer: ne állítsd, hogy a nap biztosan szabad Zolinak.

Legyen egy külön **„Még egyeztessük”** lehetőség. Ekkor nem kötelező dátumot vagy idősávot megadni, de a programválasztásra tovább lehessen menni. Ne kényszeríts kitalált dátum kiválasztására.

A dátum és az idősáv magyar helyi érték. Az időzóna `Europe/Budapest`. A dátumot `YYYY-MM-DD` date-only értékként tárold, ne alakítsd feleslegesen UTC-éjfélévé. A technikai időbélyegek legyenek UTC-ben. Ne a kliens UTC-alapú `toISOString()` dátumrészét tekintsd automatikusan a mai magyar dátumnak.

### 4.5. Programválasztás

Főcím:

> És mihez lenne kedved? ✨

Alatta:

> A lényeg a társaság, de a programot rád bízom.

Hat nagy, jól megérinthető, egyválasztós programkártya:

| Azonosító | Felirat | Rövid kísérőszöveg |
| --- | --- | --- |
| `coffee_walk` | ☕ Kávé és séta | Koffein, friss levegő, és egy beszélgetés, amit nem kell időre befejezni. |
| `dinner` | 🍝 Egy finom vacsora | Jó kaja, jó társaság. A „csak egy falatot kérek” belefér. |
| `cinema` | 🎬 Mozi | Popcornból nem ígérem, hogy pontosan a felét eszem meg. |
| `mini_trip` | 🌿 Egy kis kiruccanás | Szép hely, közös élmény, nulla teljesítménytúra. |
| `bowling` | 🎳 Bowling vagy biliárd | Egy kis verseny. A szabályokat azért előtte tisztázzuk. |
| `surprise` | 🎁 Lepj meg! | Én szervezek, neked csak meg kell jelenned. |

Egy fő program választható. Kijelöléskor legyen pipajel és egyértelmű állapot, ne csak színkülönbség. A vezérlők működjenek valódi radio groupként billentyűzetről is.

A kártyák alatt **„Van jobb ötletem 💡”** alternatíva nyisson sajátprogram-mezőt. Ez külön, `custom` választás; maximum 200 karakter, és csak ennél a választásnál kötelező. Másik programra váltáskor a rejtett saját ötlet ne kerüljön véletlenül beküldésre.

Opcionális, maximum 280 karakteres mező:

> Valamit még üzennél? 💬

Nem kell telefonszámot, e-mailt, lakcímet vagy új felhasználói fiókot bekérni.

### 4.6. Összegzés és beküldés

Főcím:

> Ezt a tervet nehéz lesz nem várni. 💌

Egy kártyán jelenjen meg a választott nap/idősáv vagy a „Még egyeztetjük”, a program, a saját ötlet és az opcionális üzenet. A dátum emberi, magyar formátumú legyen. Mind az időponthoz, mind a programhoz lehessen visszalépni és javítani.

Rövid, tényszerű tájékoztatás:

> A választásaidat elmentem, hogy meg tudjuk szervezni a randit. Zoli a saját kezelőfelületén látja őket.

Végső gomb:

> Mehet a randiterv 💌

Csak ez a gomb küldje be a végleges elfogadott választ. Beküldés közben legyen loading állapot, tiltott ismételt kattintás és backendoldali védelem is a duplikáció ellen. Az összegzésből még mindig legyen elérhető a fix elutasítás.

### 4.7. Sikeres lezárás

Kizárólag visszaigazolt SQLite-mentés után jelenjen meg a sikeres képernyő:

> Hivatalos: van mit várnom. 🥰
>
> Elmentettem, amit választottál. A részleteket még megbeszéljük.
>
> Addig is gyakorlom, hogyan legyek lazának tűnően izgatott.

Alatta a végleges választások rövid összefoglalója. Ne állítsd, hogy e-mail vagy más értesítés kiment, ha nincs valóban beállítva és sikeresen elküldve. Ne jeleníts meg visszaszámlálást egy még nem véglegesített találkozóig.

Elutasítás után, annak sikeres mentésével:

> Rendben, köszi, hogy jelezted. Semmi gond. 🙂

Ne indíts új győzködési kört, és ne legyen kötelező indoklás.

Mentési hibánál:

> Most nem sikerült elmenteni. A választásaid megvannak, próbáld újra.

Maradjon elérhető az újrapróbálkozás. Sikertelen mentésnél soha ne jeleníts meg sikeres állapotot.

## 5. Állapotkezelés és újranyitás

Legyen egyértelmű frontendállapot-gép: meghívás, igen-visszajelzés, időpont, program, összegzés, beküldés, siker, elutasítás, hiba.

A kitöltési piszkozatot meghívónként elkülönített `sessionStorage`-ban vagy a meglévő rövid életű munkamenet-megoldással őrizd meg. Újratöltéskor ugyanabban a böngészős munkamenetben ne vesszen el; másik meghívóhoz viszont ne keveredjen át. Végleges mentés vagy elutasítás után töröld a piszkozatot. Ne tárold korlátlanul a böngészőben.

A kezdő igen, a lépésváltások és a link megnyitása nem végleges válasz. Ne építs megnyitáskövetést, kattintásszámlálást, eszközprofilozást vagy rejtett viselkedésnaplózást.

Egy meghívóhoz legfeljebb egy végleges válasz tartozhat. A már válaszoló munkamenet újranyitáskor láthatja a saját végleges összegzését. Másik böngésző/munkamenet ugyanazzal a már lezárt linkkel csak általános üzenetet kapjon: „Erre a meghívóra már érkezett válasz.” Ne add vissza automatikusan a korábbi válasz és megjegyzés teljes tartalmát.

A munkamenet válaszhoz való hozzáférését szerveroldalon ellenőrizd; ne legyen elég egy kliensoldali `completed=true` jelző. A korábban nyitva hagyott lap sem írhasson már visszavont vagy lezárt meghívóra.

JavaScript nélkül legyen egyszerű, ugyanazon szerveroldali validációt és mentést használó HTML-form alternatíva: igen/nem, dátum vagy egyeztetés, program, összegzés. A látványos animációk ne legyenek a válaszadás előfeltételei.

## 6. SQLite és adatmodell

### Táblák

A neveket illeszd a projekthez, de őrizd meg az alábbi feladatköröket.

**`date_invites`**

- `id`: belső elsődleges kulcs.
- `token_hash`: egyedi, indexelt lenyomat.
- `recipient_name`: opcionális keresztnév, maximum 80 karakter.
- `sender_name`: maximum 80 karakter, alapértelmezetten Zoli.
- `intro_message`: opcionális bevezető, maximum 240 karakter.
- `expires_at`: UTC lejárat; adminban alapértelmezett élettartam 30 nap.
- `revoked_at`: nullable UTC időbélyeg.
- `created_at`, `updated_at`: UTC időbélyegek.

**`date_responses`**

- `id`: belső elsődleges kulcs.
- `invite_id`: idegen kulcs, **UNIQUE**, törléskor a kapcsolódó válasz is törlődjön.
- `decision`: kizárólag `accepted` vagy `declined`.
- `date_mode`: `specific_date` vagy `discuss_later`; elutasításkor NULL.
- `preferred_date`: nullable `YYYY-MM-DD`.
- `time_window`: `afternoon`, `early_evening`, `evening` vagy `flexible`; egyeztetés/elutasítás esetén NULL.
- `activity`: a hét engedélyezett programazonosító valamelyike; elutasításkor NULL.
- `custom_activity`: nullable, legfeljebb 200 karakter.
- `note`: nullable, legfeljebb 280 karakter.
- `submission_key_hash`: az idempotens végső beküldés lenyomata, megfelelő egyediséggel.
- `submitted_at`: UTC időbélyeg.

Az adminban megjelenített státuszt a meghívó visszavonásából, lejáratából és válaszából számold; ne tarts fenn egymásnak ellentmondó, redundáns státuszmezőket. A korábbi választ adminban lejárat után is lehessen látni, a visszavonás ténye pedig legyen külön jelzés.

### Adatbázis-kezelési követelmények

PHP-projektben használd a PDO SQLite drivert; minden felhasználói érték paraméterezett lekérdezésbe kerüljön. [4] Ne fűzz össze SQL-t névből, tokenből, programból vagy megjegyzésből.

A modul adatbázisa külön fájl legyen, abszolút, konfigurálható elérési úttal, lehetőleg a webszerver dokumentumgyökerén kívül. A valódi `.sqlite`, journal, WAL, SHM, backup és exportfájl ne kerüljön Gitbe, frontend assetek közé vagy publikus letöltési helyre.

Minden új adatbázis-kapcsolaton állítsd be és ellenőrizd:

```sql
PRAGMA foreign_keys = ON;
PRAGMA busy_timeout = 5000;
```

Ezeket ne tekintsd a párhuzamos írási problémák teljes megoldásának. [5] Tartsd röviden a tranzakciót, és kezeld szabályosan a zárolási és írási hibákat. WAL-t ne kapcsolj be automatikusan: csak ellenőrzött fájlrendszer, támogatott tárhelykörnyezet és megfelelő mentési eljárás mellett használd. [6]

A végleges beküldés tranzakcióban ellenőrizze a meghívó állapotát és lejáratát, validálja az adatokat és szúrja be az egyetlen választ. A párhuzamos elfogadás/elutasítás versenyét is a tranzakció és az egyediség oldja fel: az első sikeres végleges válasz nyer, a második nem írhatja felül.

A kliens egy stabil, munkamenethez és meghívóhoz kötött beküldési kulcsot használjon az újrapróbálkozásokhoz. Ugyanazon kulccsal és tartalommal ismételt kérés a korábbi eredményt adja vissza, ne hozzon létre új sort. Ugyanazzal a kulccsal eltérő tartalom, vagy más munkamenetből érkező új válasz ne írjon felül már lezárt döntést. Hálózati timeout után is legyen biztonságos az ismétlés.

Készíts ismételten, adatvesztés nélkül futtatható, verziózott migrációt és fejlesztői demóadatot. Inicializáló/seedelő művelet ne legyen publikus GET-végpont. A tesztek külön, ideiglenes adatbázist használjanak.

## 7. Szerveroldali validáció és biztonság

Minden érdemi szabályt a backend érvényesítsen. A JavaScript-validáció csak kényelmi réteg.

Elfogadáskor kötelező a program és az érvényes dátumválasztási mód. Konkrét dátumnál a dátum és az idősáv is kötelező; egyeztetésnél mindkettő legyen NULL. Ellenőrizd a ténylegesen létező dátumot, a szerver szerinti magyar naptári tartományt és a mai, teljesen elmúlt idősávokat. Saját programnál kell a szöveg, más programnál ne ments rejtett sajátprogram-adatot. Elutasítás ne követeljen dátumot, programot vagy megjegyzést; a beküldött ilyen adatokat ne őrizze meg.

Legyen központi allowlist az engedélyezett értékekre és karakterkorlát a szövegekre. Az admin űrlapjai is kapjanak teljes validációt. Ismeretlen mezőket ne lehessen automatikusan adatbázis-oszlopokra tömegesen ráírni.

Minden állapotváltoztató művelet POST legyen, CSRF-védelemmel; a meglévő framework helyes megoldását használd. A CSRF-token és a meghívótoken külön feladatú titok, ne legyenek egymás helyettesítői. A CSRF-token ne kerüljön URL-be. [7] A GET/HEAD kérések és a közösségi linkelőnézet-botok ne hozzanak létre választ és ne módosítsák a meghívó állapotát.

Kimenetkor megfelelő HTML-/JSON-escaping kell a nevekre, egyedi bevezetőkre és üzenetekre, adminban is. Ne renderelj megjegyzést nyers HTML-ként. A JSON-válaszokban ne legyen SQL, stack trace, szerverfájlútvonal, adminadat vagy más meghívó adata.

A modul saját admin- és válaszküldő végpontjai kapjanak észszerű, dokumentált rate limitet. Ne tárolj analitikai célból nyers IP-címet vagy user agentet; a visszaélés-védelemhez használt ideiglenes azonosítókhoz legyen rövid megőrzés.

A személyes és adminoldalak használjanak `Cache-Control: private, no-store` és `Referrer-Policy: no-referrer` fejléceket. A modul ne töltsön be külső analytics-, pixel-, chat-, font- vagy GIF-erőforrást. A meglévő globális követőkódokat a `/randi` nézetein hagyd ki, a főoldalon ne változtasd meg őket.

A meghívótoken ne kerüljön alkalmazásnaplóba, hibakövetőbe vagy analitikába. A webszerver URL-naplózását ahol lehet, maszkolással korlátozd; a README-ben jelezd, hogy a tárhely hozzáférési naplója tartalmazhatja az eredeti megosztott URL-t. Ne állítsd, hogy a titkos link soha nem kerülhet naplóba.

A randimodul ne kerüljön sitemapbe vagy a céges navigációba. Legyen `noindex, nofollow, noarchive`; ez ne helyettesítse az admin valódi hitelesítését. A közösségi megosztás metaadatai legyenek semlegesek és statikusak: „Van egy kérdésem… 💌” / „Egy kis meghívó, csak neked.” Név, válasz, dátum vagy megjegyzés ne jelenjen meg OG-metaadatban.

## 8. Egyszerű, védett admin

Javasolt útvonal: `/randi/admin`. A publikus meghívótoken nem jogosít adminhozzáférésre. A fix admin-/demó-/API-útvonalakat a dinamikus tokenroute előtt kezeld; a tokenroute kapjon formátumkorlátot.

Ha van megfelelő meglévő adminhitelesítés, azt használd explicit jogosultság-ellenőrzéssel. Ha nincs, készíts minimális, egyfelhasználós, session-alapú belépést. A jelszó csak biztonságos hash formájában, környezeti vagy nem publikus konfigurációban legyen; használj bevett jelszóhash-ellenőrzést, ne saját titkosítási módszert. Ne legyen beégetett vagy alapértelmezett jelszó, publikus regisztráció vagy titkos query paraméteres adminbelépés.

Sikeres belépéskor új sessionazonosító; éles HTTPS alatt Secure, HttpOnly, SameSite cookie-k; bejelentkezési rate limit és működő kijelentkezés. Hiányzó authkonfigurációnál az admin maradjon lezárva.

Az admin tudja:

1. Meghívó létrehozását keresztnévvel, feladónévvel, opcionális bevezetővel és lejárattal; létrehozáskor a teljes link másolását.
2. A meghívók listázását, az állapotuk és a beküldött válasz időpontjának áttekintését.
3. A dátum, idősáv, program, saját ötlet és megjegyzés megtekintését.
4. Aktív meghívó visszavonását; külön megerősítés után a meghívó és válasza végleges törlését.
5. A mentés nélküli demó megnyitását.

Nem kell többfelhasználós rendszer, címzettlista-import, kampányküldés, CRM vagy statisztikai dashboard. Az admin ne tudja a címzett nevében utólag átírni a döntést; változtatáshoz új meghívó készíthető. A régi adatok törlésére legyen dokumentált admin- vagy CLI-művelet, ne gyűljenek korlátlanul.

## 9. Technikai felépítés és válaszok

Válaszd szét a nézeteket, kliensoldali interakciókat, validációt, meghívókezelést és az adatbázis-hozzáférést. Ne készíts egyetlen több ezer soros PHP-fájlt. A CSS legyen a modulra szkópolva; ne használj a főoldalt átformázó globális szelektorokat.

Lehetséges végpontfelosztás — a tényleges stackhez igazítható:

```text
GET  /randi                              mentés nélküli demó
GET  /randi/{token}                      személyes meghívó / lezárt állapot
POST /randi/{token}/response             accepted vagy declined végleges válasz
GET  /randi/admin                        védett áttekintés
GET  /randi/admin/login                  belépő, ha külön auth szükséges
POST /randi/admin/login                  belépés
POST /randi/admin/logout                 kijelentkezés
POST /randi/admin/invitations            meghívó létrehozása
POST /randi/admin/invitations/{id}/revoke visszavonás
POST /randi/admin/invitations/{id}/delete megerősített végleges törlés
```

Használj konzisztens siker-/hibaválaszokat és megfelelő HTTP-státuszokat. A backend validációs hibáit a megfelelő mezőnél, emberi magyar szöveggel jelenítsd meg. A token nélküli demó ne kerülhesse meg az éles mentés hitelesítési szabályait.

Értesítő e-mail csak opcionális extra: meglévő, ellenőrzött mailkonfigurációhoz kapcsolódhat, a sikeres adatbázis-tranzakció után. SMTP-hiba ne veszítse el a már elmentett választ, és a felület ne ígérjen sikeres értesítést bizonyíték nélkül. Az MVP működéséhez az adminban megjelenő válasz elegendő.

## 10. Ellenőrzés és elfogadási feltételek

Ne csak a boldog utat teszteld. Automatizáltan ellenőrizd, ahol a környezet ezt lehetővé teszi:

- Meghívó létrehozása, hash alapján történő feloldása; hibás, lejárt és visszavont token.
- Az érvényes elfogadott válasz teljes és helyes SQLite-mentése.
- A „Még egyeztessük”, „Lepj meg” és sajátprogram-ág helyes mentése.
- Elutasítás dátum/program nélkül; elfogadás kötelező adat nélkül nem menthető.
- Érvénytelen, múltbeli és tartományon túli dátum; magyar éjfél, hónap-/évváltás és időzónahatár.
- Mai, már teljesen elmúlt idősáv; tiltott programazonosító és túl hosszú szöveg.
- Dupla kattintás, hálózati újrapróbálkozás, két lapról egyszerre érkező beküldés: egyetlen végleges sor.
- Ugyanazon idempotenciakulccsal eltérő tartalom nem írja át a korábbi választ.
- CSRF nélküli írás elutasítása; illetéktelen adminelérés tiltása.
- XSS-próbák a névben, bevezetőben, saját programban és megjegyzésben, a publikus és adminnézetben is.
- Másik meghívó adataihoz való hozzáférés és idegen sessionből való részletes válaszolvasás tiltása.
- A GET/HEAD és a demó nem ment éles választ.
- Írásvédett adatbázis, zárolási hiba vagy sikertelen mentés nem eredményez hamis sikerképernyőt.

Böngészőben ellenőrizd a menekülő gombot egérrel és valódi/megfelelően emulált touch eseményekkel. Tesztelj 320, 390 és 430 px szélességen, asztali nézetben, átméretezés és orientációváltás után. Ellenőrizd, hogy a „Nem” soha nem takarja el az igent vagy a fix kilépést, és érintéskor nincs véletlen igenaktiválás.

Nézd meg billentyűzettel, reduced-motion módban, JavaScript nélkül, frissítés után és böngésző-visszalépéssel is. Ha van böngészős tesztkörnyezet, készíts képernyőképet a meghívásról, programválasztásról és sikerképernyőről. Ha valamilyen tesztet nem tudsz valóban futtatni, külön jelöld meg: ne nevezd teszteltnek.

A meglévő főoldal, kapcsolatűrlap, egyéb útvonalak és assetek továbbra is működjenek.

## 11. Átadás és telepítés

Készíts `docs/RANDI_SETUP.md` dokumentumot, benne a ténylegesen megvalósított állapottal:

- Követelmények, ellenőrzött PHP-/SQLite-környezet, szükséges bővítmények.
- Konfigurációs változók, privát adatbázisútvonal és minimálisan szükséges fájljogosultságok; ne javasolj `777`-et.
- Migráció és helyi indítás konkrét parancsai, demó és tesztek futtatása.
- Adminhitelesítés beállítása, alapértelmezett jelszó nélkül.
- Első valódi meghívó létrehozása, link másolása, válasz ellenőrzése.
- SQLite konzisztens mentése és visszaállítása; ne javasold aktív adatbázis egyszerű, ellenőrizetlen fájlmásolását.
- Adatok törlése, megosztott linkek bizalmi modellje és lejáratának kezelése.
- Az esetleges webserver rewrite-/hozzáférésvédelmi beállítások és az ellenőrzésük.

Ne töltsd fel automatikusan élesbe, ne futtass adatvesztő migrációt, és ne változtass meglévő titkokat. A fejlesztést és a teszteket a rendelkezésre álló fejlesztői környezetben végezd.

A munka végén röviden add át: mit építettél meg, mely fájlokat módosítottad, mely teszteket futtattad ténylegesen és milyen eredménnyel, mi igényel még környezeti beállítást, illetve hogyan lehet elkészíteni az első elküldhető linket.

**A kész állapot:** egy valódi címzettnek elküldhető, magyar, mobilon is cuki és vicces meghívó; működő játékos gomb; végigjárható dátum- és programválasztás; őszinte lezáró képernyő; SQLite-ban megbízhatóan tárolt döntés; kizárólag jogosult admin számára látható válaszok. Nem statikus mockup, és nem indokolatlanul túlépített platform.

## Technikai hivatkozások

Az egyedi szövegek és a termékdöntések ehhez a projekthez készült specifikációk. Az alábbi hivatalos dokumentációk a megjelölt implementációs alapokat támasztják alá; a tényleges környezet verziójához igazodj.

[1] PHP — `random_bytes`: `https://www.php.net/manual/en/function.random-bytes.php`

[2] MDN — `prefers-reduced-motion`: `https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/@media/prefers-reduced-motion`

[3] MDN — Pointer Events: `https://developer.mozilla.org/en-US/docs/Web/API/Pointer_events`

[4] PHP — PDO SQLite és paraméterezett lekérdezések: `https://www.php.net/manual/en/ref.pdo-sqlite.php` ; `https://www.php.net/manual/en/pdo.prepare.php`

[5] SQLite — PRAGMA: `https://www.sqlite.org/pragma.html`

[6] SQLite — WAL működés és korlátok: `https://www.sqlite.org/wal.html`

[7] OWASP — CSRF Prevention Cheat Sheet: `https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html`

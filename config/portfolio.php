<?php

return [
    'base_url' => env('PORTFOLIO_BASE_URL', 'https://pzoli.com'),
    'statistics_password' => env('STATISTICS_PASSWORD'),
    'site_name' => 'Papp Zoltán Portfólió',
    'person' => [
        'name' => 'Papp Zoltán',
        'job_title' => 'Web developer és digitális megoldások szakértője',
        'image' => 'icons/profile-bw.jpg',
        'contact' => [
            'email' => 'melo@pzoli.com',
            'linkedin' => 'https://linkedin.com/in/papp-zoltán-41a7a4172/',
            'github' => 'https://github.com/zoltan2561',
            'instagram' => 'https://www.instagram.com/zoltan.ppp/',
            'facebook' => 'https://facebook.com/ztech20',
            'facebook_business' => 'https://www.facebook.com/pzinformatika/',
        ],
        'same_as' => [
            'https://linkedin.com/in/papp-zoltán-41a7a4172/',
            'https://github.com/zoltan2561',
            'https://www.instagram.com/zoltan.ppp/',
            'https://facebook.com/ztech20',
            'https://www.facebook.com/pzinformatika/',
        ],
    ],
    'service_types' => [
        'PHP és Laravel fejlesztés',
        'SQL adatbázis-tervezés',
        'JavaScript alapú webes fejlesztés',
        'Linux és DevOps támogatás',
    ],
    'locales' => [
        'hu' => [
            'meta' => [
                'home' => [
                    'title' => 'Papp Zoltán – Weboldal készítés, automatizálás és IT megoldások vállalkozásoknak',
                    'description' => 'Modern weboldalak, webshopok, foglalási rendszerek és automatizálás kisvállalkozásoknak. Gyors, átlátható és üzletileg használható digitális megoldások.',
                ],
                'skills' => [
                    'title' => 'Papp Zoltán – Technológiák és szakmai háttér',
                    'description' => 'Részletes áttekintés azokról a technológiákról és területekről, amelyekkel webes, automatizálási és IT projekteken dolgozom.',
                ],
            ],
            'nav' => [
                'about' => 'Rólam',
                'services' => 'Szolgáltatások',
                'projects' => 'Projektek',
                'skills' => 'Technológiák',
                'faq' => 'GYIK',
                'contact' => 'Kapcsolat',
            ],
            'home' => [
                'profile_alt' => 'Papp Zoltán profilkép',
                'hero_title' => 'Webfejlesztés, ami üzletileg is működik',
                'hero_text' => 'Gyors, átlátható weboldalakat és rendszereket készítek vállalkozásoknak.',
                'hero_note' => 'AI + automatizációval és stabil technikai háttérrel segítek egyszerűbbé tenni a működést.',
                'proof_chip' => 'WEB + AI + AUTOMATIZÁLÁS',
                'proof_title' => 'A három fő terület, amivel dolgozom',
                'proof_text' => 'A weboldal nálam sokszor csak a kezdőpont: az igazi érték az, amikor a felület, az automatizálás és a technikai háttér együtt működik.',
                'proof_footer' => 'Így nem csak egy szép oldalt kapsz, hanem egy jobban összerakott digitális rendszert.',
                'highlights' => [
                    'Webfejlesztés',
                    'AI + automatizáció',
                    'Gépek és technikai háttér',
                ],
                'proof_cards' => [
                    [
                        'title' => 'Webfejlesztés',
                        'text' => 'Weboldalak, landingek, webshopok, foglalási rendszerek és egyedi webes funkciók üzleti szemlélettel.',
                    ],
                    [
                        'title' => 'AI + automatizáció',
                        'text' => 'Ismétlődő feladatok, űrlapok, válaszok, értesítések vagy admin folyamatok egyszerűsítése AI-val és automatizálással.',
                    ],
                    [
                        'title' => 'Gépek és technikai háttér',
                        'text' => 'Ha a rendszer mögé stabil eszközök, jobb működés vagy technikai rendrakás kell, abban is tudok segíteni.',
                    ],
                ],
                'actions' => [
                    'primary' => '📩 Beszéljünk a projektedről',
                    'secondary' => '💻 Szakmai háttér',
                    'tertiary' => '🚀 Nézd meg a munkáim',
                ],
                'services' => [
                    'title' => 'Szolgáltatások',
                    'lead' => 'A fő fókuszom a webfejlesztésen van: olyan oldalakban és rendszerekben gondolkodom, amik nemcsak jól néznek ki, hanem a működésedet is tényleg támogatják. Ha kell, a mögöttes technikai oldalt is összerakom hozzá.',
                    'items' => [
                        [
                            'title' => '💻 Webfejlesztés és automatizálás',
                            'text' => 'Ha kell egy modern weboldal, online foglalás, webshop vagy valami, ami levesz adminisztrációt a válladról, ebben tudok segíteni. A cél mindig az, hogy egyszerűbb, gyorsabb és profibb legyen a működésed.',
                        ],
                        [
                            'title' => '🛠️ Eszközös támogatás',
                            'text' => 'Ha egy gépet bővíteni, rendbe tenni vagy stabilabbá tenni kell, abban is tudok segíteni. Ez jó kiegészítés akkor is, ha a webes projekt mellé a teljes technikai környezetet is össze kell rakni.',
                        ],
                        [
                            'title' => '🌐 Hálózat és technikai háttér',
                            'text' => 'Ha egy vállalkozásnál a webes rendszer mellé stabil hálózat, jobb Wi‑Fi vagy rendezettebb technikai háttér kell, ebben is tudok gyakorlati segítséget adni.',
                        ],
                        [
                            'title' => '📱 Eszközök és napi IT támogatás',
                            'text' => 'Ha a napi működésedhez használt eszközök, szoftverek vagy beállítások körül van elakadás, segítek gyorsan használható állapotba hozni őket.',
                        ],
                        [
                            'title' => '💽 Szoftver beállítás',
                            'text' => 'Programok telepítése, beállítása és rendberakása úgy, hogy ne neked kelljen vele órákig szenvedni. Olyan gépet kapsz vissza, ami tényleg használatra kész.',
                        ],
                        [
                            'title' => '💾 Biztonsági mentés és adatvédelem',
                            'text' => 'Mentés, helyreállítás, jelszókezelés és alap biztonsági higiénia. Az adat akkor érték, ha vissza is lehet hozni, amikor tényleg szükség van rá.',
                        ],
                        [
                            'title' => '🛒 Eszközvásárlási tanácsadás',
                            'text' => 'Segítek jól vásárolni, nem csak sokat költeni. Igény, keret és használat alapján ajánlok eszközt, és a beüzemelésben is ott vagyok.',
                        ],
                        [
                            'title' => '🔐 Technikai tanácsadás',
                            'text' => 'Ha azt kell eldönteni, milyen rendszerre, fejlesztésre vagy technikai háttérre van valóban szükséged, segítek tisztán látni és jó irányba indulni.',
                        ],
                    ],
                ],
                'projects' => [
                    'title' => 'Projektek',
                    'lead' => 'Néhány példa arra, hogyan lesz a technikából valódi üzleti eszköz.',
                    'hint' => 'Mobilon oldalra húzva vagy a nyilakkal tudsz lapozni a projektek között.',
                    'controls_label' => 'Projekt lapozó',
                    'prev_label' => 'Előző projekt',
                    'next_label' => 'Következő projekt',
                    'items' => [
                        [
                            'tag' => 'Weboldal + rendelés',
                            'title' => '🌯 GyrosCity',
                            'text' => 'Éttermi weboldal online rendelési és fizetési folyamattal, gyorsabb vásárlói útra hangolva.',
                            'url' => 'https://gyroscity.eu',
                            'link' => 'Weboldal megtekintése',
                        ],
                        [
                            'tag' => 'Foglalás + brand',
                            'title' => '💈 ZCutzBarber',
                            'text' => 'Barber oldal online időpontfoglalással, erős vizuális világgal és egyszerűbb ügyfélúttal.',
                            'url' => 'https://zcutzbarber.com',
                            'link' => 'Weboldal megtekintése',
                        ],
                        [
                            'tag' => 'Sportoldal + MLSZ automatizálás',
                            'title' => '⚽ TiszaszalkaSE.com',
                            'text' => 'Sportegyesületi weboldal automatikus MLSZ eredményfrissítéssel: az oldal lehúzza és megjeleníti a meccseredményeket, így a friss tartalom nem kézi adminon múlik.',
                            'url' => 'https://tiszaszalkase.com',
                            'link' => 'Weboldal megtekintése',
                        ],
                        [
                            'tag' => 'AI + Laravel híroldal',
                            'title' => '📰 NapiInfo.com',
                            'text' => 'Teljesen automatizált híroldal AI- és Laravel-alapokon. A monetizációs és automatizálási tudásom koncentrált projektje: egy okos rendszer, amely önállóan dolgozik, tanul és folyamatosan építi a tartalmat.',
                            'url' => 'https://napiinfo.com',
                            'link' => 'Weboldal megtekintése',
                        ],
                        [
                            'tag' => 'Hardver + stabilitás',
                            'title' => '🧰 Hardveres megoldások',
                            'text' => 'Egyedi gépépítés, bővítés és diagnosztika úgy, hogy a végeredmény tartósan megbízható legyen.',
                            'url' => null,
                            'link' => null,
                        ],
                        [
                            'tag' => 'Napi IT háttér',
                            'title' => '🧠 Informatikai támogatás',
                            'text' => 'Mindennapi hibakezelés, karbantartás és technikai segítség, hogy ne akadjon meg a működésed.',
                            'url' => null,
                            'link' => null,
                        ],
                    ],
                ],
                'workflow' => [
                    'title' => 'Hogyan dolgozom?',
                    'lead' => 'Egyszerű, átlátható folyamatban haladunk, hogy gyorsan kiderüljön, mire van szükséged, mennyibe kerülhet, és hogyan lesz belőle működő megoldás.',
                    'steps' => [
                        [
                            'title' => 'Rövid egyeztetés',
                            'text' => 'Pár perc alatt átbeszéljük, mire van szükséged, milyen problémát kell megoldani, és mi lenne a cél.',
                        ],
                        [
                            'title' => 'Irány és becslés',
                            'text' => 'Adok egy érthető javaslatot, várható időtartamot és körülbelüli árkategóriát.',
                        ],
                        [
                            'title' => 'Fejlesztés',
                            'text' => 'Elkészítem a weboldalt, rendszert vagy automatizációt úgy, hogy közben a gyakorlati használhatóság legyen a fókuszban.',
                        ],
                        [
                            'title' => 'Tesztelés és finomítás',
                            'text' => 'Átnézzük, kipróbáljuk, javítjuk, amit kell, hogy átadáskor stabil és használható legyen.',
                        ],
                        [
                            'title' => 'Átadás és támogatás',
                            'text' => 'Segítek az indulásban, beállításokban, és igény esetén későbbi karbantartásban is.',
                        ],
                    ],
                ],
                'audience' => [
                    'title' => 'Kinek tudok segíteni?',
                    'lead' => 'Leginkább olyan vállalkozásoknak, ahol fontos a gyorsabb ügyfélút, az egyszerűbb adminisztráció és a megbízható online jelenlét.',
                    'cards' => [
                        [
                            'title' => 'Éttermek és büfék',
                            'text' => 'Online rendelés, menü, kapcsolatfelvétel, kiszállítási információk.',
                        ],
                        [
                            'title' => 'Fodrászok, barber shopok, szépségipari szolgáltatók',
                            'text' => 'Bemutatkozó oldal, online időpontfoglalás, szolgáltatáslista.',
                        ],
                        [
                            'title' => 'Helyi kisvállalkozások',
                            'text' => 'Modern weboldal, ajánlatkérés, Google-barát alapok.',
                        ],
                        [
                            'title' => 'Sportegyesületek és közösségek',
                            'text' => 'Hírek, események, eredmények, automatizált frissítések.',
                        ],
                        [
                            'title' => 'Webshopot vagy rendelési rendszert tervezők',
                            'text' => 'Termékek, kosár, rendelési folyamat, admin felület.',
                        ],
                        [
                            'title' => 'Adminisztrációval küzdő cégek',
                            'text' => 'Riportok, értesítések, adatkezelés és ismétlődő munkák automatizálása.',
                        ],
                    ],
                ],
                'faq' => [
                    'title' => 'Gyakran Ismételt Kérdések',
                    'lead' => 'Rövid válaszok a weboldalakkal, automatizálással és technikai beállításokkal kapcsolatos gyakori kérdésekre.',
                    'items' => [
                        [
                            'question' => 'Mennyi idő alatt készül el egy weboldal?',
                            'answer' => 'Ez nagyban függ a weboldal típusától és a szükséges funkcióktól. Egy egyszerű bemutatkozó vagy kapcsolatfelvételi oldal akár 2–3 nap alatt is elkészülhet. Egy összetettebb, több funkcióval rendelkező oldal általában 5–7 nap, míg egy nagyobb webshop vagy egyedi rendszer fejlesztése jellemzően 2–3 hét vagy több is lehet.',
                        ],
                        [
                            'question' => 'Mennyibe kerül egy weboldal?',
                            'answer' => 'Ez mindig az igényektől függ. Egy rövid, pár perces egyeztetés után általában már tudok mondani egy körülbelüli árkategóriát, így gyorsan kiderül, milyen nagyságrenddel érdemes számolni.',
                        ],
                        [
                            'question' => 'Mit lehet automatizálni egy vállalkozásban?',
                            'answer' => 'Szinte bármilyen gépies, ismétlődő munkafolyamat automatizálható. Például termékek bevételezése, időpontfoglalások naplózása, ügyfelek felkeresése, riportok készítése, Facebook-posztok előkészítése vagy akár belső adminisztrációs folyamatok egyszerűsítése. Ha elmeséled, hogyan működik a vállalkozásod, nagy eséllyel találok olyan pontot, ahol időt vagy pénzt tudunk spórolni.',
                        ],
                        [
                            'question' => 'Számlaképes vagy?',
                            'answer' => 'Igen, természetesen számlaképesen dolgozom.',
                        ],
                        [
                            'question' => 'Csak weboldalakat készítesz?',
                            'answer' => 'Nem. Weboldalak mellett kisebb egyedi programokat, admin felületeket, automatizációkat, riportokat és belső vállalkozási eszközöket is tudok készíteni. A cél mindig az, hogy a megoldás valóban segítsen a napi működésben.',
                        ],
                        [
                            'question' => 'Tudsz meglévő weboldalt javítani vagy továbbfejleszteni?',
                            'answer' => 'Igen. Meglévő weboldalak hibajavításában, gyorsításában, kisebb átalakításában, új funkciók hozzáadásában vagy modernizálásában is tudok segíteni.',
                        ],
                        [
                            'question' => 'Készítesz mobilbarát weboldalakat?',
                            'answer' => 'Igen. Alap elvárás, hogy az elkészült oldal mobilon, tableten és asztali gépen is jól használható legyen.',
                        ],
                        [
                            'question' => 'Segítesz domain, tárhely vagy email beállításban is?',
                            'answer' => 'Igen. Tudok segíteni domain, tárhely, céges email, alap SEO, kapcsolatfelvételi űrlap és egyéb technikai beállításokban is.',
                        ],
                        [
                            'question' => 'Jár céges email cím a weboldal mellé?',
                            'answer' => 'Igen. Minden elkészült weboldal mellé ingyenesen segítek beállítani egy @cégnév.com végződésű céges email címet, hogy az online jelenléted egységesebb és bizalomkeltőbb legyen.',
                        ],
                    ],
                ],
                'contact' => [
                    'title' => 'Kapcsolat',
                    'lead' => 'Ha van egy ötleted, elakadásod vagy kinőtt rendszered, írj nyugodtan. Átnézem, megmondom mi lehet a jó irány, és közösen kitaláljuk a következő lépést.',
                    'email_label' => 'E-mail',
                    'linkedin_label' => 'LinkedIn',
                    'linkedin_link' => 'LinkedIn profil megnyitása',
                    'github_label' => 'GitHub',
                    'github_link' => 'GitHub profil megnyitása',
                    'instagram_label' => 'Instagram',
                    'facebook_label' => 'Facebook',
                    'success' => '✅ Köszönöm! Az üzeneted megérkezett.',
                    'error' => '❌ Hiba történt az üzenetküldés során. Próbáld újra!',
                    'form_title' => '📨 Írd meg, miben segíthetek',
                    'name' => 'Név',
                    'message' => 'Üzenet...',
                    'submit' => '📩 Küldés',
                ],
                'typewriter_sets' => [
                    ["> Rendszer inicializálása...", "> Üzleti megoldások betöltése...", "> Technológiák aktiválva...", "> Állapot: Készen a közös munkára ✅", " Helló, ez itt az én digitális műhelyem 👋"],
                    ["> 📡 Kapcsolat indítása...", "> Portfólió modul betöltve", "> Rendszerek online ✅", "> Interfész megnyitása...", " Nézz körül, itt valódi megoldások készülnek 🙌"],
                    ["> 🔎 Diagnosztika...", "> Nincsenek kritikus hibák", "> Fejlesztői környezet aktív", "> Betöltés 100% ✔️", " Üdv, indulhatunk 🌐"],
                    ["> 🗂️ Adatok előkészítése...", "> Profil betöltve", "> Projektmód aktiválva ✅", " Jó helyen jársz, ha profi rendszert akarsz 🚀"],
                ],
                'preloader_sets' => [
                    ["> 👋 Üdvözöllek!", "  Jó, hogy itt vagy 😊", "➡️ Digitális műhely indítása... 🟢"],
                    ["> 🤖 Helló!", "  Rendszerek előkészítése folyamatban 🙌", "➡️ Csatlakozás..."],
                    ["> 🌟 Portfólió indítása", "  Megoldások betöltése ✨", "➡️ Kezdünk..."],
                    ["> 🎉 Minden készen áll", "  Mehet a következő projekt 😎", "➡️ Belépés... 🚀"],
                ],
            ],
            'skills' => [
                'hero_title' => 'Technológiák és szakmai háttér',
                'hero_text' => 'Itt találod részletesebben, milyen eszközökkel és rendszerekkel dolgozom. Ha neked inkább az a fontos, hogy miben tudok segíteni, a főoldalon rövidebben és ügyfélbarátabban is végigvezetlek rajta.',
                'primary_cta' => '📩 Beszéljünk a projektedről',
                'secondary_cta' => '🚀 Vissza a projektekhez',
                'back_home' => '⬅ Vissza a főoldalra',
                'footer_title' => 'Ha van egy konkrét elképzelésed',
                'footer_text' => 'Nem kell előre tudnod, milyen stack kell hozzá. Elég, ha elmondod a célt, én segítek jó irányba bontani a technikai oldalt.',
                'typewriter_sets' => [
                    ["> Stack betöltése...", "> Webes rendszerek készen", "> Automatizálás aktív", "> IT háttér online", " Nézd meg részletesen, mivel dolgozom 👇"],
                    ["> Technológiák inicializálása...", "> Projektekhez használt eszközök listázva", "> Rendszer stabil ✅", " Itt a részletes szakmai háttér"],
                ],
            ],
        ],
        'en' => [
            'meta' => [
                'home' => [
                    'title' => 'Papp Zoltán – Website Development, Automation and IT Solutions for Businesses',
                    'description' => 'Modern websites, webshops, booking systems and business automation. Clear, fast and practical digital solutions for small businesses.',
                ],
                'skills' => [
                    'title' => 'Papp Zoltán – Technologies and Technical Background',
                    'description' => 'A detailed overview of the technologies and areas I use across web, automation, and IT projects.',
                ],
            ],
            'nav' => [
                'about' => 'About',
                'services' => 'Services',
                'projects' => 'Projects',
                'skills' => 'Tech Stack',
                'faq' => 'FAQ',
                'contact' => 'Contact',
            ],
            'home' => [
                'profile_alt' => 'Profile photo of Zoltán Papp',
                'hero_title' => 'Web development that works for business',
                'hero_text' => 'I build fast, clear websites and web systems for businesses.',
                'hero_note' => 'AI + automation and a reliable technical setup help make the whole operation smoother.',
                'proof_chip' => 'WEB + AI + AUTOMATION',
                'proof_title' => 'The three main areas I work in',
                'proof_text' => 'For me, the website is often just the starting point. The real value comes when the interface, automation, and technical setup work together.',
                'proof_footer' => 'That way you get more than a nice-looking site. You get a better-built digital system.',
                'highlights' => [
                    'Web development',
                    'AI + automation',
                    'Devices & technical setup',
                ],
                'proof_cards' => [
                    [
                        'title' => 'Web development',
                        'text' => 'Websites, landing pages, webshops, booking systems, and custom web features built with business goals in mind.',
                    ],
                    [
                        'title' => 'AI + automation',
                        'text' => 'Repeated tasks, forms, replies, notifications, or admin flows can be simplified with AI and automation.',
                    ],
                    [
                        'title' => 'Devices & technical setup',
                        'text' => 'If the system also needs reliable devices, smoother operation, or technical cleanup behind the scenes, I can help with that too.',
                    ],
                ],
                'actions' => [
                    'primary' => '📩 Let\'s talk about your project',
                    'secondary' => '💻 Technical background',
                    'tertiary' => '🚀 See my work',
                ],
                'services' => [
                    'title' => 'Services',
                    'lead' => 'My main focus is web development: websites and systems that not only look good, but genuinely support how your business works. When needed, I can also take care of the technical side behind them.',
                    'items' => [
                        [
                            'title' => '💻 Web Development & Automation',
                            'text' => 'If you need a modern website, online booking, a webshop, or something that removes repetitive admin work from your day, I can help. The goal is always to make your business simpler, faster, and more professional to run.',
                        ],
                        [
                            'title' => '🛠️ Device Support',
                            'text' => 'If a machine needs upgrading, troubleshooting, or long-term stabilization, I can help there too. This is especially useful when a web project also needs the surrounding technical environment to be sorted out.',
                        ],
                        [
                            'title' => '🌐 Network & Technical Infrastructure',
                            'text' => 'If a business needs stable networking, better Wi‑Fi, or a cleaner technical setup behind its web systems, I can help with that in a practical way.',
                        ],
                        [
                            'title' => '📱 Devices & Day-to-Day IT Support',
                            'text' => 'If the devices, software, or settings behind your daily work are causing friction, I can help get them back into a fast and usable state.',
                        ],
                        [
                            'title' => '💽 Software Setup',
                            'text' => 'Software installation and setup done properly, so you do not have to waste hours figuring everything out. You get a machine back that is actually ready to use.',
                        ],
                        [
                            'title' => '💾 Backup & Data Security',
                            'text' => 'Backups, recovery plans, password management, and practical security hygiene. Data only has real value if it can be restored when it matters.',
                        ],
                        [
                            'title' => '🛒 Device Purchase Consulting',
                            'text' => 'I help you buy well, not just spend more. Recommendations are based on needs, budget, and actual use, with setup support included if needed.',
                        ],
                        [
                            'title' => '🔐 Technical Consulting',
                            'text' => 'If you need to decide what kind of system, development work, or technical setup actually makes sense, I can help you choose a clear and practical direction.',
                        ],
                    ],
                ],
                'projects' => [
                    'title' => 'Projects',
                    'lead' => 'A few examples of how technology becomes a real business tool.',
                    'hint' => 'On mobile, swipe sideways or use the arrows to browse the projects.',
                    'controls_label' => 'Project slider controls',
                    'prev_label' => 'Previous project',
                    'next_label' => 'Next project',
                    'items' => [
                        [
                            'tag' => 'Website + ordering',
                            'title' => '🌯 GyrosCity',
                            'text' => 'Restaurant website with online ordering and payment flow built for faster customer action.',
                            'url' => 'https://gyroscity.eu',
                            'link' => 'View Website',
                        ],
                        [
                            'tag' => 'Booking + branding',
                            'title' => '💈 ZCutzBarber',
                            'text' => 'Barbershop site with online booking, strong visuals, and a smoother client journey.',
                            'url' => 'https://zcutzbarber.com',
                            'link' => 'View Website',
                        ],
                        [
                            'tag' => 'Sports site + MLSZ automation',
                            'title' => '⚽ TiszaszalkaSE.com',
                            'text' => 'Sports club website with automatic MLSZ result updates: it pulls and displays match results from the MLSZ site, keeping the page current with less manual admin.',
                            'url' => 'https://tiszaszalkase.com',
                            'link' => 'View Website',
                        ],
                        [
                            'tag' => 'AI + Laravel news',
                            'title' => '📰 NapiInfo.com',
                            'text' => 'A fully automated AI-powered news site built on Laravel. It concentrates my current monetization and automation knowledge into a smart system that works, learns, and keeps building content on its own.',
                            'url' => 'https://napiinfo.com',
                            'link' => 'View Website',
                        ],
                        [
                            'tag' => 'Hardware + stability',
                            'title' => '🧰 Hardware Solutions',
                            'text' => 'Custom builds, upgrades, and diagnostics focused on long-term reliability.',
                            'url' => null,
                            'link' => null,
                        ],
                        [
                            'tag' => 'Day-to-day IT',
                            'title' => '🧠 IT Support',
                            'text' => 'Ongoing troubleshooting, maintenance, and practical support to keep work moving.',
                            'url' => null,
                            'link' => null,
                        ],
                    ],
                ],
                'workflow' => [
                    'title' => 'How I work',
                    'lead' => 'We move through a simple and transparent process, so it becomes clear what you need, what it may cost, and how it turns into a working solution.',
                    'steps' => [
                        [
                            'title' => 'Short discussion',
                            'text' => 'We quickly go through what you need, what problem should be solved, and what the goal is.',
                        ],
                        [
                            'title' => 'Direction and estimate',
                            'text' => 'I give you a clear suggestion, expected timeline, and an estimated price range.',
                        ],
                        [
                            'title' => 'Development',
                            'text' => 'I build the website, system, or automation with practical usability in focus.',
                        ],
                        [
                            'title' => 'Testing and refinement',
                            'text' => 'We review, test, and adjust what is needed before launch.',
                        ],
                        [
                            'title' => 'Delivery and support',
                            'text' => 'I help with launch, setup, and optional later maintenance if needed.',
                        ],
                    ],
                ],
                'audience' => [
                    'title' => 'Who I can help',
                    'lead' => 'I mainly help businesses where faster customer flow, simpler administration, and a reliable online presence matter.',
                    'cards' => [
                        [
                            'title' => 'Restaurants and food businesses',
                            'text' => 'Online ordering, menu pages, contact options, delivery information.',
                        ],
                        [
                            'title' => 'Barbers, hairdressers, and beauty services',
                            'text' => 'Presentation website, online booking, service list.',
                        ],
                        [
                            'title' => 'Local small businesses',
                            'text' => 'Modern website, quote request, Google-friendly basics.',
                        ],
                        [
                            'title' => 'Sports clubs and communities',
                            'text' => 'News, events, results, and automated updates.',
                        ],
                        [
                            'title' => 'Webshops and ordering systems',
                            'text' => 'Products, cart, order flow, and admin panel.',
                        ],
                        [
                            'title' => 'Businesses with too much manual admin',
                            'text' => 'Reports, notifications, data handling, and repetitive task automation.',
                        ],
                    ],
                ],
                'faq' => [
                    'title' => 'Frequently Asked Questions',
                    'lead' => 'Short answers to common questions about websites, automation, and technical setup.',
                    'items' => [
                        [
                            'question' => 'How long does it take to build a website?',
                            'answer' => 'It depends heavily on the type of website and the required features. A simple landing page or contact website can often be completed within 2–3 days. A more advanced website with custom features usually takes around 5–7 days, while a larger webshop or custom system may take 2–3 weeks or more.',
                        ],
                        [
                            'question' => 'How much does a website cost?',
                            'answer' => 'The price always depends on the requirements. After a short discussion, I can usually give you an estimated price range, so you quickly know what budget level to expect.',
                        ],
                        [
                            'question' => 'What can be automated in a business?',
                            'answer' => 'Almost any repetitive, manual or time-consuming process can be automated. For example: product intake, appointment logging, customer follow-ups, report generation, Facebook post preparation, or internal administrative workflows. Tell me how your business works, and I may find a way to save you time or money.',
                        ],
                        [
                            'question' => 'Can you issue invoices?',
                            'answer' => 'Yes, of course. I can work with proper invoicing.',
                        ],
                        [
                            'question' => 'Do you only build websites?',
                            'answer' => 'No. Besides websites, I can also build smaller custom tools, admin panels, automations, reports and internal business systems. The goal is always to create something that actually helps daily operations.',
                        ],
                        [
                            'question' => 'Can you fix or improve an existing website?',
                            'answer' => 'Yes. I can help with bug fixes, performance improvements, smaller redesigns, new features or modernization of an existing website.',
                        ],
                        [
                            'question' => 'Do you build mobile-friendly websites?',
                            'answer' => 'Yes. Every website should work properly on mobile, tablet and desktop devices.',
                        ],
                        [
                            'question' => 'Can you help with domain, hosting or email setup?',
                            'answer' => 'Yes. I can help with domain setup, hosting, business email, basic SEO, contact forms and other technical configuration tasks.',
                        ],
                        [
                            'question' => 'Do I get a business email address with the website?',
                            'answer' => 'Yes. Every completed website includes free help setting up a business email address ending in @companyname.com, so your online presence feels more professional and trustworthy.',
                        ],
                    ],
                ],
                'contact' => [
                    'title' => 'Contact',
                    'lead' => 'If you have an idea, a blocker, or a system you have outgrown, feel free to reach out. I will review it, suggest a good direction, and we can figure out the next step together.',
                    'email_label' => 'E-mail',
                    'linkedin_label' => 'LinkedIn',
                    'linkedin_link' => 'Open LinkedIn profile',
                    'github_label' => 'GitHub',
                    'github_link' => 'Open GitHub profile',
                    'instagram_label' => 'Instagram',
                    'facebook_label' => 'Facebook',
                    'success' => '✅ Thank you! Your message has been received.',
                    'error' => '❌ Error sending message. Please try again!',
                    'form_title' => '📨 Tell me what you need',
                    'name' => 'Name',
                    'message' => 'Message...',
                    'submit' => '📩 Send',
                ],
                'typewriter_sets' => [
                    ["> Initializing system...", "> Loading business-ready solutions...", "> Tech stack activated...", "> Status: Ready to build ✅", " Welcome to my digital workshop 👋"],
                    ["> 📡 Starting connection...", "> Portfolio module online", "> Systems ready ✅", "> Interface unlocked...", " Take a look around, this is where ideas become products 🙌"],
                    ["> 🔎 Running diagnostics...", "> No critical errors found", "> Dev environment active", "> Load 100% ✔️", " Ready when you are 🌐"],
                    ["> 🗂️ Preparing data...", "> Profile loaded", "> Project mode enabled ✅", " You are in the right place for clean and powerful solutions 🚀"],
                ],
                'preloader_sets' => [
                    ["> 👋 Welcome!", "  Glad you're here 😊", "➡️ Starting the digital workshop... 🟢"],
                    ["> 🤖 Hello!", "  Preparing systems and tools 🙌", "➡️ Connecting..."],
                    ["> 🌟 Portfolio booting", "  Loading solutions ✨", "➡️ Starting up..."],
                    ["> 🎉 Everything is ready", "  Time to build something great 😎", "➡️ Entering... 🚀"],
                ],
            ],
            'skills' => [
                'hero_title' => 'Technologies and Technical Background',
                'hero_text' => 'This page gives a deeper look into the tools and systems I work with. If you only need the client-friendly overview, the homepage walks through it in a shorter way.',
                'primary_cta' => '📩 Let\'s talk about your project',
                'secondary_cta' => '🚀 Back to projects',
                'back_home' => '⬅ Back to home',
                'footer_title' => 'If you already have an idea',
                'footer_text' => 'You do not need to know the right stack in advance. It is enough to describe the goal, and I can help turn that into a practical technical direction.',
                'typewriter_sets' => [
                    ["> Loading stack...", "> Web systems ready", "> Automation active", "> IT background online", " Take a closer look at the tools I use 👇"],
                    ["> Initializing technologies...", "> Project toolkit listed", "> System stable ✅", " Here is the detailed technical background"],
                ],
            ],
        ],
    ],
];

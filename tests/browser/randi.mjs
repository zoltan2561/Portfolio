import assert from "node:assert/strict";
import { mkdir, writeFile } from "node:fs/promises";
import { pathToFileURL } from "node:url";

// Optional development-only runner. An existing Playwright installation can be
// used via PLAYWRIGHT_MODULE; the web application needs no npm installation.
const { chromium } = await import(
    process.env.PLAYWRIGHT_MODULE
        ? pathToFileURL(process.env.PLAYWRIGHT_MODULE).href
        : "playwright"
);
const base = process.env.RANDI_TEST_URL || "http://127.0.0.1:8787";
const password = process.env.RANDI_TEST_PASSWORD;
assert(password, "Set RANDI_TEST_PASSWORD for the isolated local QA server.");
assert(
    /^http:\/\/(127\.0\.0\.1|localhost)(:\d+)?$/.test(base),
    "Browser writes are restricted to a local QA server.",
);
const output = "artifacts/randi";
await mkdir(output, { recursive: true });
const browser = await chromium.launch({ headless: true });
const failures = [];
const evidence = [];
const admin = await browser.newContext();
const adminPage = await admin.newPage();
const contexts = [admin];

async function check(name, fn) {
    try {
        await fn();
        evidence.push({ name, result: "passed" });
        console.log(`PASS ${name}`);
    } catch (error) {
        failures.push(name);
        evidence.push({ name, result: "failed", error: error.message });
        console.error(`FAIL ${name}: ${error.message}`);
    }
}

async function createInvite(name) {
    await adminPage.goto(`${base}/randi/admin`);
    await adminPage.locator("#recipient-name").fill(name);
    await adminPage
        .locator("#intro-message")
        .fill("Egy böngészős fejlesztői próba.");
    await adminPage
        .getByRole("button", { name: "Személyes link készítése" })
        .click();
    await adminPage.locator("#created-link").waitFor();
    const link = await adminPage.locator("#created-link").inputValue();
    assert(link.startsWith(`${base}/randi/`));
    return link;
}

async function newPage(options = {}) {
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
        ...options,
    });
    contexts.push(context);
    const page = await context.newPage();
    page.on("pageerror", (error) =>
        failures.push(`pageerror: ${error.message}`),
    );
    return { context, page };
}

async function newAdminPage() {
    return newPage({ storageState: await admin.storageState() });
}

async function current(page, step) {
    await page.locator(`#randi-app[data-current-step="${step}"]`).waitFor();
    assert.equal(await page.locator("[data-step]:visible").count(), 1);
}

async function begin(page, link) {
    await page.goto(link);
    await current(page, "invite");
    await page.getByRole("button", { name: "Igen, menjünk" }).click();
    await current(page, "joy");
    await page.getByRole("button", { name: "Akkor találjunk" }).click();
    await current(page, "date");
}

async function discussLater(page) {
    await page.getByLabel("Még egyeztessük", { exact: true }).check();
    await page.getByRole("button", { name: "Jöhet a program" }).click();
    await current(page, "activity");
}

async function assertArena(page) {
    const bounds = await page.locator("#answer-arena").boundingBox();
    const no = await page.locator("#playful-no").boundingBox();
    const yes = await page.locator(".yes-button").boundingBox();
    const decline = await page.locator(".decline-button:visible").boundingBox();
    assert(no.x >= bounds.x - 0.5 && no.y >= bounds.y - 0.5);
    assert(
        no.x + no.width <= bounds.x + bounds.width + 0.5 &&
            no.y + no.height <= bounds.y + bounds.height + 0.5,
    );
    const overlap = (a, b) =>
        a.x < b.x + b.width &&
        a.x + a.width > b.x &&
        a.y < b.y + b.height &&
        a.y + a.height > b.y;
    assert(!overlap(no, yes));
    assert(!overlap(no, decline));
    assert.equal(
        await page.locator("#randi-app").getAttribute("data-current-step"),
        "invite",
    );
}

try {
    await adminPage.goto(`${base}/randi/admin/login`);
    await adminPage.locator("#password").fill(password);
    await adminPage.getByRole("button", { name: "Belépek" }).click();
    await adminPage
        .getByRole("heading", { name: "Meghívók & randitervek" })
        .waitFor();

    await check(
        "320/390/430 px, desktop, mouse dodge and resize boundaries",
        async () => {
            const { page } = await newAdminPage();
            const external = [];
            page.on("request", (req) => {
                if (!req.url().startsWith(base)) external.push(req.url());
            });
            await page.goto(`${base}/randi/demo`);
            for (const width of [320, 390, 430, 1280, 844, 390]) {
                await page.mouse.move(0, 0);
                await page.setViewportSize({
                    width,
                    height: width === 844 ? 390 : 844,
                });
                await page.locator("#answer-arena").scrollIntoViewIfNeeded();
                await page.waitForTimeout(380);
                assert.equal(
                    await page.evaluate(
                        () =>
                            document.documentElement.scrollWidth <= innerWidth,
                    ),
                    true,
                );
                const before = await page.locator("#playful-no").boundingBox();
                await page.mouse.move(
                    before.x + before.width / 2,
                    before.y + before.height / 2,
                );
                await page.waitForTimeout(80);
                await assertArena(page);
                const after = await page.locator("#playful-no").boundingBox();
                assert(
                    before.x !== after.x || before.y !== after.y,
                    `The mouse approach must move the no button at ${width}px.`,
                );
                if (width === 390)
                    await page.screenshot({
                        path: `${output}/invitation-390.png`,
                        fullPage: true,
                        animations: "disabled",
                    });
                if (width === 1280)
                    await page.screenshot({
                        path: `${output}/invitation-desktop.png`,
                        fullPage: true,
                        animations: "disabled",
                    });
                await page.mouse.move(0, 0);
            }
            assert.deepEqual(external, []);
        },
    );

    await check(
        "Touch dodge, synthetic click suppression, orientation and real decline",
        async () => {
            const { page } = await newPage({ hasTouch: true, isMobile: true });
            const link = await createInvite("Touch próba");
            await page.goto(link);
            let before = await page.locator("#playful-no").boundingBox();
            await page.touchscreen.tap(
                before.x + before.width / 2,
                before.y + before.height / 2,
            );
            await page.waitForTimeout(80);
            await assertArena(page);
            const after = await page.locator("#playful-no").boundingBox();
            assert(before.x !== after.x || before.y !== after.y);
            await page.setViewportSize({ width: 844, height: 390 });
            await assertArena(page);
            await page.setViewportSize({ width: 320, height: 740 });
            await assertArena(page);
            await page
                .getByRole("button", {
                    name: "Most inkább kihagyom",
                    exact: true,
                })
                .click();
            await current(page, "declined");
            await page.reload();
            await current(page, "declined");
        },
    );

    await check(
        "Keyboard no is a real decline, reduced motion is static and accessible",
        async () => {
            const link = await createInvite("Billentyűzet próba");
            const { page } = await newPage();
            await page.goto(link);
            await page.locator("#playful-no").focus();
            await page.keyboard.press("Enter");
            await current(page, "declined");
            const reducedLink = await createInvite("Csökkentett mozgás");
            const reduced = await newPage({
                reducedMotion: "reduce",
                hasTouch: true,
                isMobile: true,
            });
            await reduced.page.goto(reducedLink);
            assert.equal(
                await reduced.page
                    .locator("#playful-no")
                    .evaluate((el) => getComputedStyle(el).animationName),
                "none",
            );
            await reduced.page
                .getByRole("button", { name: "Nem 🙈", exact: true })
                .tap();
            await current(reduced.page, "declined");
        },
    );

    await check(
        "Full personal flow, draft reload/back, radio keys, custom program and SQLite success",
        async () => {
            const link = await createInvite("Teljes folyamat");
            const { context, page } = await newPage();
            await begin(page, link);
            await page.locator("#preferred-date").fill("");
            await page.getByRole("button", { name: "Jöhet a program" }).click();
            await page
                .locator("#error-preferred_date")
                .filter({ hasText: "Válassz" })
                .waitFor();
            await page
                .getByRole("button", { name: "Szombat", exact: true })
                .click();
            await page.getByLabel("Este 19:00–22:00", { exact: true }).check();
            const date = await page.locator("#preferred-date").inputValue();
            await page.getByRole("button", { name: "Jöhet a program" }).click();
            await current(page, "activity");
            assert.equal(
                await page.evaluate(
                    () =>
                        document.activeElement ===
                        document.querySelector('[data-step="activity"] h1'),
                ),
                true,
            );
            await page
                .locator('[name="activity"][value="coffee_walk"]')
                .focus();
            await page.keyboard.press("ArrowDown");
            assert.equal(
                await page
                    .locator('[name="activity"][value="dinner"]')
                    .isChecked(),
                true,
            );
            await page.locator('[name="activity"][value="custom"]').check();
            await page.locator("#custom-activity").fill("Egy közös kiállítás");
            await page.locator("#note").fill("Ezt most már várom.");
            await page.reload();
            await current(page, "activity");
            assert.equal(
                await page.locator("#note").inputValue(),
                "Ezt most már várom.",
            );
            assert.equal(
                await page.locator("#custom-activity").inputValue(),
                "Egy közös kiállítás",
            );
            await page.screenshot({
                path: `${output}/activities-390.png`,
                fullPage: true,
                animations: "disabled",
            });
            await page.getByRole("button", { name: "Nézzük a tervet" }).click();
            await current(page, "review");
            await page.goBack();
            await current(page, "activity");
            await page.getByRole("button", { name: "Nézzük a tervet" }).click();
            await page
                .getByRole("button", { name: "Időpont módosítása" })
                .click();
            await current(page, "date");
            assert.equal(
                await page.locator("#preferred-date").inputValue(),
                date,
            );
            await page.getByRole("button", { name: "Jöhet a program" }).click();
            await page.getByRole("button", { name: "Nézzük a tervet" }).click();
            await page
                .getByRole("button", { name: "Mehet a randiterv" })
                .click();
            await current(page, "success");
            assert.equal(await page.evaluate(() => sessionStorage.length), 0);
            await page.screenshot({
                path: `${output}/success-390.png`,
                fullPage: true,
                animations: "disabled",
            });
            await page.reload();
            await current(page, "success");
            assert(
                (
                    await page.locator('[data-step="success"]').textContent()
                ).includes("Egy közös kiállítás"),
            );
            const foreign = await newPage();
            await foreign.page.goto(link);
            await foreign.page
                .getByText("Erre a meghívóra már érkezett válasz.")
                .waitFor();
            assert(
                !(await foreign.page.locator("body").textContent()).includes(
                    "Ezt most már várom.",
                ),
            );
            await adminPage.goto(`${base}/randi/admin`);
            const row = adminPage
                .locator(".invite-row")
                .filter({ hasText: "Teljes folyamat" })
                .first();
            await row.getByText("Válasz és részletek").click();
            assert((await row.textContent()).includes("Egy közös kiállítás"));
            await adminPage.screenshot({
                path: `${output}/admin-desktop.png`,
                fullPage: true,
                animations: "disabled",
            });
            assert(
                (await context.cookies()).some(
                    (cookie) => cookie.httpOnly && cookie.sameSite === "Lax",
                ),
            );
        },
    );

    await check(
        "Discuss later + surprise discards a hidden custom idea; double click is safe",
        async () => {
            const link = await createInvite("Egyeztetés próba");
            const { page } = await newPage();
            await begin(page, link);
            await discussLater(page);
            await page.locator('[name="activity"][value="custom"]').check();
            await page.locator("#custom-activity").fill("Ne kerüljön mentésbe");
            await page.locator('[name="activity"][value="surprise"]').check();
            await page.getByRole("button", { name: "Nézzük a tervet" }).click();
            const outgoing = [];
            page.on("request", (req) => {
                if (req.url().endsWith("/response"))
                    outgoing.push(req.postDataJSON());
            });
            await page
                .getByRole("button", { name: "Mehet a randiterv" })
                .evaluate((button) => {
                    button.click();
                    button.click();
                });
            await current(page, "success");
            assert.equal(outgoing.length, 1);
            assert.equal(outgoing[0].preferred_date, null);
            assert.equal(outgoing[0].custom_activity, null);
        },
    );

    await check(
        "A failed network write preserves the draft, then retries successfully",
        async () => {
            const link = await createInvite("Újrapróbálás próba");
            const { page } = await newPage();
            await begin(page, link);
            await discussLater(page);
            await page.locator('[name="activity"][value="surprise"]').check();
            await page.getByRole("button", { name: "Nézzük a tervet" }).click();
            await page.route("**/response", (route) => route.abort());
            await page
                .getByRole("button", { name: "Mehet a randiterv" })
                .click();
            await page
                .getByText(
                    "Most nem sikerült elmenteni. A választásaid megvannak, próbáld újra.",
                )
                .waitFor();
            await current(page, "review");
            assert.equal(
                await page
                    .getByRole("button", { name: "Mehet a randiterv" })
                    .isEnabled(),
                true,
            );
            await page.unroute("**/response");
            await page
                .getByRole("button", { name: "Mehet a randiterv" })
                .click();
            await current(page, "success");
        },
    );

    await check("Demo completion stays explicitly unsaved", async () => {
        const { page } = await newAdminPage();
        await begin(page, `${base}/randi/demo`);
        await discussLater(page);
        await page.locator('[name="activity"][value="surprise"]').check();
        await page.getByRole("button", { name: "Nézzük a tervet" }).click();
        await page.getByRole("button", { name: "Mehet a randiterv" }).click();
        await current(page, "success");
        assert(
            (
                await page.locator('[data-step="success"]').textContent()
            ).includes("nem mentettünk semmit"),
        );
    });

    await check(
        "JavaScript disabled: HTML forms and confirmed SQLite persistence",
        async () => {
            const link = await createInvite("JavaScript nélkül");
            const { page } = await newPage({ javaScriptEnabled: false });
            await page.goto(link);
            await page.getByRole("button", { name: "Igen, menjünk" }).click();
            await page.getByRole("button", { name: "Akkor találjunk" }).click();
            await page.getByLabel("Még egyeztessük", { exact: true }).check();
            await page.getByRole("button", { name: "Jöhet a program" }).click();
            await page.locator('[name="activity"][value="custom"]').check();
            await page.locator("#custom-activity").fill("Egy múzeum");
            await page.locator("#note").fill("JavaScript nélkül is működik.");
            await page.getByRole("button", { name: "Nézzük a tervet" }).click();
            await page
                .getByRole("button", { name: "Mehet a randiterv" })
                .click();
            await page.locator('[data-step="success"]:visible').waitFor();
            assert(
                (
                    await page.locator('[data-step="success"]').textContent()
                ).includes("Egy múzeum"),
            );
            assert.equal(
                await page.evaluate(
                    () => document.documentElement.scrollWidth <= innerWidth,
                ),
                true,
            );
        },
    );

    await check(
        "Homepage photo, immediate fast rendering, delayed slow preloader, existing routes",
        async () => {
            const { page } = await newPage({
                viewport: { width: 1280, height: 900 },
            });
            await page.goto(base);
            assert.equal(await page.locator("#preloader").isVisible(), false);
            assert.equal(
                await page
                    .locator("#preloader")
                    .textContent()
                    .then((text) => text.trim()),
                "",
            );
            assert(
                (
                    await page.locator("#rolam picture img").getAttribute("src")
                ).includes("profile-2026-720.jpg"),
            );
            assert.equal(
                await page
                    .locator("#rolam picture img")
                    .evaluate((el) => el.complete && el.naturalWidth > 0),
                true,
            );
            await page.screenshot({
                path: `${output}/homepage-photo.png`,
                fullPage: false,
                animations: "disabled",
            });
            await page.route("**/icons/profile-2026-*", async (route) => {
                await new Promise((resolve) => setTimeout(resolve, 2200));
                await route.continue();
            });
            await page.goto(base, { waitUntil: "domcontentloaded" });
            await page.waitForTimeout(850);
            assert.equal(await page.locator("#preloader").isVisible(), true);
            await page.waitForLoadState("load");
            assert.equal(await page.locator("#preloader").isVisible(), false);
            await page.unroute("**/icons/profile-2026-*");
            for (const path of ["/?lang=en", "/skills", "/statistics"]) {
                const response = await page.goto(`${base}${path}`);
                assert.equal(response.status(), 200);
            }
        },
    );

    await check(
        "Admin revoke/delete confirmation/logout work through the UI",
        async () => {
            await createInvite("Visszavonás próba");
            await adminPage.goto(`${base}/randi/admin`);
            const row = adminPage
                .locator(".invite-row")
                .filter({ hasText: "Visszavonás próba" })
                .first();
            await row
                .getByRole("button", { name: "Link visszavonása" })
                .click();
            assert((await row.textContent()).includes("A link visszavonva."));
            await row.getByText("Végleges törlés", { exact: true }).click();
            await row
                .getByLabel("Megerősítés: írd be, hogy TORLES")
                .fill("TORLES");
            await row.getByRole("button", { name: "Végleg törlöm" }).click();
            await adminPage
                .getByText(
                    "A meghívót és a hozzá tartozó választ végleg törölted.",
                )
                .waitFor();
            await adminPage
                .getByRole("button", { name: "Kijelentkezés" })
                .click();
            await adminPage.goto(`${base}/randi/admin`);
            assert(adminPage.url().endsWith("/randi/admin/login"));
        },
    );
} finally {
    await writeFile(
        `${output}/browser-results.json`,
        JSON.stringify({ evidence, failures }, null, 2),
    );
    for (const context of contexts) await context.close();
    await browser.close();
}
assert.equal(failures.length, 0, failures.join("\n"));
console.log(`All ${evidence.length} browser scenarios passed.`);

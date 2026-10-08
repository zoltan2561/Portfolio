(() => {
    "use strict";
    const copyButton = document.getElementById("copy-link");
    if (copyButton) {
        const field = document.getElementById("created-link");
        const status = document.getElementById("copy-status");
        field.addEventListener("click", () => field.select());
        copyButton.addEventListener("click", async () => {
            field.focus();
            field.select();
            try {
                if (!navigator.clipboard?.writeText)
                    throw new Error("clipboard unavailable");
                await navigator.clipboard.writeText(field.value);
                status.textContent =
                    "Kimásolva. Most már csak el kell küldeni. 💌";
            } catch {
                status.textContent =
                    "A linket kijelöltem. Másold a böngésző menüjével vagy Ctrl/Cmd + C-vel.";
            }
        });
    }

    const configElement = document.getElementById("randi-config");
    const app = document.getElementById("randi-app");
    if (!configElement || !app) return;
    const config = JSON.parse(configElement.textContent);
    const form = document.getElementById("randi-form");
    const errors = document.getElementById("randi-errors");
    const motion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const steps = [
        "invite",
        "joy",
        "date",
        "activity",
        "review",
        "success",
        "declined",
    ];
    const terminal = () => ["success", "declined"].includes(step);
    const storageKey = `pz-randi:v1:${config.identity}`;
    let step = config.step;
    let sending = false;

    function read(name) {
        return form.elements.namedItem(name)?.value || "";
    }

    function payload(decision = "accepted") {
        if (decision === "declined")
            return { decision, submission_key: config.key };
        const dateMode = read("date_mode");
        const activity = read("activity");
        return {
            decision,
            submission_key: config.key,
            date_mode: dateMode,
            preferred_date:
                dateMode === "specific_date" ? read("preferred_date") : null,
            time_window:
                dateMode === "specific_date" ? read("time_window") : null,
            activity,
            custom_activity:
                activity === "custom" ? read("custom_activity").trim() : null,
            note: read("note").trim() || null,
        };
    }

    function saveDraft() {
        if (terminal()) return;
        try {
            const data = payload();
            delete data.submission_key;
            sessionStorage.setItem(
                storageKey,
                JSON.stringify({
                    step,
                    data,
                    expires: Date.now() + 2 * 60 * 60 * 1000,
                }),
            );
        } catch {
            /* Storage may be disabled; the form remains usable. */
        }
    }

    function clearDraft() {
        try {
            sessionStorage.removeItem(storageKey);
        } catch {
            /* optional storage */
        }
    }

    function restore() {
        if (terminal()) {
            clearDraft();
            return;
        }
        try {
            const stored = JSON.parse(
                sessionStorage.getItem(storageKey) || "null",
            );
            if (
                !stored ||
                stored.expires < Date.now() ||
                !steps.slice(0, 5).includes(stored.step)
            ) {
                clearDraft();
                return;
            }
            for (const name of [
                "date_mode",
                "preferred_date",
                "time_window",
                "activity",
                "custom_activity",
                "note",
            ]) {
                const value = stored.data?.[name];
                if (value !== null && typeof value !== "string") continue;
                const elements = Array.from(
                    form.querySelectorAll(`[name="${name}"]`),
                );
                elements.forEach((el) => {
                    if (el.type === "radio") el.checked = el.value === value;
                    else el.value = value || "";
                });
            }
            step = stored.step;
        } catch {
            clearDraft();
        }
    }

    function clearErrors() {
        errors.replaceChildren();
        errors.hidden = true;
        form.querySelectorAll("[data-error]").forEach((el) => {
            el.textContent = "";
        });
        form.querySelectorAll("[aria-invalid]").forEach((el) =>
            el.removeAttribute("aria-invalid"),
        );
    }

    function setErrors(messages, message = "Egy apróság még hiányzik.") {
        clearErrors();
        const heading = document.createElement("p");
        heading.textContent = message;
        errors.append(heading);
        errors.hidden = false;
        let first = null;
        Object.entries(messages || {}).forEach(([name, lines]) => {
            const text = Array.isArray(lines) ? lines.join(" ") : lines;
            const target = form.querySelector(`[data-error="${name}"]`);
            if (target) target.textContent = text;
            else {
                const item = document.createElement("p");
                item.textContent = text;
                errors.append(item);
            }
            form.querySelectorAll(`[name="${name}"]`).forEach((el) => {
                el.setAttribute("aria-invalid", "true");
                if (!first && !el.disabled) first = el;
            });
        });
        if (first) first.focus();
    }

    function budapestNow() {
        const parts = new Intl.DateTimeFormat("en-GB", {
            timeZone: "Europe/Budapest",
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            hourCycle: "h23",
        }).formatToParts(new Date());
        const values = Object.fromEntries(parts.map((p) => [p.type, p.value]));
        return {
            today: `${values.year}-${values.month}-${values.day}`,
            hour: Number(values.hour),
        };
    }

    function plusDays(date, count) {
        const [year, month, day] = date.split("-").map(Number);
        const result = new Date(Date.UTC(year, month - 1, day + count));
        return `${result.getUTCFullYear()}-${String(result.getUTCMonth() + 1).padStart(2, "0")}-${String(result.getUTCDate()).padStart(2, "0")}`;
    }

    function updateConditionalFields() {
        const specific = read("date_mode") === "specific_date";
        const dateFields = document.getElementById("specific-date-fields");
        dateFields.hidden = !specific;
        dateFields.querySelectorAll("input").forEach((el) => {
            el.disabled = !specific;
        });
        const custom = read("activity") === "custom";
        document.getElementById("custom-activity-field").hidden = !custom;
        form.elements.namedItem("custom_activity").disabled = !custom;
        const now = budapestNow();
        const date = form.elements.namedItem("preferred_date");
        date.min = now.today;
        date.max = plusDays(now.today, 60);
        form.querySelectorAll('[name="time_window"]').forEach((el) => {
            const end = config.timeWindows[el.value][2];
            const elapsed =
                !terminal() &&
                specific &&
                date.value === now.today &&
                end !== null &&
                now.hour >= end;
            el.disabled = !specific || elapsed;
            if (elapsed) el.checked = false;
        });
    }

    function summarize(data = payload()) {
        let dateText = "Még egyeztetjük";
        let timeText = "";
        if (data.date_mode === "specific_date" && data.preferred_date) {
            const [year, month, day] = data.preferred_date
                .split("-")
                .map(Number);
            dateText = new Intl.DateTimeFormat("hu-HU", {
                timeZone: "Europe/Budapest",
                year: "numeric",
                month: "long",
                day: "numeric",
                weekday: "long",
            }).format(new Date(Date.UTC(year, month - 1, day, 12)));
            timeText = (config.timeWindows[data.time_window] || [])
                .slice(0, 2)
                .filter(Boolean)
                .join(" · ");
        }
        const activity = config.activities[data.activity];
        const values = {
            date: dateText,
            time: timeText,
            activity: activity ? `${activity[0]} ${activity[1]}` : "",
            custom: data.activity === "custom" ? data.custom_activity : "",
            note: data.note,
        };
        Object.entries(values).forEach(([name, value]) => {
            form.querySelectorAll(`[data-summary="${name}"]`).forEach((el) => {
                el.textContent = value || "";
            });
        });
        form.querySelectorAll("[data-summary-note]").forEach((el) => {
            el.hidden = !data.note;
        });
    }

    function show(next, { focus = true, history = true } = {}) {
        if (!steps.includes(next) || (terminal() && next !== step)) return;
        step = next;
        app.dataset.currentStep = step;
        form.querySelectorAll("[data-step]").forEach((panel) => {
            panel.hidden = panel.dataset.step !== step;
        });
        const progress = app.querySelector(".step-indicator");
        progress.hidden = !["date", "activity", "review"].includes(step);
        progress.querySelectorAll("[data-progress]").forEach((el) => {
            if (el.dataset.progress === step)
                el.setAttribute("aria-current", "step");
            else el.removeAttribute("aria-current");
        });
        updateConditionalFields();
        if (["review", "success"].includes(step)) summarize();
        if (history)
            window.history.pushState(
                { randi: config.identity, step },
                "",
                window.location.href,
            );
        if (focus) {
            const title = form.querySelector(`[data-step="${step}"] h1`);
            title?.focus({ preventScroll: true });
            app.scrollIntoView({ behavior: "instant", block: "start" });
        }
        if (terminal()) clearDraft();
        else saveDraft();
    }

    function validateDate() {
        const data = payload();
        const problems = {};
        if (!["specific_date", "discuss_later"].includes(data.date_mode))
            problems.date_mode =
                "Válassz egy napot, vagy jelöld, hogy még egyeztessük.";
        if (data.date_mode === "specific_date") {
            const dateField = form.elements.namedItem("preferred_date");
            if (!data.preferred_date || !dateField.validity.valid)
                problems.preferred_date =
                    "Válassz egy napot a következő 60 napból.";
            if (!data.time_window)
                problems.time_window = "Válassz egy idősávot.";
        }
        if (Object.keys(problems).length) {
            setErrors(problems);
            return false;
        }
        return true;
    }

    function validateActivity() {
        const data = payload();
        const problems = {};
        if (!config.activities[data.activity])
            problems.activity =
                "Válassz egy programot, vagy írd le a saját ötleted.";
        if (
            data.activity === "custom" &&
            (!data.custom_activity || [...data.custom_activity].length > 200)
        )
            problems.custom_activity =
                "Írd le a saját ötleted legfeljebb 200 karakterben.";
        if (data.note && [...data.note].length > 280)
            problems.note = "Az üzenet legfeljebb 280 karakter lehet.";
        if (Object.keys(problems).length) {
            setErrors(problems);
            return false;
        }
        return true;
    }

    function celebrate() {
        if (motion.matches) return;
        const container = document.getElementById("confetti");
        for (let i = 0; i < 12; i++) {
            const heart = document.createElement("span");
            heart.className = "confetti-piece";
            heart.textContent = i % 3 ? "♡" : "✧";
            heart.style.setProperty(
                "--x",
                `${Math.cos((i * Math.PI) / 6) * 110}px`,
            );
            heart.style.setProperty(
                "--y",
                `${Math.sin((i * Math.PI) / 6) * 95 - 35}px`,
            );
            heart.style.setProperty("--r", `${i * 30}deg`);
            container.append(heart);
        }
        window.setTimeout(() => container.replaceChildren(), 1100);
    }

    async function send(decision, button) {
        if (sending || terminal()) return;
        if (decision === "accepted" && (!validateDate() || !validateActivity()))
            return;
        clearErrors();
        saveDraft();
        sending = true;
        app.dataset.state = "sending";
        const buttons = [...form.querySelectorAll("button")];
        buttons.forEach((el) => {
            el.disabled = true;
        });
        const original = button.textContent;
        button.textContent = config.demo
            ? "Egy pillanat…"
            : "Mentem a válaszod…";
        button.setAttribute("aria-busy", "true");
        const abort = new AbortController();
        const timeout = window.setTimeout(() => abort.abort(), 15000);
        try {
            const result = await fetch(config.responseUrl, {
                method: "POST",
                credentials: "same-origin",
                signal: abort.signal,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": read("_token"),
                },
                body: JSON.stringify(payload(decision)),
            });
            const body = await result.json();
            if (!result.ok || body.ok !== true) {
                if (body.errors) {
                    const dateError = Object.keys(body.errors).some((name) =>
                        ["date_mode", "preferred_date", "time_window"].includes(
                            name,
                        ),
                    );
                    show(dateError ? "date" : "activity");
                }
                setErrors(
                    body.errors || {},
                    body.message ||
                        "Most nem sikerült elmenteni. A választásaid megvannak, próbáld újra.",
                );
                app.dataset.state = "error";
                if ([409, 410, 419].includes(result.status)) {
                    const link = document.createElement("a");
                    link.href = window.location.pathname;
                    link.className = "text-button";
                    link.textContent = "Meghívó újranyitása →";
                    errors.append(link);
                }
                return;
            }
            show(decision === "declined" ? "declined" : "success", {
                history: false,
            });
            summarize(body.response);
            window.history.replaceState(
                { randi: config.identity, step },
                "",
                window.location.href,
            );
            if (config.demo) {
                form.querySelector("[data-success-label]").textContent =
                    "A demó végére értél · nincs mentés";
                form.querySelector("[data-success-copy]").textContent =
                    "Éles meghívóban itt lenne a mentett válaszod. Ebben a bemutatóban nem mentettünk semmit.";
            }
            clearDraft();
            app.dataset.state = step;
        } catch {
            setErrors(
                {},
                "Most nem sikerült elmenteni. A választásaid megvannak, próbáld újra.",
            );
            app.dataset.state = "error";
        } finally {
            window.clearTimeout(timeout);
            sending = false;
            buttons.forEach((el) => {
                el.disabled = false;
            });
            button.textContent = original;
            button.removeAttribute("aria-busy");
        }
    }

    // Keep the playful button in its own arena. Only validated, non-overlapping
    // destinations are used. Pointer attempts never submit an actual decision.
    const no = document.getElementById("playful-no");
    const arena = document.getElementById("answer-arena");
    const yes = arena.querySelector(".yes-button");
    const playfulMessage = document.getElementById("playful-message");
    const messages = [
        "Hopp, ez arrébb ment. 😇",
        "Ez a gomb ma kicsit félénk. 🙈",
        "Technikai probléma. Teljesen véletlen. 👀",
        "Az igen legalább megvár. 😌",
    ];
    let attempts = 0;
    let lastMove = -Infinity;
    let keyboard = false;
    let suppressPointerClickUntil = 0;
    let recentPositions = [];
    document.addEventListener("keydown", (event) => {
        if (event.key === "Tab") keyboard = true;
    });
    document.addEventListener(
        "pointerdown",
        () => {
            keyboard = false;
        },
        true,
    );

    const intersects = (a, b, margin = 8) =>
        a.left < b.right + margin &&
        a.right > b.left - margin &&
        a.top < b.bottom + margin &&
        a.bottom > b.top - margin;

    function dodge(event) {
        if (motion.matches || keyboard || step !== "invite") return;
        const now = performance.now();
        if (now - lastMove < 350) return;
        const bounds = arena.getBoundingClientRect();
        const current = no.getBoundingClientRect();
        const forbidden = [
            yes,
            playfulMessage,
            ...form.querySelectorAll(".decline-button"),
        ].map((el) => el.getBoundingClientRect());
        const maxX = Math.max(0, bounds.width - current.width);
        const maxY = Math.max(0, bounds.height - current.height - 10);
        const candidates = [];
        for (let row = 0; row <= 4; row++) {
            const y = (maxY * row) / 4;
            for (let column = 0; column <= 6; column++) {
                const x = (maxX * column) / 6;
                const target = {
                    left: bounds.left + x,
                    top: bounds.top + y,
                    right: bounds.left + x + current.width,
                    bottom: bounds.top + y + current.height,
                };
                const distance = Math.hypot(
                    target.left - current.left,
                    target.top - current.top,
                );
                const coversPointer =
                    event &&
                    event.clientX >= target.left - 10 &&
                    event.clientX <= target.right + 10 &&
                    event.clientY >= target.top - 10 &&
                    event.clientY <= target.bottom + 10;
                if (
                    distance > 45 &&
                    !coversPointer &&
                    !forbidden.some((rect) => intersects(target, rect))
                )
                    candidates.push({ x, y, distance });
            }
        }
        if (!candidates.length) return;
        const fresh = candidates.filter((candidate) =>
            !recentPositions.some((position) =>
                Math.hypot(candidate.x - position.x, candidate.y - position.y) < 28,
            ),
        );
        const choices = fresh.length ? fresh : candidates;
        const destination = choices[Math.floor(Math.random() * choices.length)];
        recentPositions.push({ x: destination.x, y: destination.y });
        recentPositions = recentPositions.slice(-4);
        no.style.left = `${destination.x}px`;
        no.style.top = `${destination.y}px`;
        no.style.right = "auto";
        lastMove = now;
        playfulMessage.textContent =
            messages[Math.min(attempts++, messages.length - 1)];
    }

    function resetArena() {
        no.style.left = "";
        no.style.top = "";
        no.style.right = "";
        lastMove = -Infinity;
        recentPositions = [];
    }

    no.addEventListener("pointerenter", (event) => {
        if (event.pointerType === "mouse") dodge(event);
    });
    arena.addEventListener("pointermove", (event) => {
        if (event.pointerType !== "mouse" || motion.matches || keyboard) return;
        const rect = no.getBoundingClientRect();
        if (
            event.clientX >= rect.left - 22 &&
            event.clientX <= rect.right + 22 &&
            event.clientY >= rect.top - 16 &&
            event.clientY <= rect.bottom + 16
        )
            dodge(event);
    });
    no.addEventListener("pointerdown", (event) => {
        if (motion.matches) return;
        event.preventDefault();
        event.stopPropagation();
        suppressPointerClickUntil = performance.now() + 600;
        dodge(event);
    });
    no.addEventListener("click", (event) => {
        if (
            !motion.matches &&
            (event.detail > 0 ||
                ["mouse", "touch", "pen"].includes(event.pointerType))
        ) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }
    });
    // Absorb the synthetic click after a touch dodge before it can hit the yes.
    arena.addEventListener(
        "click",
        (event) => {
            if (
                !motion.matches &&
                (event.detail > 0 ||
                    ["mouse", "touch", "pen"].includes(event.pointerType)) &&
                performance.now() < suppressPointerClickUntil
            ) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        },
        true,
    );
    new ResizeObserver(resetArena).observe(arena);
    window.addEventListener("orientationchange", resetArena);
    motion.addEventListener("change", resetArena);

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        if (sending || terminal()) return;
        const button = event.submitter;
        if (!button) return;
        if (button.hasAttribute("data-decline")) {
            send("declined", button);
            return;
        }
        if (button.hasAttribute("data-send")) {
            send("accepted", button);
            return;
        }
        const next = button.dataset.go;
        if (!next) return;
        clearErrors();
        updateConditionalFields();
        if (step === "date" && next === "activity" && !validateDate()) return;
        if (step === "activity" && next === "review" && !validateActivity())
            return;
        const firstYes = step === "invite" && next === "joy";
        show(next);
        if (firstYes) celebrate();
    });
    form.addEventListener("input", () => {
        updateConditionalFields();
        saveDraft();
    });
    form.addEventListener("change", () => {
        updateConditionalFields();
        saveDraft();
    });
    document.getElementById("quick-dates").hidden = false;
    document
        .getElementById("quick-dates")
        .addEventListener("click", (event) => {
            const button = event.target.closest("[data-weekday]");
            if (!button) return;
            const today = budapestNow().today;
            const weekday = new Date(`${today}T12:00:00Z`).getUTCDay();
            form.elements.namedItem("preferred_date").value = plusDays(
                today,
                (Number(button.dataset.weekday) - weekday + 7) % 7,
            );
            updateConditionalFields();
            saveDraft();
        });
    window.addEventListener("popstate", (event) => {
        if (!terminal() && event.state?.randi === config.identity)
            show(event.state.step, { history: false });
    });
    window.addEventListener("pageshow", (event) => {
        if (event.persisted && !config.demo) window.location.reload();
    });
    restore();
    app.classList.add("randi-app-ready");
    show(step, { focus: false, history: false });
    window.history.replaceState(
        { randi: config.identity, step },
        "",
        window.location.href,
    );
})();

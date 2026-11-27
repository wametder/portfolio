/**
 * Work-Rad: endloses Drehrad mit Projekttiteln, Vorschaubild, Illustration
 * und einer Frame-Animation auf einem Canvas, die an der Drehposition hängt.
 *
 * Einheiten: `position` und `target` sind in "Slots" (1 Slot = 1 Titel).
 */

const MIN_ITEMS = 10;         // so viele Einträge braucht das Rad mindestens (sonst wird geklont)
const VISIBLE_RANGE = 4;      // ab diesem Abstand zur Mitte ist ein Titel unsichtbar (in Ruhe 7 sichtbar)
const ROWS_IN_VIEW = 7;       // Titel, die in Ruhe in die Listenhöhe passen müssen
const ROW_FALLBACK = 44;      // Zeilenhöhe, falls --work-wheel-row im CSS fehlt
const ROW_MIN = 24;           // minimale Zeilenhöhe in px
const LERP = 0.14;            // Trägheit pro Frame (bei 60 fps)
const WHEEL_PX_PER_SLOT = 110; // Mausrad-/Trackpad-Pixel pro Projektschritt
const WHEEL_STEP_THRESHOLD = 40; // reduced motion: Pixel bis zu einem Schritt
const SNAP_DELAY = 140;       // ms Ruhe, bevor eingerastet wird
const DRAG_THRESHOLD = 6;     // px, ab denen ein Touch als Wischen gilt (kein Klick)
const MOMENTUM = 180;         // ms, wie weit der Schwung nach dem Wischen trägt
const FADE_MS = 250;          // muss zu --work-wheel-fade im CSS passen

const mod = (a, b) => ((a % b) + b) % b;

export function initWorkWheel(root) {
    const list = root.querySelector('.work-wheel__list');
    const originals = list ? [...list.querySelectorAll(':scope > .work-wheel__item')] : [];
    const n = originals.length;
    if (!n) {
        return;
    }

    const previewLink = root.querySelector('.work-wheel__preview');
    const illustrationBox = root.querySelector('.work-wheel__illustration');
    const canvas = root.querySelector('.work-wheel__animation canvas');

    // -> reduced motion (reagiert live auf Änderungen)
    const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let reduced = motionQuery.matches;
    motionQuery.addEventListener('change', (e) => {
        reduced = e.matches;
        if (reduced) {
            target = Math.round(target);
            start();
        }
    });

    // -> Liste bei wenigen Projekten intern wiederholen (Klone sind für Screenreader und Tastatur unsichtbar)
    const copies = Math.max(1, Math.ceil(MIN_ITEMS / n));
    for (let c = 1; c < copies; c++) {
        originals.forEach((item) => {
            const clone = item.cloneNode(true);
            clone.classList.remove('work-wheel__item--active');
            clone.setAttribute('aria-hidden', 'true');
            clone.querySelectorAll('a').forEach((a) => a.setAttribute('tabindex', '-1'));
            list.appendChild(clone);
        });
    }
    const items = [...list.querySelectorAll(':scope > .work-wheel__item')];
    const m = items.length;

    let position = 0;
    let target = 0;
    let rowHeight = 60;
    let activeSlot = 0;
    let activeIndex = 0;
    let running = false;
    let lastTime = 0;
    let snapTimer = null;

    root.classList.add('is-ready');

    // ------------------------------------------------------------------
    // Rendering
    // ------------------------------------------------------------------
    function measure() {
        // Zeilenhöhe aus dem CSS (--work-wheel-row, z. B. in rem), aber so klein, dass 7 Titel in die Liste passen.
        // Über ein Mess-Element auslesen, damit jede CSS-Einheit korrekt in px umgerechnet wird.
        const probe = document.createElement('div');
        probe.style.cssText = 'position:absolute;visibility:hidden;height:var(--work-wheel-row);';
        root.appendChild(probe);
        const cssRow = probe.getBoundingClientRect().height || ROW_FALLBACK;
        probe.remove();
        const fitRow = list.clientHeight / ROWS_IN_VIEW || cssRow;
        rowHeight = Math.max(ROW_MIN, Math.min(cssRow, fitRow));
        root.style.setProperty('--work-wheel-row-current', `${rowHeight}px`);
    }

    function render() {
        for (let i = 0; i < m; i++) {
            let d = mod(i - position + m / 2, m) - m / 2;
            const dist = Math.abs(d);
            const opacity = Math.max(0, 1 - dist / VISIBLE_RANGE);
            const scale = 1 - Math.min(dist, VISIBLE_RANGE) * 0.06;
            const style = items[i].style;
            style.transform = `translateY(calc(${(d * rowHeight).toFixed(2)}px - 50%)) scale(${scale.toFixed(3)})`;
            style.opacity = opacity.toFixed(3);
            style.visibility = opacity <= 0.001 ? 'hidden' : 'visible';
        }

        const slot = mod(Math.round(position), m);
        if (slot !== activeSlot) {
            items[activeSlot].classList.remove('work-wheel__item--active');
            items[slot].classList.add('work-wheel__item--active');
            activeSlot = slot;
        }
        const index = slot % n;
        if (index !== activeIndex) {
            activeIndex = index;
            showProject(index);
        }

        animation.update(position);
    }

    // -> rAF-Loop, läuft nur solange sich etwas bewegt
    function start() {
        if (running) {
            return;
        }
        running = true;
        lastTime = performance.now();
        requestAnimationFrame(tick);
    }

    function tick(now) {
        const dt = Math.min(64, now - lastTime);
        lastTime = now;
        if (reduced || dragging) {
            position = target;
        } else {
            const factor = 1 - Math.pow(1 - LERP, dt / (1000 / 60));
            position += (target - position) * factor;
            if (Math.abs(target - position) < 0.0005) {
                position = target;
            }
        }
        render();
        if (position !== target) {
            requestAnimationFrame(tick);
        } else {
            running = false;
        }
    }

    function scheduleSnap() {
        clearTimeout(snapTimer);
        snapTimer = setTimeout(() => {
            target = Math.round(target);
            start();
        }, SNAP_DELAY);
    }

    function step(direction) {
        clearTimeout(snapTimer);
        target = Math.round(target) + direction;
        start();
    }

    // ------------------------------------------------------------------
    // Vorschau & Illustration (Crossfade)
    // ------------------------------------------------------------------
    const preloaded = new Set();
    function preload(src) {
        if (src && !preloaded.has(src)) {
            preloaded.add(src);
            const img = new Image();
            img.src = src;
        }
    }

    function swapImage(container, src, alt, state) {
        if (!container) {
            return;
        }
        const token = ++state.token;
        const retire = (keep) => {
            container.querySelectorAll('.work-wheel__layer').forEach((layer) => {
                if (layer === keep) {
                    return;
                }
                layer.classList.remove('is-current');
                layer.alt = '';
                layer.setAttribute('aria-hidden', 'true');
                setTimeout(() => layer.remove(), reduced ? 0 : FADE_MS + 50);
            });
        };

        if (!src) {
            retire(null);
            return;
        }

        const img = new Image();
        img.className = 'work-wheel__layer';
        img.alt = alt || '';
        img.decoding = 'async';
        img.draggable = false;
        img.src = src;
        const ready = img.decode ? img.decode() : new Promise((res, rej) => {
            img.onload = res;
            img.onerror = rej;
        });
        ready.then(() => {
            if (token !== state.token) {
                return;
            }
            container.appendChild(img);
            // Reflow erzwingen, damit die Transition startet
            void img.offsetWidth;
            img.classList.add('is-current');
            retire(img);
        }, () => {
            if (token === state.token) {
                retire(null);
            }
        });
    }

    const previewState = { token: 0 };
    const illustrationState = { token: 0 };

    function showProject(index) {
        const item = originals[index];
        const link = item.querySelector('a');
        if (previewLink && link) {
            previewLink.href = link.href;
        }
        swapImage(previewLink, item.dataset.previewSrc, item.dataset.previewAlt, previewState);
        swapImage(illustrationBox, item.dataset.illustrationSrc, item.dataset.illustrationAlt, illustrationState);

        for (const offset of [-2, -1, 1, 2]) {
            const neighbour = originals[mod(index + offset, n)];
            preload(neighbour.dataset.previewSrc);
            preload(neighbour.dataset.illustrationSrc);
        }
    }

    // ------------------------------------------------------------------
    // Canvas-Animation
    // ------------------------------------------------------------------
    const animation = createAnimation(canvas, root);

    // ------------------------------------------------------------------
    // Eingaben
    // ------------------------------------------------------------------
    let wheelAccumulator = 0;
    // Scrollen auf der ganzen Seite dreht das Rad (die Seite selbst scrollt nicht)
    window.addEventListener('wheel', (e) => {
        if (e.ctrlKey) {
            return; // Pinch-Zoom auf dem Trackpad nicht blockieren
        }
        e.preventDefault();
        let delta = Math.abs(e.deltaY) >= Math.abs(e.deltaX) ? e.deltaY : e.deltaX;
        if (e.deltaMode === 1) {
            delta *= 16;
        } else if (e.deltaMode === 2) {
            delta *= root.clientHeight;
        }

        if (reduced) {
            wheelAccumulator += delta;
            if (Math.abs(wheelAccumulator) >= WHEEL_STEP_THRESHOLD) {
                step(Math.sign(wheelAccumulator));
                wheelAccumulator = 0;
            }
            return;
        }

        // ein einzelnes Event (z. B. Mausrad-Raste) bewegt höchstens um einen Slot
        target += Math.max(-1, Math.min(1, delta / WHEEL_PX_PER_SLOT));
        start();
        scheduleSnap();
    }, { passive: false });

    // -> Touch
    let dragging = false;
    let touchLastY = 0;
    let touchLastTime = 0;
    let touchMoved = 0;
    let velocity = 0; // px pro ms
    let suppressClick = false;

    window.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) {
            return;
        }
        clearTimeout(snapTimer);
        dragging = true;
        suppressClick = false;
        touchLastY = e.touches[0].clientY;
        touchLastTime = performance.now();
        touchMoved = 0;
        velocity = 0;
        // laufende Trägheit anhalten, Rad "greifen"
        target = position;
    }, { passive: true });

    window.addEventListener('touchmove', (e) => {
        if (!dragging) {
            return;
        }
        e.preventDefault();
        const y = e.touches[0].clientY;
        const now = performance.now();
        const dy = y - touchLastY;
        const dt = Math.max(1, now - touchLastTime);
        velocity = velocity * 0.6 + (dy / dt) * 0.4;
        touchLastY = y;
        touchLastTime = now;
        touchMoved += Math.abs(dy);
        if (touchMoved > DRAG_THRESHOLD) {
            suppressClick = true;
        }
        target -= dy / rowHeight;
        start();
    }, { passive: false });

    const endTouch = () => {
        if (!dragging) {
            return;
        }
        dragging = false;
        if (!suppressClick) {
            // nur getippt: Position nicht verändern
            target = Math.round(target);
        } else if (reduced) {
            target = Math.round(target);
        } else {
            const stale = performance.now() - touchLastTime > 80;
            const momentum = stale ? 0 : -velocity * MOMENTUM / rowHeight;
            target = Math.round(target + momentum);
        }
        start();
    };
    window.addEventListener('touchend', endTouch);
    window.addEventListener('touchcancel', endTouch);

    // -> Klick nach Wischen unterdrücken (sonst öffnet sich beim Wischen ein Projekt)
    window.addEventListener('click', (e) => {
        if (suppressClick) {
            e.preventDefault();
            e.stopPropagation();
            suppressClick = false;
        }
    }, true);

    // -> Tastatur (Seite scrollt nicht, daher global, ausser in Eingabefeldern)
    window.addEventListener('keydown', (e) => {
        if (e.defaultPrevented || e.altKey || e.ctrlKey || e.metaKey) {
            return;
        }
        const tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable) {
            return;
        }
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
            e.preventDefault();
            step(1);
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
            e.preventDefault();
            step(-1);
        }
    });

    // -> Tab-Fokus auf einen Titel dreht dieses Projekt in die Mitte
    list.addEventListener('focusin', (e) => {
        const item = e.target.closest('.work-wheel__item');
        if (!item) {
            return;
        }
        const index = items.indexOf(item) % n;
        const base = Math.round(target);
        let diff = mod(index - base, n);
        if (diff > n / 2) {
            diff -= n;
        }
        clearTimeout(snapTimer);
        target = base + diff;
        start();
    });
    // Fokus darf die Liste nicht scrollen (die Positionen setzt JS)
    list.addEventListener('scroll', () => {
        list.scrollTop = 0;
    });

    // -> Grösse
    new ResizeObserver(() => {
        measure();
        render();
    }).observe(list);

    measure();
    render();
    for (const offset of [-2, -1, 1, 2]) {
        preload(originals[mod(offset, n)].dataset.previewSrc);
    }
}

/**
 * Lädt die Frames vor und zeichnet je nach Drehposition den passenden Frame.
 * Ohne Canvas oder ohne Frames passiert nichts.
 */
function createAnimation(canvas, root) {
    let frames = [];
    try {
        frames = JSON.parse(root.dataset.frames || '[]');
    } catch {
        frames = [];
    }
    const ctx = canvas ? canvas.getContext('2d') : null;
    if (!ctx || !Array.isArray(frames) || !frames.length) {
        return { update() {} };
    }

    const framesPerStep = Math.max(1, parseInt(root.dataset.framesPerStep, 10) || 3);
    const count = frames.length;
    let wanted = 0;
    let drawn = -1;

    const images = frames.map((src, i) => {
        const img = new Image();
        img.decoding = 'async';
        img.onload = () => {
            if (i === wanted && drawn !== wanted) {
                draw(i);
            }
        };
        img.src = src;
        return img;
    });

    function draw(index) {
        const img = images[index];
        if (!img.complete || !img.naturalWidth) {
            return; // noch nicht geladen: letzten Frame stehen lassen
        }
        const { width, height } = canvas;
        if (!width || !height) {
            return;
        }
        const ratio = Math.min(width / img.naturalWidth, height / img.naturalHeight);
        const w = img.naturalWidth * ratio;
        const h = img.naturalHeight * ratio;
        ctx.clearRect(0, 0, width, height);
        ctx.drawImage(img, (width - w) / 2, (height - h) / 2, w, h);
        drawn = index;
    }

    new ResizeObserver(() => {
        const dpr = Math.min(2, window.devicePixelRatio || 1);
        const rect = canvas.getBoundingClientRect();
        canvas.width = Math.round(rect.width * dpr);
        canvas.height = Math.round(rect.height * dpr);
        drawn = -1;
        draw(wanted);
    }).observe(canvas);

    return {
        update(position) {
            wanted = mod(Math.floor(position * framesPerStep + 1e-6), count);
            if (wanted !== drawn) {
                draw(wanted);
            }
        },
    };
}

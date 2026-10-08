if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function setupMatrix() {
  const canvas = document.getElementById('matrix');
  if (!canvas) return;
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const fontSize = 20;
  const columnWidth = 22;
  const frameInterval = 1000 / 30;
  // Cache glyphs once; the animation only blits small images.
  const glyphs = ['#00ff00', '#66ffe0'].map((color) => ['0', '1'].map((letter) => {
    const glyph = document.createElement('canvas');
    glyph.width = columnWidth;
    glyph.height = fontSize + 4;
    const glyphCtx = glyph.getContext('2d');
    glyphCtx.font = `${fontSize}px monospace`;
    glyphCtx.fillStyle = color;
    glyphCtx.fillText(letter, 0, fontSize);
    return glyph;
  }));
  let width = 0;
  let height = 0;
  let drops = [];
  let frameId = null;
  let resizeId = null;
  let previousTime = 0;
  let lastDraw = 0;
  let elapsed = 0;
  let scrollDepth = 0;
  let scrollBoost = 0;
  let lastScrollY = window.scrollY;
  let lastScrollTime = performance.now();
  let ready = false;

  function updateDepth() {
    const maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    scrollDepth = Math.min(1, Math.max(0, window.scrollY / maxScroll));
  }

  function resizeCanvas() {
    resizeId = null;
    const nextWidth = window.innerWidth;
    const nextHeight = window.innerHeight;
    if (nextWidth === width && nextHeight === height) return;
    const oldHeight = height;
    width = nextWidth;
    height = nextHeight;
    // One CSS pixel per canvas pixel bounds the rendering cost on high-DPI phones.
    canvas.width = width;
    canvas.height = height;
    const columns = Math.ceil(width / columnWidth);
    drops = Array.from({ length: columns }, (_, index) => ({
      y: drops[index] ? drops[index].y * height / Math.max(1, oldHeight) : Math.random() * height,
      speed: drops[index]?.speed ?? 0.8 + Math.random() * 0.4,
      bit: index % 2
    }));
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, width, height);
    // Seed a complete rain field so there is no synchronised start at the top.
    drops.forEach((drop, index) => {
      for (let trail = 8; trail >= 0; trail -= 1) {
        ctx.globalAlpha = (1 - trail / 9) * (width <= 600 ? 0.55 : 0.92);
        ctx.drawImage(glyphs[0][(drop.bit + trail) % 2], index * columnWidth, Math.floor(drop.y / fontSize) * fontSize - trail * fontSize);
      }
    });
    ctx.globalAlpha = 1;
    updateDepth();
  }

  function animate(timestamp) {
    if (document.hidden || motion.matches) {
      stop();
      return;
    }
    const delta = previousTime ? Math.min(50, timestamp - previousTime) : 0;
    previousTime = timestamp;
    elapsed += delta;
    scrollBoost *= Math.exp(-delta / 280);
    if (timestamp - lastDraw >= frameInterval - 1) {
      // Fade and movement depend on elapsed time, never on monitor refresh rate.
      const fadeDuration = width <= 600 ? 420 : 740;
      ctx.fillStyle = `rgba(0, 0, 0, ${1 - Math.exp(-elapsed / fadeDuration)})`;
      ctx.fillRect(0, 0, width, height);
      const seconds = elapsed / 1000;
      drops.forEach((drop, index) => {
        drop.y += drop.speed * (width <= 600 ? 155 : 440) * seconds * (1 + scrollDepth * 0.2 + scrollBoost * 0.3);
        if (drop.y > height + 12 * fontSize) drop.y = -Math.random() * height * 0.25;
        const row = Math.floor(drop.y / fontSize);
        if (row !== drop.row) drop.bit = Math.random() < 0.5 ? 0 : 1;
        drop.row = row;
        ctx.globalAlpha = width <= 600 ? 0.55 : 0.92;
        ctx.drawImage(glyphs[scrollDepth > 0.5 ? 1 : 0][drop.bit], index * columnWidth, row * fontSize);
      });
      ctx.globalAlpha = 1;
      elapsed = 0;
      // Preserve the remainder to avoid uneven 33/50 ms frame scheduling.
      lastDraw = timestamp - ((timestamp - lastDraw) % frameInterval);
    }
    frameId = window.requestAnimationFrame(animate);
  }

  function start() {
    canvas.hidden = motion.matches;
    if (!ready || document.hidden || motion.matches || frameId !== null) return;
    previousTime = 0;
    elapsed = 0;
    lastDraw = performance.now();
    frameId = window.requestAnimationFrame(animate);
  }

  function stop() {
    if (frameId !== null) window.cancelAnimationFrame(frameId);
    frameId = null;
    previousTime = 0;
    elapsed = 0;
  }

  function boot() {
    const begin = () => {
      ready = true;
      start();
    };
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(begin, { timeout: 500 });
    } else {
      window.requestAnimationFrame(begin);
    }
  }

  resizeCanvas();
  canvas.hidden = motion.matches;
  if (document.readyState === 'complete') boot();
  else window.addEventListener('load', boot, { once: true });

  window.addEventListener('resize', () => {
    if (resizeId === null) resizeId = window.requestAnimationFrame(resizeCanvas);
  }, { passive: true });
  window.addEventListener('scroll', () => {
    const now = performance.now();
    scrollBoost = Math.min(1.5, Math.abs(window.scrollY - lastScrollY) / Math.max(16, now - lastScrollTime));
    lastScrollY = window.scrollY;
    lastScrollTime = now;
    updateDepth();
  }, { passive: true });
  document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
  window.addEventListener('pagehide', stop);
  window.addEventListener('pageshow', start);
  motion.addEventListener('change', () => {
    stop();
    start();
  });
}


function setupMenu() {
  const nav = document.getElementById('main-nav');
  const hamburger = document.querySelector('.hamburger');

  if (!nav || !hamburger) {
    return;
  }

  const header = document.querySelector('.nav-container');
  if (header && 'ResizeObserver' in window) {
    new ResizeObserver(() => {
      document.documentElement.style.setProperty('--navigation-height', `${header.offsetHeight}px`);
    }).observe(header);
  }

  function setMenuOpen(isOpen) {
    nav.classList.toggle('active', isOpen);
    hamburger.setAttribute('aria-expanded', String(isOpen));
    if (header) document.documentElement.style.setProperty('--navigation-height', `${header.offsetHeight}px`);
  }

  function toggleMenu() {
    setMenuOpen(!nav.classList.contains('active'));
  }

  function closeMenu() {
    setMenuOpen(false);
  }

  window.toggleMenu = toggleMenu;
  hamburger.addEventListener('click', toggleMenu);

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  window.addEventListener('scroll', () => {
    if (window.getComputedStyle(hamburger).display !== 'none') {
      closeMenu();
    }
  });

  document.addEventListener('click', (event) => {
    if (!nav.contains(event.target) && !hamburger.contains(event.target)) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenu();
      hamburger.focus();
    }
  });
}

function setupFadeIns() {
  const faders = document.querySelectorAll('.fade-in');
  if (!faders.length) {
    return;
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries, activeObserver) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) {
            return;
          }

          entry.target.classList.add('visible');
          activeObserver.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    faders.forEach((element) => observer.observe(element));
    return;
  }

  const revealOnScroll = () => {
    const triggerBottom = window.innerHeight * 0.9;

    faders.forEach((element) => {
      const top = element.getBoundingClientRect().top;
      if (top < triggerBottom) {
        element.classList.add('visible');
      }
    });
  };

  window.addEventListener('scroll', revealOnScroll);
  window.addEventListener('load', revealOnScroll, { once: true });
  revealOnScroll();
}

function setupProjectCarousel() {
  const track = document.querySelector('[data-projects-track]');
  const prevButton = document.querySelector('[data-projects-nav="prev"]');
  const nextButton = document.querySelector('[data-projects-nav="next"]');

  if (!track || !prevButton || !nextButton) {
    return;
  }

  function getStepSize() {
    const firstCard = track.querySelector('.project');
    if (!firstCard) {
      return track.clientWidth;
    }

    const gap = Number.parseFloat(window.getComputedStyle(track).gap || '0');
    return firstCard.getBoundingClientRect().width + gap;
  }

  function updateControls() {
    const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    const scrollLeft = Math.max(0, track.scrollLeft);
    const isHorizontal = window.innerWidth <= 600 && maxScroll > 0;

    prevButton.disabled = !isHorizontal || scrollLeft <= 8;
    nextButton.disabled = !isHorizontal || scrollLeft >= maxScroll - 8;
  }

  function scrollProjects(direction) {
    track.scrollBy({
      left: getStepSize() * direction,
      behavior: prefersReducedMotion ? 'auto' : 'smooth'
    });
  }

  prevButton.addEventListener('click', () => scrollProjects(-1));
  nextButton.addEventListener('click', () => scrollProjects(1));
  track.addEventListener('scroll', updateControls, { passive: true });
  window.addEventListener('resize', updateControls);
  window.addEventListener('load', updateControls, { once: true });

  updateControls();
}

function setupFlashMessages() {
  const success = document.querySelector('.form-success');
  const error = document.querySelector('.form-error');

  if (!success && !error) {
    return;
  }

  window.setTimeout(() => {
    if (success) {
      success.style.display = 'none';
    }

    if (error) {
      error.style.display = 'none';
    }
  }, 5000);
}

function setupTypewriter() {
  const typewriterEl = document.getElementById('typewriter');
  const typedLines = typeof typewriterLines !== 'undefined' && Array.isArray(typewriterLines) ? typewriterLines : [];
  if (!typewriterEl) return;
  if (prefersReducedMotion || typewriterEl.closest('.personal-terminal')) {
    typewriterEl.textContent = typedLines.join('\n');
    return;
  }
  // Reserve the server-rendered text height before revealing individual characters.
  typewriterEl.style.minHeight = `${typewriterEl.getBoundingClientRect().height}px`;
  let lineIndex = 0;
  let charIndex = 0;
  function typeLine() {
    if (lineIndex >= typedLines.length) return;
    const currentLine = typedLines[lineIndex];
    if (charIndex < currentLine.length) {
      typewriterEl.textContent += currentLine.charAt(charIndex++);
      window.setTimeout(typeLine, 20);
      return;
    }
    if (lineIndex < typedLines.length - 1) typewriterEl.textContent += '\n';
    lineIndex += 1;
    charIndex = 0;
    window.setTimeout(typeLine, 350);
  }
  typewriterEl.textContent = '';
  typeLine();
}
setupMatrix();
setupMenu();
setupFadeIns();
setupProjectCarousel();
setupFlashMessages();
setupTypewriter();

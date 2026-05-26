if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const isMobile = window.matchMedia('(max-width: 600px)').matches;

function setupMatrix() {
  const canvas = document.getElementById('matrix');
  if (!canvas || prefersReducedMotion) {
    if (canvas) {
      canvas.style.display = 'none';
    }
    return;
  }

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    return;
  }

  const letters = '01';
  const matrixConfig = isMobile
    ? {
        fontSize: 15,
        columnWidth: 24,
        trailAlpha: 0.16,
        maxTrailBoost: 0.018,
        scrollTrailFactor: 0.0035,
        depthTrailFactor: 0.007,
        baseSpeed: 0.7,
        scrollSpeedFactor: 0.22,
        depthSpeedFactor: 0.12,
        frameDelay: 74,
        frameBoostFactor: 0.12,
        easing: 0.06,
        decay: 0.9,
        maxBoost: 1.4,
        velocityFactor: 1.05,
        baseBoost: 0.06,
        resetVariance: 0.018,
        resetBoostFactor: 0.01,
        topColor: 'rgba(0, 255, 0, 0.55)',
        deepColor: 'rgba(102, 255, 224, 0.62)'
      }
    : {
        fontSize: 14,
        columnWidth: 14,
        trailAlpha: 0.055,
        maxTrailBoost: 0.03,
        scrollTrailFactor: 0.007,
        depthTrailFactor: 0.012,
        baseSpeed: 1,
        scrollSpeedFactor: 0.55,
        depthSpeedFactor: 0.25,
        frameDelay: 42,
        frameBoostFactor: 0.3,
        easing: 0.08,
        decay: 0.94,
        maxBoost: 3.2,
        velocityFactor: 2.2,
        baseBoost: 0.15,
        resetVariance: 0.04,
        resetBoostFactor: 0.02,
        topColor: 'rgba(0, 255, 0, 0.92)',
        deepColor: 'rgba(102, 255, 224, 0.95)'
      };
  const { fontSize, columnWidth } = matrixConfig;
  let columns = 0;
  let drops = [];
  let animationFrameId = null;
  let lastFrameTime = 0;
  let lastScrollY = window.scrollY;
  let lastScrollTime = performance.now();
  let scrollBoost = 0;
  let targetScrollBoost = 0;

  function resizeCanvas() {
    const previousDrops = drops;
    const previousColumns = columns;

    canvas.height = window.innerHeight;
    canvas.width = window.innerWidth;
    columns = Math.max(1, Math.floor(canvas.width / columnWidth));

    if (!previousDrops.length) {
      drops = new Array(columns).fill(1);
      return;
    }

    if (columns === previousColumns) {
      drops = previousDrops.slice(0, columns);
      return;
    }

    // Preserve existing rain positions when mobile browser chrome changes viewport size.
    drops = Array.from({ length: columns }, (_, index) => {
      const mappedIndex = Math.floor((index / Math.max(1, columns - 1)) * Math.max(0, previousColumns - 1));
      return previousDrops[mappedIndex] ?? 1;
    });
  }

  function getScrollDepth() {
    const maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    return window.scrollY / maxScroll;
  }

  function draw() {
    const scrollDepth = getScrollDepth();
    const dynamicTrail = Math.min(
      matrixConfig.maxTrailBoost,
      scrollBoost * matrixConfig.scrollTrailFactor + scrollDepth * matrixConfig.depthTrailFactor
    );

    ctx.fillStyle = `rgba(0, 0, 0, ${matrixConfig.trailAlpha + dynamicTrail})`;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = scrollDepth > 0.5 ? matrixConfig.deepColor : matrixConfig.topColor;
    ctx.font = `${fontSize}px monospace`;

    const speedMultiplier =
      matrixConfig.baseSpeed +
      scrollBoost * matrixConfig.scrollSpeedFactor +
      scrollDepth * matrixConfig.depthSpeedFactor;

    for (let index = 0; index < drops.length; index += 1) {
      const text = letters.charAt(Math.floor(Math.random() * letters.length));
      ctx.fillText(text, index * columnWidth, drops[index] * fontSize);

      if (
        drops[index] * fontSize > canvas.height &&
        Math.random() > 0.975 - Math.min(matrixConfig.resetVariance, scrollBoost * matrixConfig.resetBoostFactor)
      ) {
        drops[index] = 0;
      }

      drops[index] += speedMultiplier;
    }
  }

  function animate(timestamp) {
    if (document.hidden) {
      animationFrameId = null;
      return;
    }

    if (!lastFrameTime) {
      lastFrameTime = timestamp;
    }

    scrollBoost += (targetScrollBoost - scrollBoost) * matrixConfig.easing;
    targetScrollBoost *= matrixConfig.decay;

    const frameDelay = matrixConfig.frameDelay / (1 + scrollBoost * matrixConfig.frameBoostFactor);
    if (timestamp - lastFrameTime >= frameDelay) {
      draw();
      lastFrameTime = timestamp;
    }

    animationFrameId = window.requestAnimationFrame(animate);
  }

  function startMatrix() {
    if (animationFrameId) {
      return;
    }

    animationFrameId = window.requestAnimationFrame(animate);
  }

  function stopMatrix() {
    if (!animationFrameId) {
      return;
    }

    window.cancelAnimationFrame(animationFrameId);
    animationFrameId = null;
    lastFrameTime = 0;
  }

  window.addEventListener('scroll', () => {
    const now = performance.now();
    const deltaY = Math.abs(window.scrollY - lastScrollY);
    const deltaTime = Math.max(16, now - lastScrollTime);
    const velocity = deltaY / deltaTime;

    targetScrollBoost = Math.min(
      matrixConfig.maxBoost,
      velocity * matrixConfig.velocityFactor + matrixConfig.baseBoost
    );
    lastScrollY = window.scrollY;
    lastScrollTime = now;
  }, { passive: true });

  resizeCanvas();
  startMatrix();

  window.addEventListener('resize', resizeCanvas);
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopMatrix();
    } else {
      startMatrix();
    }
  });
}

function setupMenu() {
  const nav = document.getElementById('main-nav');
  const hamburger = document.querySelector('.hamburger');

  if (!nav || !hamburger) {
    return;
  }

  function setMenuOpen(isOpen) {
    nav.classList.toggle('active', isOpen);
    hamburger.setAttribute('aria-expanded', String(isOpen));
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
    if (window.innerWidth <= 600) {
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
      behavior: 'smooth'
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
  const preloaderEl = document.getElementById('preloader-text');
  const preloaderWrap = document.getElementById('preloader');

  const typedLines = typeof typewriterLines !== 'undefined' && Array.isArray(typewriterLines) ? typewriterLines : [];
  const preloadLines = typeof preloaderLines !== 'undefined' && Array.isArray(preloaderLines) ? preloaderLines : [];

  let lineIndex = 0;
  let charIndex = 0;
  let preloadIndex = 0;
  let preloadChar = 0;
  let typewriterStarted = false;

  function typeLine() {
    if (!typewriterEl || lineIndex >= typedLines.length) {
      return;
    }

    const currentLine = typedLines[lineIndex];

    if (charIndex < currentLine.length) {
      typewriterEl.textContent += currentLine.charAt(charIndex);
      charIndex += 1;
      window.setTimeout(typeLine, 20);
      return;
    }

    typewriterEl.textContent += '\n';
    lineIndex += 1;
    charIndex = 0;
    window.setTimeout(typeLine, 350);
  }

  function startTypewriter() {
    if (typewriterStarted) {
      return;
    }

    typewriterStarted = true;

    if (preloaderWrap) {
      preloaderWrap.style.display = 'none';
    }

    if (typewriterEl) {
      typewriterEl.textContent = '';
    }

    typeLine();
  }

  function typePreloader() {
    if (!preloaderEl || !preloaderWrap || !preloadLines.length) {
      startTypewriter();
      return;
    }

    if (preloadIndex < preloadLines.length) {
      const line = preloadLines[preloadIndex];

      if (preloadChar < line.length) {
        preloaderEl.textContent += line.charAt(preloadChar);
        preloadChar += 1;
        window.setTimeout(typePreloader, 40);
        return;
      }

      preloaderEl.textContent += '\n';
      preloadIndex += 1;
      preloadChar = 0;
      window.setTimeout(typePreloader, 250);
      return;
    }

    window.setTimeout(() => {
      if (preloaderWrap) {
        preloaderWrap.style.display = 'none';
      }
      window.scrollTo(0, 0);
      startTypewriter();
    }, 700);
  }

  function hasSeenPreloader() {
    try {
      return localStorage.getItem('pz_preloader_seen') === '1';
    } catch (error) {
      return false;
    }
  }

  function markPreloaderSeen() {
    try {
      localStorage.setItem('pz_preloader_seen', '1');
    } catch (error) {
      // ignore storage issues
    }
  }

  window.addEventListener(
    'load',
    () => {
      const urlParams = new URLSearchParams(window.location.search);
      const shouldSkipPreloader =
        urlParams.has('success') ||
        urlParams.has('error') ||
        prefersReducedMotion ||
        hasSeenPreloader();

      if (shouldSkipPreloader) {
        startTypewriter();
        return;
      }

      markPreloaderSeen();
      typePreloader();
    },
    { once: true }
  );
}

setupMatrix();
setupMenu();
setupFadeIns();
setupProjectCarousel();
setupFlashMessages();
setupTypewriter();

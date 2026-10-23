/**
 * Scroll reveals, hero entrance + parallax, and number counters.
 * Everything is skipped (content shown immediately) with prefers-reduced-motion.
 */
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
let revealObserver;

/* ---------- Scroll reveals: [data-animate] ---------- */
export function observeReveals(scope = document) {
  const selector = '[data-animate]:not(.is-visible), .reveal:not(.is-visible), .reveal-left:not(.is-visible), .reveal-right:not(.is-visible)';
  scope.querySelectorAll(selector).forEach((el) => {
    if (reduceMotion.matches || !revealObserver) el.classList.add('is-visible');
    else revealObserver.observe(el);
  });
}

export function initReveals() {
  if ('IntersectionObserver' in window) {
    revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-visible');
          revealObserver.unobserve(entry.target);
        });
      },
      { rootMargin: '0px 0px -8% 0px', threshold: 0.1 }
    );
  }
  observeReveals();
}

/* ---------- Hero: subtle portrait parallax (the entrance itself is pure CSS) ---------- */
export function initHero() {
  const hero = document.querySelector('.hero');
  const img = document.querySelector('[data-parallax] img');
  if (!hero || !img || reduceMotion.matches) return;

  const MAX_SHIFT = 12; // px
  let pointer = { x: 0, y: 0 };
  let frame = 0;

  const paint = () => {
    frame = 0;
    const scrollShift = Math.min(window.scrollY, window.innerHeight) * 0.04;
    img.style.setProperty('--px', `${pointer.x * MAX_SHIFT}px`);
    img.style.setProperty('--py', `${pointer.y * MAX_SHIFT + scrollShift}px`);
  };
  const schedule = () => {
    if (!frame) frame = requestAnimationFrame(paint);
  };

  if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
    hero.addEventListener('pointermove', (e) => {
      const rect = hero.getBoundingClientRect();
      pointer = { x: (e.clientX - rect.left) / rect.width - 0.5, y: (e.clientY - rect.top) / rect.height - 0.5 };
      schedule();
    });
    hero.addEventListener('pointerleave', () => {
      pointer = { x: 0, y: 0 };
      schedule();
    });
  }
  window.addEventListener('scroll', () => window.scrollY < window.innerHeight && schedule(), { passive: true });
}

/* ---------- Count-up numbers: [data-count] ---------- */
function countUp(el) {
  const target = Number(el.dataset.count);
  const duration = 1400;
  const start = performance.now();
  const step = (now) => {
    const p = Math.min((now - start) / duration, 1);
    el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

export function initCounters(scope = document) {
  const counters = scope.querySelectorAll('[data-count]');
  if (!counters.length || reduceMotion.matches || !('IntersectionObserver' in window)) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        countUp(entry.target);
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.6 }
  );
  counters.forEach((el) => {
    el.textContent = '0';
    observer.observe(el);
  });
}

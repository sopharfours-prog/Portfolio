/**
 * Lightweight testimonial carousel (no library):
 * arrows, dots, swipe, keyboard arrows, and autoplay that pauses on hover or focus.
 * Autoplay is off with prefers-reduced-motion.
 */
import { t } from './language.js';

const AUTOPLAY_MS = 6500;

export function initCarousel(root = document.querySelector('[data-carousel]')) {
  if (!root) return null;

  const track = root.querySelector('[data-carousel-track]');
  const dotsWrap = root.querySelector('[data-carousel-dots]');
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let index = 0;
  let timer = 0;
  let paused = false;

  const slides = () => [...track.children];

  function buildDots() {
    dotsWrap.innerHTML = slides()
      .map(
        (_, i) =>
          `<button type="button" class="carousel-x__dot" data-index="${i}" aria-label="${t('testimonials.goTo')} ${i + 1}"></button>`
      )
      .join('');
  }

  function go(i) {
    const list = slides();
    if (!list.length) return;
    index = (i + list.length) % list.length;
    track.style.transform = `translate3d(${-index * 100}%, 0, 0)`;
    list.forEach((slide, k) => {
      const hidden = k !== index;
      slide.inert = hidden;
      slide.setAttribute('aria-hidden', String(hidden));
    });
    dotsWrap.querySelectorAll('.carousel-x__dot').forEach((dot, k) => dot.setAttribute('aria-current', String(k === index)));
  }

  const stop = () => clearInterval(timer);
  const start = () => {
    stop();
    if (reduce || paused) return;
    timer = setInterval(() => go(index + 1), AUTOPLAY_MS);
  };

  // A user action stops autoplay for good and lets screen readers announce slide changes
  const userGo = (i) => {
    paused = true;
    stop();
    track.setAttribute('aria-live', 'polite');
    go(i);
  };

  root.querySelector('[data-carousel-prev]')?.addEventListener('click', () => userGo(index - 1));
  root.querySelector('[data-carousel-next]')?.addEventListener('click', () => userGo(index + 1));
  dotsWrap.addEventListener('click', (e) => {
    const dot = e.target.closest('[data-index]');
    if (dot) userGo(Number(dot.dataset.index));
  });
  root.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') userGo(index - 1);
    if (e.key === 'ArrowRight') userGo(index + 1);
  });

  // Swipe
  let startX = null;
  track.addEventListener('pointerdown', (e) => (startX = e.clientX), { passive: true });
  track.addEventListener('pointerup', (e) => {
    if (startX === null) return;
    const dx = e.clientX - startX;
    startX = null;
    if (Math.abs(dx) > 50) userGo(index + (dx < 0 ? 1 : -1));
  });

  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);
  root.addEventListener('focusin', stop);
  root.addEventListener('focusout', start);
  document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

  track.setAttribute('aria-live', 'off');
  buildDots();
  go(0);
  start();

  return {
    // After a language switch the slides are re-rendered: rebuild dots and keep position
    refresh() {
      buildDots();
      go(index);
    },
  };
}

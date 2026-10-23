/**
 * Header state, scroll progress, back-to-top, smooth anchor scrolling with
 * header offset, active section highlighting and the mobile menu.
 */
import { t } from './language.js';

const root = document.documentElement;
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
const scrollBehavior = () => (reduceMotion.matches ? 'auto' : 'smooth');

/* ---------- Scroll-driven UI (one rAF-throttled listener) ---------- */
function initScrollUI() {
  const header = document.querySelector('[data-header]');
  const backToTop = document.querySelector('.back-to-top');
  let ticking = false;

  const update = () => {
    const y = window.scrollY;
    const max = root.scrollHeight - window.innerHeight;
    root.style.setProperty('--progress', max > 0 ? Math.min(y / max, 1).toFixed(4) : '0');
    header?.classList.toggle('is-scrolled', y > 10);
    backToTop?.classList.toggle('is-visible', y > window.innerHeight * 0.6);
    ticking = false;
  };

  window.addEventListener(
    'scroll',
    () => {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    },
    { passive: true }
  );
  window.addEventListener('resize', update, { passive: true });
  update();

  document.querySelectorAll('[data-back-to-top]').forEach((btn) =>
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      window.scrollTo({ top: 0, behavior: scrollBehavior() });
      document.getElementById('main')?.focus({ preventScroll: true });
    })
  );
}

/* ---------- Smooth scrolling to in-page anchors, offset by the fixed header ---------- */
const normalizePath = (path) => path.replace(/\/index\.html$/, '/');

export function scrollToSection(target) {
  const header = document.querySelector('[data-header]');
  const offset = header ? header.offsetHeight : 0;
  const top = target.id === 'home' ? 0 : target.getBoundingClientRect().top + window.scrollY - offset + 1;
  window.scrollTo({ top, behavior: scrollBehavior() });
}

function initAnchors(closeMenu) {
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href*="#"]');
    if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey) return;

    const url = new URL(link.href, location.href);
    if (normalizePath(url.pathname) !== normalizePath(location.pathname) || !url.hash) return;

    const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
    if (!target) return;

    event.preventDefault();
    closeMenu();
    scrollToSection(target);
    history.replaceState(null, '', target.id === 'home' ? location.pathname + location.search : url.hash);
    // Move keyboard focus to the section without jumping
    if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
    target.focus({ preventScroll: true });
  });

  // Arriving from another page with a hash: correct for the fixed header once laid out
  if (location.hash) {
    const target = document.getElementById(decodeURIComponent(location.hash.slice(1)));
    if (target) window.addEventListener('load', () => scrollToSection(target), { once: true });
  }
}

/* ---------- Active navigation item ---------- */
function setActive(id, current = 'true') {
  document.querySelectorAll('[data-nav]').forEach((link) => {
    const active = link.dataset.nav === id;
    link.classList.toggle('active', active);
    if (active) link.setAttribute('aria-current', current);
    else link.removeAttribute('aria-current');
  });
}

function initActiveSection() {
  const page = document.body.dataset.page;
  if (page !== 'home') {
    setActive(page, 'page');
    return;
  }

  const navIds = new Set([...document.querySelectorAll('[data-nav]')].map((a) => a.dataset.nav));
  const sections = [...document.querySelectorAll('[data-section][id]')].filter((s) => navIds.has(s.id));

  // A section is "current" while it crosses the middle band of the viewport
  const observer = new IntersectionObserver(
    (entries) => entries.forEach((entry) => entry.isIntersecting && setActive(entry.target.id)),
    { rootMargin: '-45% 0px -50% 0px' }
  );
  sections.forEach((section) => observer.observe(section));
  setActive('home');
}

/* ---------- Mobile menu ---------- */
function initMobileMenu() {
  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-mobile-menu]');
  if (!toggle || !menu) return () => {};

  menu.querySelectorAll('.mobile-menu__links li, .mobile-menu__footer').forEach((el, i) => el.style.setProperty('--i', i));

  const isOpen = () => menu.classList.contains('is-open');

  const setLabel = (open) => {
    toggle.dataset.i18nAttr = `aria-label:nav.${open ? 'closeMenu' : 'openMenu'}`;
    toggle.setAttribute('aria-label', t(`nav.${open ? 'closeMenu' : 'openMenu'}`));
  };

  const setOpen = (open, { restoreFocus = false } = {}) => {
    if (open === isOpen()) return;
    menu.classList.toggle('is-open', open);
    menu.inert = !open;
    toggle.setAttribute('aria-expanded', String(open));
    root.classList.toggle('menu-open', open);
    setLabel(open);
    if (open) menu.querySelector('a, button')?.focus({ preventScroll: true });
    else if (restoreFocus) toggle.focus();
  };

  toggle.addEventListener('click', () => setOpen(!isOpen(), { restoreFocus: true }));

  document.addEventListener('keydown', (event) => {
    if (!isOpen()) return;
    if (event.key === 'Escape') {
      setOpen(false, { restoreFocus: true });
      return;
    }
    // Keep focus inside the toggle + menu while open
    if (event.key === 'Tab') {
      const focusables = [toggle, ...menu.querySelectorAll('a[href], button:not([disabled])')];
      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  // Menu links to other pages: close so the menu isn't open on "back"
  menu.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false);
  });

  matchMedia('(min-width: 992px)').addEventListener('change', (e) => e.matches && setOpen(false));

  return () => setOpen(false);
}

export function initNavigation() {
  const closeMenu = initMobileMenu();
  initScrollUI();
  initAnchors(closeMenu);
  initActiveSection();
}

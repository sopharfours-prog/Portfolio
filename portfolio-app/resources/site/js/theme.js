/**
 * Light / dark mode. The initial theme is set by an inline script in <head>
 * (partials/head.html) to avoid a flash; this module handles toggling and persistence.
 */
const STORAGE_KEY = 'theme';
const root = document.documentElement;
const systemDark = matchMedia('(prefers-color-scheme: dark)');

function hasStoredTheme() {
  try {
    return ['light', 'dark'].includes(localStorage.getItem(STORAGE_KEY));
  } catch {
    return false;
  }
}

function applyTheme(theme) {
  root.setAttribute('data-theme', theme);
  const bg = getComputedStyle(document.body).getPropertyValue('--bg').trim();
  document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => meta.setAttribute('content', bg));
}

export function initTheme() {
  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      applyTheme(next);
      try {
        localStorage.setItem(STORAGE_KEY, next);
      } catch {
        /* storage unavailable: theme lasts for this page only */
      }
    });
  });

  // Follow the OS setting until the visitor picks a theme explicitly
  systemDark.addEventListener('change', (e) => {
    if (!hasStoredTheme()) applyTheme(e.matches ? 'dark' : 'light');
  });
}

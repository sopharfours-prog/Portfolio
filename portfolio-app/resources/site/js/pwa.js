/**
 * Registers the service worker (public/sw.js) that makes the site an installable web app
 * with an offline page. Skipped while the Vite dev server is serving assets (hot reload).
 */
export function initPwa() {
  if (!('serviceWorker' in navigator) || document.querySelector('script[src*="/@vite/client"]')) return;

  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch((error) => {
      console.warn('[pwa] Service worker registration failed:', error);
    });
  });
}

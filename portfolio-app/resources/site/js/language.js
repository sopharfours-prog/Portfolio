/**
 * Pages are rendered server-side in the visitor's language (LA | EN links go to /lang/{code}).
 * The few strings JavaScript needs are embedded by Blade in <script id="i18n" type="application/json">.
 */
let strings = {};
try {
  strings = JSON.parse(document.getElementById('i18n')?.textContent || '{}');
} catch {
  strings = {};
}

export function t(key) {
  const value = key.split('.').reduce((node, part) => node?.[part], strings);
  return typeof value === 'string' ? value : key;
}

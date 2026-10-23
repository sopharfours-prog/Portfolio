/** Icon picker: type to filter Bootstrap Icon names, click to select. */
import { request, debounce } from './http.js';

let namesPromise;
const loadNames = (url) => (namesPromise ??= request(url));

export function initIconInputs() {
  document.querySelectorAll('[data-icon-input]').forEach((root) => {
    const input = root.querySelector('[data-icon-value]');
    const grid = root.querySelector('[data-icon-grid]');
    const preview = root.querySelector('[data-icon-preview]');
    const src = (name) => `${root.dataset.base}/${name}.svg`;

    const setPreview = (name) => (preview.innerHTML = `<img src="${src(name)}" alt="" width="18" height="18" style="filter:var(--icon-filter,none)">`);

    const show = async (all = false) => {
      const names = await loadNames(root.dataset.url);
      const term = all === true ? '' : input.value.trim().toLowerCase();
      const matches = (term ? names.filter((n) => n.includes(term)) : names).slice(0, 72);
      grid.hidden = false;
      grid.innerHTML = matches
        .map((n) => `<button type="button" data-name="${n}" title="${n}" class="${n === input.value ? 'is-active' : ''}"><img src="${src(n)}" alt="${n}" loading="lazy"></button>`)
        .join('');
      if (names.includes(term)) setPreview(term);
    };

    // Select the current name so typing replaces it; show all icons until a search is typed
    input.addEventListener('focus', () => {
      input.select();
      show(true);
    });
    input.addEventListener('input', debounce(() => show(), 200));
    grid.addEventListener('click', (event) => {
      const btn = event.target.closest('[data-name]');
      if (!btn) return;
      input.value = btn.dataset.name;
      setPreview(btn.dataset.name);
      grid.hidden = true;
    });
  });
}

/**
 * Project filter (client-side, on server-rendered cards) + detail modal.
 * The modal body is fetched from /projects/{slug}?partial=1, so the full page
 * /projects/{slug} stays the canonical, crawlable URL.
 */
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
const SWITCH_MS = 300;

function initFilters() {
  const filters = document.querySelector('[data-project-filters]');
  const grid = document.querySelector('[data-projects-grid]');
  if (!filters || !grid) return;

  const limit = Number(grid.dataset.limit) || 0;
  const cards = [...grid.querySelectorAll('.project-card')];
  const empty = grid.querySelector('.projects-empty');

  const show = (filter) => {
    let shown = 0;
    cards.forEach((card) => {
      const visible = (filter === 'all' || card.dataset.category === filter) && (!limit || shown < limit);
      card.hidden = !visible;
      if (visible) {
        // Editorial rhythm: rows alternate wide/narrow, then narrow/wide
        const wide = (shown % 2 === 0) === (Math.floor(shown / 2) % 2 === 0);
        card.classList.toggle('project-card--wide', wide);
        card.classList.toggle('project-card--narrow', !wide);
        card.classList.toggle('project-card--offset', shown % 2 === 1);
        card.style.setProperty('--i', shown);
        shown += 1;
      }
    });
    if (empty) empty.hidden = shown > 0;
    filters.querySelectorAll('[data-filter]').forEach((btn) => {
      const active = btn.dataset.filter === filter;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', String(active));
    });
    grid.classList.add('is-filtered');
    grid.classList.remove('is-switching');
  };

  filters.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-filter]');
    if (!btn || btn.classList.contains('is-active')) return;
    if (reduceMotion.matches) {
      show(btn.dataset.filter);
    } else {
      grid.classList.add('is-switching');
      setTimeout(() => show(btn.dataset.filter), SWITCH_MS);
    }
  });
}

function initModal() {
  const dialog = document.querySelector('[data-project-modal]');
  const content = dialog?.querySelector('[data-project-modal-content]');
  if (!dialog || typeof dialog.showModal !== 'function') return;

  let opener = null;
  let returnUrl = location.href;

  const open = async (url) => {
    let html;
    try {
      const response = await fetch(`${url}${url.includes('?') ? '&' : '?'}partial=1`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!response.ok) throw new Error(response.status);
      html = await response.text();
    } catch {
      window.location.href = url; // fall back to the full page
      return;
    }
    content.innerHTML = html;
    if (!dialog.open) {
      opener = document.activeElement;
      returnUrl = location.href;
      dialog.showModal();
    }
    dialog.scrollTop = 0;
    dialog.querySelector('[data-project-close]')?.focus({ preventScroll: true });
    history.replaceState(null, '', url);
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-project-open]');
    if (!trigger || event.metaKey || event.ctrlKey || event.shiftKey) return;
    event.preventDefault();
    open(trigger.getAttribute('href') || trigger.dataset.projectOpen);
  });

  dialog.querySelector('[data-project-close]')?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target !== dialog) return;
    const r = dialog.getBoundingClientRect();
    if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close();
  });
  dialog.addEventListener('close', () => {
    history.replaceState(null, '', returnUrl);
    opener?.focus?.({ preventScroll: true });
  });
}

export function initProjects() {
  initFilters();
  initModal();
}

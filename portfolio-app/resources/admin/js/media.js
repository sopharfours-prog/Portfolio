/**
 * Everything image-related:
 *  - Media picker dialog (choose from library / upload)
 *  - Single image inputs
 *  - Project gallery (drag & drop upload, reorder, set cover, remove)
 *  - Media Library page (drop zone, details drawer, copy URL, rename, delete)
 */
import { request, debounce, escapeHtml, t } from './http.js';
import { toast } from './ui.js';

const ACCEPT = /\.(jpe?g|png|webp|svg)$/i;

const BATCH_BYTES = 95 * 1024 * 1024; // stay under the server's per-request limit (post_max_size)

async function upload(url, files, folder = 'uploads') {
  const valid = [...files].filter((f) => ACCEPT.test(f.name));
  if (!valid.length) throw new Error(t('error'));

  // Group files so each request carries at most ~95 MB; a single file up to 100 MB gets its own request
  const batches = [];
  valid.forEach((file) => {
    const last = batches[batches.length - 1];
    if (last && last.bytes + file.size <= BATCH_BYTES) {
      last.files.push(file);
      last.bytes += file.size;
    } else {
      batches.push({ files: [file], bytes: file.size });
    }
  });

  const results = [];
  for (const batch of batches) {
    const body = new FormData();
    batch.files.forEach((file) => body.append('files[]', file));
    body.append('folder', folder);
    results.push(await request(url, { method: 'POST', body }));
  }

  return { message: results.map((r) => r.message).join(' · '), data: results.flatMap((r) => r.data) };
}

/* ---------- Picker ---------- */
let picker;

function createPicker() {
  const dialog = document.querySelector('[data-media-picker]');
  if (!dialog) return null;
  const grid = dialog.querySelector('[data-picker-grid]');
  const confirm = dialog.querySelector('[data-picker-confirm]');
  const more = dialog.querySelector('[data-picker-more]');
  const search = dialog.querySelector('[data-picker-search]');
  let items = new Map();
  let selected = new Set();
  let multiple = false;
  let resolve = null;
  let nextUrl = null;

  const card = (m) => `<button type="button" class="media-card ${selected.has(m.id) ? 'is-selected' : ''}" data-id="${m.id}">
      <span class="media-card__img ${m.mime === 'image/svg+xml' ? 'media-card__img--contain' : ''}"><img src="${escapeHtml(m.url)}" alt="" loading="lazy"></span>
      <span class="media-card__body"><span class="media-card__name truncate" style="display:block">${escapeHtml(m.name)}</span>
      <span class="media-card__meta">${escapeHtml(m.dimensions ?? '')} · ${escapeHtml(m.size)}</span></span></button>`;

  const load = async (url, append = false) => {
    try {
      const data = await request(url);
      if (!append) {
        items = new Map();
        grid.innerHTML = '';
      }
      data.data.forEach((m) => items.set(m.id, m));
      grid.insertAdjacentHTML('beforeend', data.data.map(card).join(''));
      if (!items.size) grid.innerHTML = `<p class="empty" style="grid-column:1/-1">${escapeHtml(t('noResults'))}</p>`;
      nextUrl = data.next;
      more.hidden = !nextUrl;
    } catch (error) {
      toast(error.message, 'error');
    }
  };

  const baseUrl = () => `${dialog.dataset.url}?search=${encodeURIComponent(search.value.trim())}`;

  grid.addEventListener('click', (event) => {
    const el = event.target.closest('[data-id]');
    if (!el) return;
    const id = Number(el.dataset.id);
    if (!multiple) {
      selected.clear();
      grid.querySelectorAll('.is-selected').forEach((c) => c.classList.remove('is-selected'));
    }
    if (selected.has(id)) selected.delete(id);
    else selected.add(id);
    el.classList.toggle('is-selected', selected.has(id));
    confirm.disabled = selected.size === 0;
  });
  grid.addEventListener('dblclick', () => !confirm.disabled && confirm.click());

  search.addEventListener('input', debounce(() => load(baseUrl()), 350));
  more.addEventListener('click', () => nextUrl && load(nextUrl, true));

  dialog.querySelector('[data-picker-upload]').addEventListener('change', async (event) => {
    try {
      const data = await upload(dialog.dataset.upload, event.target.files, dialog.dataset.folder);
      toast(data.message);
      data.data.forEach((m) => selected.add(m.id));
      if (!multiple && data.data.length) selected = new Set([data.data[0].id]);
      await load(baseUrl());
      confirm.disabled = selected.size === 0;
    } catch (error) {
      toast(error.message, 'error');
    }
    event.target.value = '';
  });

  const finish = (result) => {
    dialog.close();
    resolve?.(result);
    resolve = null;
  };
  confirm.addEventListener('click', () => finish([...selected].map((id) => items.get(id)).filter(Boolean)));
  dialog.querySelectorAll('[data-picker-close]').forEach((b) => b.addEventListener('click', () => finish([])));
  dialog.addEventListener('cancel', () => resolve?.([]));

  return {
    /** Opens the library and resolves with the chosen media items ([] if cancelled). */
    open({ multiple: many = false, folder = 'uploads' } = {}) {
      multiple = many;
      selected = new Set();
      confirm.disabled = true;
      search.value = '';
      dialog.dataset.folder = folder;
      dialog.showModal();
      load(baseUrl());
      return new Promise((r) => (resolve = r));
    },
  };
}

/* ---------- Single image input ---------- */
function initImageInputs() {
  document.querySelectorAll('[data-image-input]').forEach((root) => {
    const value = root.querySelector('[data-image-value]');
    const preview = root.querySelector('[data-image-preview]');
    const remove = root.querySelector('[data-image-remove]');
    const empty = preview.innerHTML;

    const set = (media) => {
      value.value = media ? media.path : '';
      preview.innerHTML = media ? `<img src="${escapeHtml(media.url)}" alt="">` : root.dataset.emptyIcon || empty;
      remove.hidden = !media;
      value.dispatchEvent(new CustomEvent('image:change', { bubbles: true, detail: media }));
    };
    if (!value.value) root.dataset.emptyIcon = empty;

    root.querySelector('[data-image-choose]').addEventListener('click', async () => {
      const [media] = await picker.open({ folder: 'site' });
      if (media) set(media);
    });
    remove.addEventListener('click', () => {
      preview.innerHTML = '<svg class="icon" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/></svg>';
      value.value = '';
      remove.hidden = true;
      value.dispatchEvent(new CustomEvent('image:change', { bubbles: true, detail: null }));
    });
  });
}

/* ---------- Project gallery ---------- */
async function initGallery() {
  const root = document.querySelector('[data-gallery]');
  if (!root) return;
  const list = root.querySelector('[data-gallery-list]');
  const zone = root.querySelector('[data-gallery-drop]');
  const fileInput = root.querySelector('[data-gallery-file]');
  const coverInput = root.querySelector('[data-gallery-cover]');
  let items = JSON.parse(root.dataset.items || '[]');
  let cover = items.find((i) => i.cover)?.id ?? items[0]?.id ?? null;

  const render = () => {
    if (!items.some((i) => i.id === cover)) cover = items[0]?.id ?? null;
    coverInput.value = cover ?? '';
    list.innerHTML = items
      .map(
        (item) => `<div class="gallery__item ${item.id === cover ? 'is-cover' : ''}" data-id="${item.id}">
          <input type="hidden" name="gallery[]" value="${item.id}">
          <img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.name)}" loading="lazy">
          ${item.id === cover ? `<span class="gallery__cover">${escapeHtml(t('cover'))}</span>` : ''}
          <span class="gallery__actions">
            ${item.id === cover ? '' : `<button type="button" class="gallery__btn" data-set-cover title="${escapeHtml(t('setCover'))}" aria-label="${escapeHtml(t('setCover'))}">★</button>`}
            <button type="button" class="gallery__btn" data-remove title="${escapeHtml(t('remove'))}" aria-label="${escapeHtml(t('remove'))}">✕</button>
          </span></div>`
      )
      .join('');
  };

  const add = (media) => {
    media.forEach((m) => {
      if (!items.some((i) => i.id === m.id)) items.push({ id: m.id, url: m.url, name: m.name });
    });
    render();
  };

  const handleFiles = async (files) => {
    zone.classList.add('is-busy');
    try {
      const data = await upload(root.dataset.upload, files, 'projects');
      add(data.data);
      toast(data.message);
    } catch (error) {
      toast(error.message, 'error');
    }
    zone.classList.remove('is-busy');
  };

  list.addEventListener('click', (event) => {
    const el = event.target.closest('[data-id]');
    if (!el) return;
    const id = Number(el.dataset.id);
    if (event.target.closest('[data-set-cover]')) cover = id;
    else if (event.target.closest('[data-remove]')) items = items.filter((i) => i.id !== id);
    else return;
    render();
  });

  zone.addEventListener('click', () => fileInput.click());
  zone.addEventListener('keydown', (e) => (e.key === 'Enter' || e.key === ' ') && (e.preventDefault(), fileInput.click()));
  fileInput.addEventListener('change', () => {
    handleFiles(fileInput.files);
    fileInput.value = '';
  });
  ['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (e) => (e.preventDefault(), zone.classList.add('is-over'))));
  ['dragleave', 'drop'].forEach((type) => zone.addEventListener(type, (e) => (e.preventDefault(), zone.classList.remove('is-over'))));
  zone.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));

  root.querySelector('[data-gallery-library]')?.addEventListener('click', async () => add(await picker.open({ multiple: true, folder: 'projects' })));

  const { default: Sortable } = await import('sortablejs');
  Sortable.create(list, {
    animation: 180,
    ghostClass: 'is-dragging',
    onEnd: () => {
      const order = [...list.querySelectorAll('[data-id]')].map((el) => Number(el.dataset.id));
      items.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
    },
  });
  render();
}

/* ---------- Media Library page ---------- */
function initLibrary() {
  const page = document.querySelector('[data-media-library]');
  if (!page) return;
  const zone = page.querySelector('[data-library-drop]');
  const fileInput = page.querySelector('[data-library-file]');
  const drawer = document.querySelector('[data-media-drawer]');

  const handleFiles = async (files) => {
    zone.classList.add('is-busy');
    try {
      const data = await upload(page.dataset.upload, files);
      toast(data.message);
      setTimeout(() => window.location.reload(), 600);
    } catch (error) {
      toast(error.message, 'error');
      zone.classList.remove('is-busy');
    }
  };
  zone.addEventListener('click', () => fileInput.click());
  zone.addEventListener('keydown', (e) => (e.key === 'Enter' || e.key === ' ') && (e.preventDefault(), fileInput.click()));
  fileInput.addEventListener('change', () => handleFiles(fileInput.files));
  ['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (e) => (e.preventDefault(), zone.classList.add('is-over'))));
  ['dragleave', 'drop'].forEach((type) => zone.addEventListener(type, (e) => (e.preventDefault(), zone.classList.remove('is-over'))));
  zone.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));

  // Details drawer
  page.addEventListener('click', (event) => {
    const card = event.target.closest('[data-media]');
    if (!card) return;
    const m = JSON.parse(card.dataset.media);
    drawer.querySelector('[data-d-preview]').style.backgroundImage = `url("${m.url}")`;
    drawer.querySelector('[data-d-form]').action = m.update_url;
    drawer.querySelector('[data-d-delete]').action = m.update_url;
    drawer.querySelector('[data-d-delete]').dataset.confirmText = m.name;
    drawer.querySelector('[data-d-name]').value = m.name;
    drawer.querySelector('[data-d-alt]').value = m.alt ?? '';
    drawer.querySelector('[data-d-url]').value = m.url;
    drawer.querySelector('[data-d-copy]').dataset.copy = new URL(m.url, location.origin).href;
    const info = { file: m.original_name, type: m.mime, size: m.size, dimensions: m.dimensions ?? '—', uploaded: m.uploaded, by: m.uploader ?? '—' };
    Object.entries(info).forEach(([key, value]) => (drawer.querySelector(`[data-d-${key}]`).textContent = value));
    drawer.showModal();
  });
  drawer?.querySelectorAll('[data-drawer-close]').forEach((b) => b.addEventListener('click', () => drawer.close()));
  drawer?.addEventListener('click', (e) => e.target === drawer && drawer.close());
}

export function initMedia() {
  picker = createPicker();
  initImageInputs();
  initGallery();
  initLibrary();
}

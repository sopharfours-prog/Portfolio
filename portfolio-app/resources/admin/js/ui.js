/** Shell behaviour: sidebar drawer, dropdowns, theme, toasts, confirm dialog, filters, bilingual tabs. */
import { debounce } from './http.js';

/* ---------- Toasts (auto-dismiss after 4s) ---------- */
export function toast(message, type = 'success') {
  const host = document.querySelector('[data-toasts]');
  if (!host || !message) return;
  const el = document.createElement('div');
  el.className = `toast${type === 'error' ? ' toast--error' : ''}`;
  el.setAttribute('role', type === 'error' ? 'alert' : 'status');
  el.textContent = `${type === 'error' ? '!' : '✓'}  ${message}`;
  host.append(el);
  setTimeout(() => {
    el.classList.add('is-leaving');
    setTimeout(() => el.remove(), 320);
  }, 4000);
}

function initFlash() {
  const flash = document.querySelector('[data-toasts]')?.dataset.flash;
  if (!flash) return;
  try {
    const { type, message } = JSON.parse(flash);
    toast(message, type);
  } catch {
    /* ignore malformed flash data */
  }
}

/* ---------- Sidebar: drawer on mobile ---------- */
function initSidebar() {
  const root = document.documentElement;
  const toggle = document.querySelector('[data-sidebar-toggle]');
  if (!toggle) return;
  const set = (open) => {
    root.classList.toggle('sidebar-open', open);
    toggle.setAttribute('aria-expanded', String(open));
  };
  toggle.addEventListener('click', () => set(!root.classList.contains('sidebar-open')));
  document.querySelectorAll('[data-sidebar-close]').forEach((el) => el.addEventListener('click', () => set(false)));
  document.addEventListener('keydown', (e) => e.key === 'Escape' && set(false));
}

/* ---------- Sidebar groups: fold / unfold, remembered in this browser ---------- */
function initNavGroups() {
  const groups = [...document.querySelectorAll('[data-nav-group]')];
  if (!groups.length) return;
  const KEY = 'admin.navGroups.collapsed';
  const iconOnly = matchMedia('(min-width: 768px) and (max-width: 1199.98px)');
  let collapsed = [];
  try {
    collapsed = JSON.parse(localStorage.getItem(KEY) || '[]');
  } catch {
    collapsed = [];
  }

  const apply = (group, isCollapsed) => {
    group.classList.toggle('is-collapsed', isCollapsed);
    group.querySelector('[data-nav-group-toggle]').setAttribute('aria-expanded', String(!isCollapsed));
    // Hidden links must not be reachable by Tab, except in the icon-only sidebar where groups always show
    group.querySelector('.nav-group__items').inert = isCollapsed && !iconOnly.matches;
  };
  const save = () => {
    try {
      localStorage.setItem(KEY, JSON.stringify(collapsed));
    } catch {
      /* private mode: state just isn't remembered */
    }
  };

  groups.forEach((group) => {
    const key = group.dataset.navGroup;
    // The group holding the current page always opens
    if (group.hasAttribute('data-has-active')) collapsed = collapsed.filter((k) => k !== key);
    apply(group, collapsed.includes(key));

    group.querySelector('[data-nav-group-toggle]').addEventListener('click', () => {
      const isCollapsed = !group.classList.contains('is-collapsed');
      collapsed = isCollapsed ? [...new Set([...collapsed, key])] : collapsed.filter((k) => k !== key);
      apply(group, isCollapsed);
      save();
    });
  });
  iconOnly.addEventListener('change', () => groups.forEach((g) => apply(g, g.classList.contains('is-collapsed'))));
}

/* ---------- Dropdowns ---------- */
function initDropdowns() {
  const closeAll = (except) =>
    document.querySelectorAll('[data-dropdown].is-open').forEach((d) => {
      if (d !== except) {
        d.classList.remove('is-open');
        d.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
      }
    });

  document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-dropdown-toggle]');
    if (toggle) {
      const dropdown = toggle.closest('[data-dropdown]');
      const open = !dropdown.classList.contains('is-open');
      closeAll(dropdown);
      dropdown.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      if (open) dropdown.dispatchEvent(new CustomEvent('dropdown:open'));
      return;
    }
    if (!event.target.closest('[data-dropdown]')) closeAll();
  });
  document.addEventListener('keydown', (e) => e.key === 'Escape' && closeAll());
}

/* ---------- Theme: light → dark → system (saved in localStorage) ---------- */
function initTheme() {
  const root = document.documentElement;
  const system = matchMedia('(prefers-color-scheme: dark)');
  const KEY = 'admin-theme';
  const order = ['light', 'dark', 'system'];

  const apply = (mode) => {
    root.setAttribute('data-theme-mode', mode);
    root.setAttribute('data-theme', mode === 'dark' || (mode === 'system' && system.matches) ? 'dark' : 'light');
    document.querySelectorAll('[data-theme-icon]').forEach((el) => (el.hidden = el.dataset.themeIcon !== mode));
    document.querySelectorAll('[data-theme-choice]').forEach((el) => (el.checked = el.value === mode));
    document.dispatchEvent(new CustomEvent('theme:change'));
  };
  const save = (mode) => {
    try {
      localStorage.setItem(KEY, mode);
    } catch {
      /* storage unavailable */
    }
    apply(mode);
  };

  apply(root.getAttribute('data-theme-mode') || 'system');
  document.querySelector('[data-theme-cycle]')?.addEventListener('click', () => {
    const current = root.getAttribute('data-theme-mode') || 'system';
    save(order[(order.indexOf(current) + 1) % order.length]);
  });
  document.querySelectorAll('[data-theme-choice]').forEach((el) => el.addEventListener('change', () => save(el.value)));
  system.addEventListener('change', () => apply(root.getAttribute('data-theme-mode') || 'system'));
}

/* ---------- Confirmation dialog: <form data-confirm="Title" data-confirm-text="..."> ---------- */
function initConfirm() {
  const modal = document.querySelector('[data-confirm-modal]');
  if (!modal) return;
  let pending = null;

  document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (!form || form.dataset.confirmed) return;
    event.preventDefault();
    pending = form;
    modal.querySelector('[data-confirm-title]').textContent = form.dataset.confirm;
    modal.querySelector('[data-confirm-text]').textContent = form.dataset.confirmText || '';
    modal.querySelector('[data-confirm-ok]').textContent = form.dataset.confirmButton || modal.querySelector('[data-confirm-ok]').textContent;
    modal.showModal();
    modal.querySelector('[data-confirm-cancel]').focus();
  });
  modal.querySelector('[data-confirm-cancel]').addEventListener('click', () => modal.close());
  modal.querySelector('[data-confirm-ok]').addEventListener('click', () => {
    modal.close();
    if (pending) {
      pending.dataset.confirmed = '1';
      pending.requestSubmit();
    }
  });
}

/* ---------- Filter bars: selects submit at once, search is debounced ---------- */
function initFilters() {
  document.querySelectorAll('form[data-filters]').forEach((form) => {
    const submit = () => form.requestSubmit();
    form.querySelectorAll('select').forEach((el) => el.addEventListener('change', submit));
    const search = form.querySelector('input[type="search"]');
    if (!search) return;
    search.addEventListener('input', debounce(submit, 450));
    // Keep typing position after the page reloads with results
    if (search.value && new URLSearchParams(location.search).has('search')) {
      search.focus();
      search.setSelectionRange(search.value.length, search.value.length);
    }
  });
}

/* ---------- Bilingual fields: LA | EN tabs ---------- */
function initBilingual() {
  document.addEventListener('click', (event) => {
    const tab = event.target.closest('[data-bilingual] [data-lang]');
    if (!tab) return;
    const field = tab.closest('[data-bilingual]');
    field.querySelectorAll('[data-lang]').forEach((b) => {
      const active = b === tab;
      b.classList.toggle('is-active', active);
      b.setAttribute('aria-selected', String(active));
    });
    field.querySelectorAll('[data-lang-panel]').forEach((p) => (p.hidden = p.dataset.langPanel !== tab.dataset.lang));
  });

  // "Switch all" control: one click flips every bilingual field on the page
  document.querySelectorAll('[data-lang-all]').forEach((btn) =>
    btn.addEventListener('click', () => {
      document.querySelectorAll(`[data-bilingual] [data-lang="${btn.dataset.langAll}"]`).forEach((tab) => tab.click());
      document.querySelectorAll('[data-lang-all]').forEach((b) => b.classList.toggle('is-active', b === btn));
    })
  );

  // Open the language that has a validation error
  document.querySelectorAll('[data-bilingual] .has-error').forEach((tab) => tab.click());
}

/* ---------- Small form helpers ---------- */
function initFormHelpers() {
  // Slug follows the title until edited by hand
  const title = document.querySelector('[data-slug-source]');
  const slug = document.querySelector('[data-slug]');
  if (title && slug) {
    let manual = Boolean(slug.value);
    slug.addEventListener('input', () => (manual = Boolean(slug.value)));
    title.addEventListener('input', () => {
      if (manual) return;
      slug.value = title.value.toLowerCase().trim().replace(/[^a-z0-9຀-໿]+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/^-+|-+$/g, '');
    });
  }

  // "Current position" disables the end date
  const current = document.querySelector('[data-current-toggle]');
  const end = document.querySelector('[data-end-date]');
  if (current && end) {
    const sync = () => {
      end.disabled = current.checked;
      if (current.checked) end.value = '';
    };
    current.addEventListener('change', sync);
    sync();
  }

  // Copy buttons
  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-copy]');
    if (!btn) return;
    try {
      await navigator.clipboard.writeText(btn.dataset.copy);
      toast(btn.dataset.copied || 'Copied');
    } catch {
      toast(btn.dataset.copy, 'error');
    }
  });

  // File drop areas ([data-file-drop]): show the chosen file name; drag & drop fills the input
  document.querySelectorAll('[data-file-drop]').forEach((drop) => {
    const input = drop.querySelector('[data-file-input]');
    const label = drop.querySelector('[data-file-label]');
    const sync = () => {
      let file = input.files[0];
      // Too large for the server: clear it now rather than fail after uploading
      if (file && input.dataset.maxBytes && file.size > Number(input.dataset.maxBytes)) {
        toast(input.dataset.tooLarge, 'error');
        input.value = '';
        file = null;
      }
      label.textContent = file ? `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)` : label.dataset.empty;
      drop.classList.toggle('has-file', Boolean(file));
    };
    input.addEventListener('change', sync);
    drop.addEventListener('dragover', (event) => {
      event.preventDefault();
      drop.classList.add('is-over');
    });
    drop.addEventListener('dragleave', () => drop.classList.remove('is-over'));
    drop.addEventListener('drop', (event) => {
      event.preventDefault();
      drop.classList.remove('is-over');
      if (event.dataTransfer.files.length) {
        input.files = event.dataTransfer.files;
        sync();
      }
    });
  });

  // Settings → Appearance: colour pickers update their hex value and the button preview live
  const preview = document.querySelector('[data-color-preview]');
  document.querySelectorAll('[data-color-input]').forEach((input) => {
    const key = input.dataset.colorInput;
    input.addEventListener('input', () => {
      document.querySelector(`[data-color-value="${key}"]`).textContent = input.value;
      preview?.style.setProperty({ primary: '--p', secondary: '--s', tertiary: '--t' }[key], input.value);
    });
  });

  // Settings → Email: fill in the server settings of a common provider
  document.querySelectorAll('[data-mail-preset]').forEach((button) => {
    button.addEventListener('click', () => {
      const form = button.closest('form');
      const presets = { gmail: { host: 'smtp.gmail.com', port: '587', encryption: 'tls' } };
      Object.entries(presets[button.dataset.mailPreset] || {}).forEach(([name, value]) => {
        if (form.elements[name]) form.elements[name].value = value;
      });
      const smtp = form.querySelector('input[name="mode"][value="smtp"]');
      if (smtp) smtp.checked = true;
      form.elements.username?.focus();
    });
  });

  // Tab rows: keep the selected tab in view and mark rows that have more tabs off to the right
  document.querySelectorAll('.tabs').forEach((tabs) => {
    const sync = () => tabs.classList.toggle('has-more', tabs.scrollLeft + tabs.clientWidth < tabs.scrollWidth - 2);
    // Scroll only the row sideways (scrollIntoView would also scroll the page)
    const active = tabs.querySelector('.is-active');
    if (active && tabs.scrollWidth > tabs.clientWidth) {
      tabs.scrollLeft = active.offsetLeft - (tabs.clientWidth - active.offsetWidth) / 2;
    }
    tabs.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync, { passive: true });
    sync();
  });

  // Prevent double submits
  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented || form.dataset.noLock !== undefined) return;
    setTimeout(() => form.querySelectorAll('button[type="submit"]').forEach((b) => (b.disabled = true)), 0);
  });
}

export function initUI() {
  initFlash();
  initSidebar();
  initNavGroups();
  initDropdowns();
  initTheme();
  initConfirm();
  initFilters();
  initBilingual();
  initFormHelpers();
}

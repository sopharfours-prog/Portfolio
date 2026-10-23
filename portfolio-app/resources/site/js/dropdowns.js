/**
 * Header dropdowns (language picker, account menu), password reveal buttons
 * and the sign-in / sign-out toast.
 */

/* ---------- Dropdowns: [data-dropdown] > [data-dropdown-toggle] + [data-dropdown-panel] ---------- */
export function initDropdowns() {
  const dropdowns = [...document.querySelectorAll('[data-dropdown]')];
  if (!dropdowns.length) return;

  const items = (dropdown) => [...dropdown.querySelectorAll('[data-dropdown-panel] a[href], [data-dropdown-panel] button')];
  const toggleOf = (dropdown) => dropdown.querySelector('[data-dropdown-toggle]');

  const setOpen = (dropdown, open, { focus = null } = {}) => {
    dropdown.classList.toggle('is-open', open);
    toggleOf(dropdown).setAttribute('aria-expanded', String(open));
    if (open && focus === 'first') {
      const list = items(dropdown);
      (list.find((el) => el.getAttribute('aria-current') === 'true') || list[0])?.focus();
    } else if (!open && focus === 'toggle') {
      toggleOf(dropdown).focus();
    }
  };

  const closeAll = (except) => dropdowns.forEach((d) => d !== except && d.classList.contains('is-open') && setOpen(d, false));

  dropdowns.forEach((dropdown) => {
    const toggle = toggleOf(dropdown);

    toggle.addEventListener('click', () => {
      const open = !dropdown.classList.contains('is-open');
      closeAll(dropdown);
      setOpen(dropdown, open);
    });

    toggle.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        closeAll(dropdown);
        setOpen(dropdown, true, { focus: 'first' });
      }
    });

    dropdown.addEventListener('keydown', (event) => {
      if (!dropdown.classList.contains('is-open')) return;
      const list = items(dropdown);
      const index = list.indexOf(document.activeElement);

      if (event.key === 'Escape') {
        event.preventDefault();
        setOpen(dropdown, false, { focus: 'toggle' });
      } else if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && index !== -1) {
        event.preventDefault();
        const step = event.key === 'ArrowDown' ? 1 : -1;
        list[(index + step + list.length) % list.length].focus();
      }
    });

    // Tabbing out of the menu closes it
    dropdown.addEventListener('focusout', (event) => {
      if (!dropdown.contains(event.relatedTarget)) setOpen(dropdown, false);
    });
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-dropdown]')) closeAll();
  });
}

/* ---------- Show / hide password ---------- */
export function initPasswordToggles() {
  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = button.parentElement.querySelector('input');
    button.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-pressed', String(show));
      input.focus();
    });
  });
}

/* ---------- Toast: slides in, leaves after a few seconds or on click ---------- */
export function initToast() {
  const toast = document.querySelector('[data-toast]');
  if (!toast) return;
  const hide = () => toast.classList.add('is-leaving');
  const timer = setTimeout(hide, 4500);
  toast.addEventListener('click', () => {
    clearTimeout(timer);
    hide();
  });
  toast.addEventListener('animationend', (event) => event.animationName === 'toast-out' && toast.remove());
}

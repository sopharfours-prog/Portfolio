/** Notification dropdown: refresh on open, mark as read, mark all, clear. */
import { request, t, escapeHtml } from './http.js';

export function initNotifications() {
  const root = document.querySelector('[data-notifications]');
  if (!root) return;
  const list = root.querySelector('[data-notif-list]');
  const dot = root.querySelector('[data-notif-dot]');

  const render = ({ items, unread }) => {
    dot.hidden = unread === 0;
    list.innerHTML = items.length
      ? items
          .map(
            (n) => `<button type="button" class="notif__item ${n.read ? 'is-read' : ''}" data-notif-read data-id="${n.id}" data-url="${escapeHtml(n.url ?? '')}">
              <span class="notif__dot"></span>
              <span><span class="notif__title">${escapeHtml(n.title)}</span>
              ${n.body ? `<span class="notif__body truncate" style="display:block">${escapeHtml(n.body)}</span>` : ''}
              <span class="notif__time" style="display:block">${escapeHtml(n.time)}</span></span></button>`
          )
          .join('')
      : `<p class="empty" style="padding:32px 16px">${escapeHtml(t('noNotifications'))}</p>`;
  };

  const refresh = async () => {
    try {
      render(await request(root.dataset.url));
    } catch {
      /* keep the server-rendered list */
    }
  };

  root.addEventListener('dropdown:open', refresh);

  root.addEventListener('click', async (event) => {
    const item = event.target.closest('[data-notif-read]');
    const action = event.target.closest('[data-notif-action]');
    try {
      if (item) {
        const url = item.dataset.id ? `${root.dataset.url}/${item.dataset.id}/read` : item.dataset.notifRead;
        await request(url, { method: 'POST' });
        if (item.dataset.url) window.location.href = item.dataset.url;
        else refresh();
      } else if (action) {
        await request(action.dataset.notifAction, { method: 'POST' });
        refresh();
      }
    } catch {
      /* non-critical */
    }
  });
}

/** Drag & drop ordering: <tbody data-sortable="/admin/reorder/skills" data-start="0"> with [data-id] rows. */
import { request } from './http.js';
import { toast } from './ui.js';

export async function initSortable() {
  const lists = document.querySelectorAll('[data-sortable]');
  if (!lists.length) return;
  const { default: Sortable } = await import('sortablejs');

  lists.forEach((list) => {
    Sortable.create(list, {
      handle: '[data-drag]',
      animation: 180,
      ghostClass: 'is-dragging',
      onEnd: async ({ oldIndex, newIndex }) => {
        if (oldIndex === newIndex) return;
        const ids = [...list.querySelectorAll('[data-id]')].map((row) => Number(row.dataset.id));
        try {
          const data = await request(list.dataset.sortable, { method: 'POST', body: { ids, start: Number(list.dataset.start || 0) } });
          toast(data.message);
        } catch (error) {
          toast(error.message, 'error');
        }
      },
    });
  });
}

/** Live preview on the About editor: mirrors form fields into a mini version of the section. */
export function initPreview() {
  const preview = document.querySelector('[data-preview]');
  if (!preview) return;
  const form = document.querySelector('[data-preview-form]');
  let lang = 'lo';

  const value = (name) => form.querySelector(`[name="${name}[${lang}]"]`)?.value || form.querySelector(`[name="${name}[lo]"]`)?.value || '';

  const render = () => {
    preview.querySelectorAll('[data-p]').forEach((el) => (el.textContent = value(el.dataset.p)));
    const stats = [...form.querySelectorAll('[data-stat-row]')]
      .map((row) => ({
        value: row.querySelector('[data-stat-value]').value,
        suffix: row.querySelector('[data-stat-suffix]').value,
        label: row.querySelector(`[data-stat-label="${lang}"]`).value || row.querySelector('[data-stat-label="lo"]').value,
      }))
      .filter((s) => s.value && s.label);
    const host = preview.querySelector('[data-p-stats]');
    host.replaceChildren(
      ...stats.map((s) => {
        const wrap = document.createElement('div');
        const strong = document.createElement('strong');
        const span = document.createElement('span');
        strong.textContent = `${s.value}${s.suffix}`;
        span.textContent = s.label;
        wrap.append(strong, span);
        return wrap;
      })
    );
    host.hidden = stats.length === 0;
  };

  form.addEventListener('input', render);
  form.addEventListener('image:change', (event) => {
    const img = preview.querySelector('[data-p-image]');
    img.hidden = !event.detail;
    if (event.detail) img.src = event.detail.url;
  });
  preview.querySelectorAll('[data-preview-lang]').forEach((btn) =>
    btn.addEventListener('click', () => {
      lang = btn.dataset.previewLang;
      preview.querySelectorAll('[data-preview-lang]').forEach((b) => b.classList.toggle('is-active', b === btn));
      render();
    })
  );
  render();
}

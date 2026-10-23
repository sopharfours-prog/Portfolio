/** fetch() wrapper: CSRF header, JSON in/out, readable errors. */
const token = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let strings = {};
try {
  strings = JSON.parse(document.getElementById('admin-i18n')?.textContent || '{}');
} catch {
  strings = {};
}
export const t = (key) => strings[key] ?? key;

export async function request(url, { method = 'GET', body, headers = {} } = {}) {
  const isForm = body instanceof FormData;
  const response = await fetch(url, {
    method,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': token(),
      ...(body && !isForm ? { 'Content-Type': 'application/json' } : {}),
      ...headers,
    },
    body: body && !isForm ? JSON.stringify(body) : body,
  });

  const data = response.status === 204 ? null : await response.json().catch(() => null);
  if (!response.ok) {
    const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
    throw new Error(firstError || data?.message || t('error'));
  }
  return data;
}

export const debounce = (fn, wait = 350) => {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  };
};

export const escapeHtml = (value) =>
  String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

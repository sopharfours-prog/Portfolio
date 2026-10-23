/**
 * Contact form: client-side validation (messages embedded by Blade), then a JSON
 * POST to Laravel, which stores the message and notifies the admins.
 * Without JavaScript the form still posts normally.
 */
import { t } from './language.js';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

const rules = {
  name: (v) => (!v ? 'contact.nameRequired' : ''),
  email: (v) => (!v ? 'contact.emailRequired' : !EMAIL_RE.test(v) ? 'contact.emailInvalid' : ''),
  subject: (v) => (!v ? 'contact.subjectRequired' : ''),
  message: (v) => (!v ? 'contact.messageRequired' : v.length < 10 ? 'contact.messageShort' : ''),
};

export function initContactForm() {
  const form = document.querySelector('[data-contact-form]');
  if (!form) return;

  const status = form.querySelector('[data-form-status]');
  const submit = form.querySelector('[data-submit]');
  const submitLabel = form.querySelector('[data-submit-label]');
  const fields = Object.keys(rules).map((name) => form.elements[name]);

  const showError = (field, text) => {
    const error = document.getElementById(`${field.id}-error`);
    field.setAttribute('aria-invalid', String(Boolean(text)));
    if (error) error.textContent = text;
  };
  const validate = (field) => {
    const key = rules[field.name](field.value.trim());
    showError(field, key ? t(key) : '');
    return !key;
  };
  const setStatus = (text, type = '') => {
    status.textContent = text;
    status.className = `form-status${type ? ` is-${type}` : ''}`;
  };

  fields.forEach((field) => {
    field.addEventListener('blur', () => field.value.trim() && validate(field));
    field.addEventListener('input', () => field.getAttribute('aria-invalid') === 'true' && validate(field));
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    setStatus('');
    const invalid = fields.filter((field) => !validate(field));
    if (invalid.length) {
      invalid[0].focus();
      return;
    }

    submit.disabled = true;
    submitLabel.textContent = t('contact.sending');
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
      });
      const data = await response.json().catch(() => ({}));
      if (response.status === 422 && data.errors) {
        Object.entries(data.errors).forEach(([name, messages]) => form.elements[name] && showError(form.elements[name], messages[0]));
        return;
      }
      if (!response.ok) throw new Error(response.status);
      form.reset();
      fields.forEach((f) => showError(f, ''));
      setStatus(data.message || t('contact.success'), 'success');
    } catch (error) {
      console.warn('[contact] Sending failed:', error);
      setStatus(t('contact.failed'), 'error');
    } finally {
      submit.disabled = false;
      submitLabel.textContent = t('contact.send');
    }
  });
}

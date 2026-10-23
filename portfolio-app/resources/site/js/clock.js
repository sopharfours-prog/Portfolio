/** Live clock in the footer ([data-clock]): the visitor's local time, in the page language, every second. */
export function initClock() {
  const clock = document.querySelector('[data-clock]');
  if (!clock) return;

  const locale = document.documentElement.lang === 'lo' ? 'lo-LA' : 'en-US';
  let format;
  try {
    format = new Intl.DateTimeFormat(locale, { hour: 'numeric', minute: '2-digit', second: '2-digit' });
  } catch {
    format = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
  }

  const tick = () => {
    const now = new Date();
    clock.textContent = format.format(now);
    clock.dateTime = now.toISOString();
  };
  tick();
  // Align updates to the start of each second
  setTimeout(() => {
    tick();
    setInterval(tick, 1000);
  }, 1000 - (Date.now() % 1000));
}

/**
 * Service cards: the description is clamped to a few lines with the feature list hidden;
 * "Read more" unfolds both. The button only appears when there is more to show.
 */
export function initServices() {
  document.querySelectorAll('[data-service]').forEach((card) => {
    const more = card.querySelector('[data-service-more]');
    const button = card.querySelector('[data-service-toggle]');
    if (!more || !button) return;

    card.classList.add('is-collapsible');
    const desc = more.querySelector('.service-card__desc');
    const hasMore = desc.scrollHeight > desc.clientHeight + 2 || more.querySelector('.service-card__list');
    if (!hasMore) {
      card.classList.remove('is-collapsible');
      return;
    }
    button.hidden = false;

    button.addEventListener('click', () => {
      const open = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', String(open));
      card.classList.toggle('is-open', open);
      button.querySelector('[data-label-more]').hidden = open;
      button.querySelector('[data-label-less]').hidden = !open;
    });
  });
}

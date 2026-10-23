/** Dashboard charts (Chart.js, loaded only on pages that have a chart). */
import { request } from './http.js';

const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
const format = (n) => new Intl.NumberFormat().format(n);

export async function initCharts() {
  const overview = document.querySelector('[data-overview]');
  const sparks = document.querySelectorAll('[data-spark]');
  if (!overview && !sparks.length) return;

  const { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip } = await import('chart.js');
  Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip);
  Chart.defaults.font.family = css('--font') || 'Inter, sans-serif';
  const charts = [];

  // Sparklines in stat cards
  sparks.forEach((canvas) => {
    // Hidden on small screens by CSS; Chart.js would force it visible again
    if (getComputedStyle(canvas).display === 'none') return;
    const values = JSON.parse(canvas.dataset.spark);
    charts.push(
      new Chart(canvas, {
        type: 'line',
        data: { labels: values.map((_, i) => i), datasets: [{ data: values, borderColor: css('--text'), borderWidth: 1.5, pointRadius: 0, tension: 0.4 }] },
        options: { responsive: false, animation: false, plugins: { tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } },
      })
    );
  });

  if (overview) {
    const canvas = overview.querySelector('canvas');
    const metrics = [...overview.querySelectorAll('[data-metric]')];
    const ranges = [...overview.querySelectorAll('[data-range]')];
    let metric = 'visitors';
    let range = 7;
    let data = null;

    const gradient = () => {
      const g = canvas.getContext('2d').createLinearGradient(0, 0, 0, canvas.height || 300);
      const dark = document.documentElement.dataset.theme === 'dark';
      g.addColorStop(0, dark ? 'rgba(255,255,255,0.16)' : 'rgba(0,0,0,0.10)');
      g.addColorStop(1, 'rgba(128,128,128,0)');
      return g;
    };

    const chart = new Chart(canvas, {
      type: 'line',
      data: { labels: [], datasets: [{ data: [], fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 5 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { tooltip: { displayColors: false, padding: 10, callbacks: { label: (ctx) => format(ctx.parsed.y) } } },
        scales: {
          x: { grid: { display: false }, border: { display: false }, ticks: { maxTicksLimit: 8, maxRotation: 0 } },
          y: { beginAtZero: true, border: { display: false }, ticks: { maxTicksLimit: 5, precision: 0 } },
        },
      },
    });
    charts.push(chart);

    const paint = () => {
      if (!data) return;
      const text = css('--text');
      const dataset = chart.data.datasets[0];
      chart.data.labels = data.series.labels;
      dataset.data = data.series[metric];
      dataset.borderColor = text;
      dataset.pointHoverBackgroundColor = text;
      dataset.backgroundColor = gradient();
      chart.options.scales.x.ticks.color = chart.options.scales.y.ticks.color = css('--muted');
      chart.options.scales.y.grid.color = css('--border');
      chart.options.plugins.tooltip.backgroundColor = text;
      chart.options.plugins.tooltip.titleColor = chart.options.plugins.tooltip.bodyColor = css('--bg');
      chart.update();

      metrics.forEach((el) => {
        const key = el.dataset.metric;
        el.classList.toggle('is-active', key === metric);
        el.setAttribute('aria-pressed', String(key === metric));
        el.querySelector('[data-value]').textContent = format(data.totals.current[key]);
        const delta = el.querySelector('[data-delta]');
        const growth = data.totals.growth[key];
        delta.textContent = growth === null ? '—' : `${growth > 0 ? '+' : ''}${growth}%`;
        delta.className = `metric__delta ${growth > 0 ? 'is-up' : growth < 0 ? 'is-down' : ''}`;
      });
    };

    const load = async () => {
      try {
        data = await request(`${overview.dataset.overview}?range=${range}`);
        paint();
      } catch {
        /* chart stays empty */
      }
    };

    metrics.forEach((el) => el.addEventListener('click', () => ((metric = el.dataset.metric), paint())));
    ranges.forEach((el) =>
      el.addEventListener('click', () => {
        range = Number(el.dataset.range);
        ranges.forEach((r) => r.classList.toggle('is-active', r === el));
        load();
      })
    );
    document.addEventListener('theme:change', paint);
    load();
  }

  document.addEventListener('theme:change', () =>
    charts.filter((c) => c.canvas.hasAttribute('data-spark')).forEach((c) => {
      c.data.datasets[0].borderColor = css('--text');
      c.update();
    })
  );
}

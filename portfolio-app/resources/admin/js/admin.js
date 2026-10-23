/** Admin entry point. Each module is a no-op on pages without its elements. */
import '../css/admin.css';
import { initUI } from './ui.js';
import { initNotifications } from './notifications.js';
import { initSortable } from './sortable.js';
import { initMedia } from './media.js';
import { initIconInputs } from './icons.js';
import { initCharts } from './charts.js';
import { initPreview } from './preview.js';

initUI();
initNotifications();
initSortable();
initMedia();
initIconInputs();
initCharts();
initPreview();

/** Public site entry point. Every module checks that its elements exist. */
import '../css/style.css';
import '../css/animations.css';
import '../css/responsive.css';
import { initTheme } from './theme.js';
import { initNavigation } from './navigation.js';
import { initReveals, initHero, initCounters } from './animations.js';
import { initProjects } from './projects.js';
import { initCarousel } from './testimonials.js';
import { initContactForm } from './contact.js';
import { initDropdowns, initPasswordToggles, initToast } from './dropdowns.js';
import { initPwa } from './pwa.js';
import { initServices } from './services.js';
import { initClock } from './clock.js';

initTheme();
initNavigation();
initReveals();
initHero();
initCounters();
initProjects();
initCarousel();
initContactForm();
initDropdowns();
initPasswordToggles();
initToast();
initPwa();
initServices();
initClock();

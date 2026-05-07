import './bootstrap';

import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.bootstrap5.css';

import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

window.Alpine = Alpine;
window.L = L;
window.TomSelect = TomSelect;

window.sidebarLayout = function sidebarLayout() {
	return {
		sidebarCollapsed: false,
		mobileOpen: false,
		isDark: false,

		init() {
			this.sidebarCollapsed = localStorage.getItem('steg.sidebar.collapsed') === '1';
			this.isDark = localStorage.getItem('steg.theme') === 'dark';
			this.applyTheme();

			window.addEventListener('keydown', (event) => {
				if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
					event.preventDefault();
					this.toggleSidebar();
				}
			});
		},

		toggleSidebar() {
			this.sidebarCollapsed = !this.sidebarCollapsed;
			localStorage.setItem('steg.sidebar.collapsed', this.sidebarCollapsed ? '1' : '0');
		},

		toggleTheme() {
			this.isDark = !this.isDark;
			localStorage.setItem('steg.theme', this.isDark ? 'dark' : 'light');
			this.applyTheme();
		},

		applyTheme() {
			document.documentElement.classList.toggle('theme-dark', this.isDark);
		},
	};
};

L.Icon.Default.mergeOptions({
	iconRetinaUrl,
	iconUrl,
	shadowUrl,
});

// Initialize Tom Select on all data-tom-select elements
document.addEventListener('DOMContentLoaded', function() {
	const tomSelectElements = document.querySelectorAll('[data-tom-select]');
	tomSelectElements.forEach(el => {
		new TomSelect(el, {
			create: false,
			placeholder: el.getAttribute('data-placeholder') || 'Select an option...',
			searchField: ['text'],
			maxOptions: null
		});
	});
});

Alpine.start();

import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Debug logging
console.log('Debug - VITE_APP_NAME:', import.meta.env.VITE_APP_NAME);
console.log('Debug - Final appName:', appName);
console.log('Debug - All Vite env vars:', import.meta.env);

createInertiaApp({
    title: (title) => {
        const finalTitle = `${title} - ${appName}`;
        console.log('Debug - Title function called with:', { title, appName, finalTitle });
        return finalTitle;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Core vendor libraries
                    'vendor-core': ['vue', '@inertiajs/vue3'],
                    // Rich text editor (only loaded when needed)
                    'editor': [
                        '@tiptap/vue-3', 
                        '@tiptap/starter-kit', 
                        '@tiptap/extension-link',
                        '@tiptap/extension-text-align'
                    ],
                    // UI components
                    'ui': ['@headlessui/vue', '@heroicons/vue'],
                    // Utilities
                    'utils': ['lodash', 'axios'],
                },
            },
        },
        // Optimize chunk size warnings
        chunkSizeWarningLimit: 1000,
        // Enable source maps for debugging in production
        sourcemap: process.env.NODE_ENV === 'development',
        // Minification options
        minify: 'esbuild',
        target: 'es2015',
        // CSS code splitting
        cssCodeSplit: true,
    },
    // Performance optimizations
    optimizeDeps: {
        include: [
            'vue',
            '@inertiajs/vue3',
            '@headlessui/vue',
            '@heroicons/vue',
        ],
        exclude: [
            // Exclude heavy libraries that should be lazy loaded
            '@tiptap/vue-3',
            '@tiptap/starter-kit',
            '@tiptap/extension-link',
            '@tiptap/extension-text-align',
            'firebase',
        ],
    },
    // Development server optimizations
    server: {
        fs: {
            strict: false,
        },
        // Enable HMR for faster development
        hmr: {
            overlay: true,
        },
    },
});

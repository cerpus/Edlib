import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import manifestSRI from 'vite-plugin-manifest-sri';

export default defineConfig({
    server: {
        host: true,
        port: 80,
        strictPort: true,
        hmr: {
            host: 'hub-vite-hmr.edlib.test',
            clientPort: '443',
            protocol: 'wss',
        },
        watch: {
            ignored: (file) => {
                return !(
                    file.endsWith('/hub') ||
                    file.endsWith('/resources') ||
                    file.endsWith('/resources/js') ||
                    file.endsWith('/resources/css') ||
                    file.endsWith('vite.config.js') ||
                    file.includes('/resources/css/') ||
                    file.includes('/resources/js/')
                );
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',
                'resources/js/app.js',
                'resources/js/swagger.js',
            ],
            refresh: true,
        }),
        manifestSRI(),
    ],
    css: {
        devSourcemap: true,
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
            },
        },
    },
});

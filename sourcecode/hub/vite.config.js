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
    },
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
        manifestSRI(),
    ],
    css: {
        devSourcemap: true,
    },
});

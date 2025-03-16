import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/admin/theme.css'],
            refresh: true,
        }),
    ],
    server: {
        host: 'ddosp.pictodev', // Allows access from network (use 'localhost' for local only)
        port: 3001, // Change this to your desired port
    },
});

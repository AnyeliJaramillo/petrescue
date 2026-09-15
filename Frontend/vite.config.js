import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    envDir: '../Backend',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            publicDirectory: '../Backend/public',
            hotFile: '../Backend/public/hot',
            refresh: ['resources/views/**', '../Backend/app/Http/**', '../Backend/routes/**'],
        }),
        tailwindcss(),
    ],
    build: {
        // Vite publica únicamente sus recursos en el directorio público de Laravel.
        emptyOutDir: true,
    },
    server: {
        watch: {
            ignored: ['**/Backend/storage/framework/views/**'],
        },
    },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // The React admin panel and its login page. Built files go to public/build (deploy that folder with the app).
            input: ['resources/admin/main.tsx', 'resources/admin/login.tsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: { '@admin': '/resources/admin' },
    },
});

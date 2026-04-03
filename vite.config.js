import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    resolve: {
        dedupe: ['@twilio/voice-sdk'],
    },
    plugins: [
        laravel({
            input: [
                    'resources/css/app.css', 
                    'resources/js/app.js'
                ],
            refresh: true,
        }),  
        tailwindcss(),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions'],
            },
        },
    },
});

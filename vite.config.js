import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js', 'resources/css/theme.css', 
                'resources/css/login.css','resources/js/login.js',
                'resources/js/accounts.js',
                'resources/js/import.js',
                'resources/js/entry.js',
                'resources/js/journal.js',
                'resources/js/trial-balance.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

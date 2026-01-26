import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { glob } from 'glob';

const cssFiles = await glob('resources/css/**/*.css');
const jsFiles = await glob('resources/js/**/*.js');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...cssFiles,
                ...jsFiles,
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});

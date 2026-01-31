import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
    ],
    build: {
        lib: {
            entry: 'resources/js/cp.js',
            name: 'FaviconGenerator',
            formats: ['umd'],
            fileName: (format) => `js/favicon-generator.js`
        },
        rollupOptions: {
            external: ['vue'],
            output: {
                globals: {
                    vue: 'Vue'
                }
            }
        },
        outDir: 'dist',
        emptyOutDir: true,
    }
});

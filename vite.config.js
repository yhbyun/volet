import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        vue(),
    ],
    build: {
        outDir: 'resources/dist',
        rollupOptions: {
            input: resolve(__dirname, 'resources/js/volet.js'), // JS만 빌드
            output: {
                entryFileNames: 'volet-app.js',
                format: 'iife',
                name: 'VoletApp',
            }
        }
    }
});

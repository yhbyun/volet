import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        outDir: 'resources/dist-tmp',
        rollupOptions: {
            input: resolve(__dirname, 'resources/css/volet.css'),
            output: {
                assetFileNames: 'volet-default[extname]'
            }
        }
    }
});

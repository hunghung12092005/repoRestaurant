import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'resources/css/app.css'],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        // Thay bằng địa chỉ server Laravel của bạn
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
  },
});
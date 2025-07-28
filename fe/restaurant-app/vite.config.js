import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
//news
  server: {
    proxy: {
      '/api': {
        // Thay bằng địa chỉ server Laravel của bạn
        target: 'http://127.0.0.1:8000', 
        changeOrigin: true,
      },
    },
  },
})


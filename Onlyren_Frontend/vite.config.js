// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
  alias: {
      '@': '/src', // Make sure this points to the correct directory
    },
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // Your Laravel backend
        changeOrigin: true,
        secure: false,
      }
    }
  }
})
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
    host: true, 
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8081', // Your Laravel backend
        changeOrigin: true,
        secure: false,
      }
      
    },
    strictPort: true,
    watch: {
      usePolling: true // helps with file changes inside Docker on Windows/macOS

    }
  }
})
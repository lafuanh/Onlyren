import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'), // Ensure this points to the correct directory
    },
  },
  server: {
    host: true, // Use true to allow the server to be accessible from any IP
    port: 3000, // You can change this if you want to use a different port
    proxy: {
      '/api': {
        target: 'https://onlyren.noupal.pro', // Your Laravel backend
        changeOrigin: true,  // This will handle CORS issues by modifying the Origin header
        secure: false, // Disable SSL verification for local development if necessary
        rewrite: (path) => path.replace(/^\/api/, '') // Rewrite /api prefix before forwarding the request
      }
    },
    strictPort: true, // Ensures the server uses the exact port specified
    watch: {
      usePolling: true, // Helps with file changes inside Docker on Windows/macOS
    }
  }
})

// src/main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { setupAxiosInterceptors } from './api/auth'

// Import Tailwind CSS
import './assets/tailwind.css'

// Create Vue app
const app = createApp(App)

// Setup Pinia for state management
const pinia = createPinia()
app.use(pinia)

// Setup router
app.use(router)

// Setup Axios interceptors
setupAxiosInterceptors()

// Mount the app
app.mount('#app')
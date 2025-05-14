<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

// Reactive state to check if the user is logged in
const isAuthenticated = ref(false)
const router = useRouter()

// Check authentication status (you can use a token or session)
onMounted(() => {
  const token = localStorage.getItem('token')  // Check if token exists in localStorage
  isAuthenticated.value = !!token  // If a token exists, the user is authenticated
})

// Logout function to remove the token and redirect to login
const logout = () => {
  localStorage.removeItem('token') // Remove token
  isAuthenticated.value = false // Update the login state
  router.push('/login') // Redirect to the login page
}
const search = () => {
  router.push('/search') // Redirect to the login page
}
</script>

<template>
  <!-- Header -->
  <header class="bg-white shadow-sm">
    <div class="container mx-auto px-8 py-4 flex justify-between items-center">
      <div class="flex items-center">
        <router-link to="/" class="flex items-center">
          <div class="flex items-center justify-center">
            <img src="@/assets/images/ss.jpg" alt="Workspace" class="w-[55px] h-[55px] py-2 px-2 object-cover" />
            <div class="flex-grow text-center">
              <span class="text-3xl font-bold text-gray-800">ONLYREN</span>
            </div>
          </div>
        </router-link>
      </div>
        <div class="bg-white rounded-lg shadow-md flex items-center p-2">
                    <input 
                        type="text" 
                        placeholder="Cari space " 
                        class="flex-grow w-[300px] px-2 py-2 text-sm focus:outline-none" 
                    />
                    <button @click="search" class="bg-orange-500 text-white p-2 rounded-lg" >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
        </div>
        <div class="flex items-center space-x-4">
        <router-link to="/messages" class="text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </router-link>

        <!-- Conditionally render Login or User Profile link -->
        <template v-if="!isAuthenticated">
          <router-link to="/login" class="bg-white border border-gray-300 rounded px-6 py-2 text-sm">
            Masuk
          </router-link>
        </template>
        
        <template v-else>
          <router-link to="/user" class="text-sm text-blue-600">
            User Profile
          </router-link>
          <button @click="logout" class="bg-red-500 text-white rounded px-6 py-2 text-sm">
            Logout
          </button>
        </template>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* Add custom styles here if needed */
</style>

<script setup>
import OnlyFooter from '@/components/OnlyFooter.vue';

import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getCurrentUser, logout } from '@/api/auth'

const router = useRouter()
const route = useRoute()

// Reactive state
const isAuthenticated = ref(false)
const user = ref(null)
const searchQuery = ref('')
const isLoading = ref(false)
const showDropdown = ref(false)
const showLogoutModal = ref(false)
const loggingOut = ref(false)
const dropdownRef = ref(null)

// Initialize search query from route and check auth
onMounted(async () => {
  if (route.query.query) {
    searchQuery.value = route.query.query
  }
  
  // Check authentication status on component mount
  await checkAuthStatus()
  
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Watch for route changes to update search query
watch(() => route.query.query, (newQuery) => {
  if (newQuery) {
    searchQuery.value = newQuery
  }
})

// Check authentication status
const checkAuthStatus = async () => {
  try {
    const currentUser = await getCurrentUser()
    if (currentUser) {
      isAuthenticated.value = true
      user.value = currentUser
    } else {
      isAuthenticated.value = false
      user.value = null
    }
  } catch (error) {
    console.error('Error checking auth status:', error)
    isAuthenticated.value = false
    user.value = null
  }
}

// Get user initials for avatar
const getUserInitials = () => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Get profile route based on user role
const getProfileRoute = () => {
  if (!user.value) return '/profile'
  if (user.value.is_admin) return '/admin/dashboard'
  if (user.value.is_renter) return '/renter/profile'
  return '/profile'
}

// Get profile label based on user role
const getProfileLabel = () => {
  if (!user.value) return 'My Profile'
  if (user.value.is_admin) return 'Admin Profile'
  if (user.value.is_renter) return 'Renter Profile'
  return 'My Profile'
}

// Handle search
const handleSearch = () => {
  if (!searchQuery.value.trim()) return
  
  if (route.name !== 'Search') {
    router.push({ 
      name: 'Search', 
      query: { query: searchQuery.value.trim() } 
    })
  } else {
    // Emit search event or trigger search on current page
    router.replace({ 
      name: 'Search', 
      query: { query: searchQuery.value.trim() } 
    })
  }
}

// Handle location click
const handleLocationClick = (location) => {
  searchQuery.value = location.toLowerCase()
  handleSearch()
}

// Dropdown functionality
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const closeDropdown = () => {
  showDropdown.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

// Logout functionality
const handleLogout = () => {
  showDropdown.value = false
  showLogoutModal.value = true
}

const cancelLogout = () => {
  showLogoutModal.value = false
}

const confirmLogout = async () => {
  loggingOut.value = true
  
  try {
    // Call logout API
    await logout()
    
    // Clear local storage and session storage
    localStorage.clear()
    sessionStorage.clear()
    
    // Update state
    isAuthenticated.value = false
    user.value = null
    showLogoutModal.value = false
    
    // Show success message (optional)
    console.log('Logged out successfully')
    
    // Redirect to login page
    router.push('/login')
    
  } catch (error) {
    console.error('Logout error:', error)
    
    // Even if API fails, clear local data and redirect
    localStorage.clear()
    sessionStorage.clear()
    isAuthenticated.value = false
    user.value = null
    showLogoutModal.value = false
    
    router.push('/login')
  } finally {
    loggingOut.value = false
  }
}

// Expose methods for parent components if needed
defineExpose({
  checkAuthStatus,
  refreshUser: checkAuthStatus
})
</script>

<style scoped>
.font-koulen {
  font-family: 'Koulen', cursive;
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Location card hover effect */
.location-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.location-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}
</style>

<template>
  <div class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-8 my-2 pt-2 flex justify-between items-center">
        <div class="flex items-center">
          <router-link to="/" class="flex items-center">
            <div class="flex items-center justify-center">
                 <img src="../assets/images/ss.jpg" alt="Workspace" class="w-[55px] h-[55px] object-cover " />
            </div>
          </router-link>
        </div>
        <!-- Title in the center -->
        <div class="flex-grow text-center">
          <span class="text-6xl font-koulen text-gray-800">ONLYREN</span>
        </div>
       
      <!-- Right side navigation -->
      <div class="flex items-center space-x-4">
        <!-- Messages link (only show when authenticated) -->
        <router-link 
          v-if="isAuthenticated" 
          to="/messages" 
          class="text-gray-600 hover:text-orange-500 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </router-link>

        <!-- Unauthenticated state -->
        <template v-if="!isAuthenticated">
          <router-link 
            to="/login" 
            class="bg-white border border-gray-300 rounded px-6 py-2 text-sm hover:bg-gray-50 transition-colors"
          >
            Masuk
          </router-link>
        </template>
        
        <!-- Authenticated state -->
        <template v-else>
          <!-- User dropdown menu -->
          <div class="relative" ref="dropdownRef">
            <button 
              @click="toggleDropdown"
              class="flex items-center space-x-2 text-gray-700 hover:text-orange-500 focus:outline-none"
            >
              <!-- User avatar -->
              <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                {{ getUserInitials() }}
              </div>
              <span class="text-sm font-medium">{{ user?.name || 'User' }}</span>
              <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Dropdown menu -->
            <div 
              v-if="showDropdown"
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
            >
              <!-- Profile link based on user role -->
              <router-link 
                :to="getProfileRoute()"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                @click="closeDropdown"
              >
                <i class="fas fa-user mr-2"></i>
                {{ getProfileLabel() }}
              </router-link>
              
              <!-- Role-specific links -->
              <template v-if="user?.is_admin">
                <router-link 
                  to="/admin/dashboard"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  @click="closeDropdown"
                >
                  <i class="fas fa-tachometer-alt mr-2"></i>
                  Admin Dashboard
                </router-link>
              </template>
              
              <template v-else-if="user?.is_renter">
                <router-link 
                  to="/renter/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  @click="closeDropdown"
                >
                  <i class="fas fa-building mr-2"></i>
                  Renter Dashboard
                </router-link>
              </template>
              
              <template v-else>
                <router-link 
                  to="/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                  @click="closeDropdown"
                >
                  <i class="fas fa-calendar-alt mr-2"></i>
                  My Reservations
                </router-link>
              </template>

              <hr class="my-1">
              
              <!-- Logout button -->
              <button 
                @click="handleLogout"
                :disabled="loggingOut"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50"
              >
                <i class="fas fa-sign-out-alt mr-2"></i>
                {{ loggingOut ? 'Logging out...' : 'Logout' }}
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div 
      v-if="showLogoutModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="cancelLogout"
    >
      <div 
        class="bg-white rounded-lg p-6 max-w-sm w-full mx-4"
        @click.stop
      >
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-sign-out-alt text-red-600"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">Confirm Logout</h3>
        </div>
        
        <p class="text-gray-600 mb-6">
          Are you sure you want to logout? You will need to login again to access your account.
        </p>
        
        <div class="flex space-x-3">
          <button 
            @click="cancelLogout"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="confirmLogout"
            :disabled="loggingOut"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ loggingOut ? 'Logging out...' : 'Logout' }}
          </button>
        </div>
      </div>
    </div>
    </header>

    <!-- Hero section with search -->
    <section class="bg-white">
      <div class="mx-0 px-0 py-4">
        <div class="relative">
          <img src="@/assets/images/workspace-hero.jpg" alt="Workspace" class="w-full h-128 object-cover " />
          <div class="absolute inset-0 bg-black bg-opacity-10 rounded-lg"></div>
          <div class="absolute top-1/2 left-1/3 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md px-4">
            <!-- Slogan -->
            <div class="text-left mb-6">
              <h1 class="text-black text-xl font-extralight drop-shadow-lg">
                Dari Freelancer hingga Startup – Ruang untuk Semua
              </h1>
            </div>
            <!-- Search Box -->
            <div class="bg-white rounded-lg shadow-md flex items-center p-2">
                <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari space" 
            class="flex-grow w-[300px] px-2 py-2 text-sm focus:outline-none" 
                  :disabled="isLoading"
                  @keyup.enter="handleSearch"
          />
          <button @click="handleSearch" class="bg-orange-500 text-white p-2 rounded-lg hover:bg-orange-600 transition-colors" :disabled="isLoading">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
          </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lokasi Populer Section -->
    <section class="bg-white py-8">
      <div class="container mx-auto px-8">
        <h2 class="text-xl italic font-extralight mb-6 text-center">Lokasi <b>Populer!</b></h2>
        <div class="grid grid-cols-4 gap-4 mb-6">
          <!-- Semarang -->
          <div 
            class="relative rounded-lg overflow-hidden h-96 location-card"
            @click="handleLocationClick('Semarang')"
          >
            <img src="../assets/images/semarang.png" alt="Semarang" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30 hover:bg-opacity-40 transition-all duration-300"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Semarang</h3>
            </div>
          </div>
          
          <!-- Jogja -->
          <div 
            class="relative rounded-lg overflow-hidden h-96 location-card"
            @click="handleLocationClick('Jogja')"
          >
            <img src="../assets/images/jogja.png" alt="Jogja" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30 hover:bg-opacity-40 transition-all duration-300"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Jogja</h3>
            </div>
          </div>
          
          <!-- Jakarta -->
          <div 
            class="relative rounded-lg overflow-hidden h-96 location-card"
            @click="handleLocationClick('Jakarta')"
          >
            <img src="../assets/images/jakarta.png" alt="Jakarta" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30 hover:bg-opacity-40 transition-all duration-300"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Jakarta</h3>
            </div>
          </div>
          
          <!-- Bandung -->
          <div 
            class="relative rounded-lg overflow-hidden h-96 location-card"
            @click="handleLocationClick('Bandung')"
          >
            <img src="../assets/images/bandung.png" alt="Bandung" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-40 hover:bg-opacity-40 transition-all duration-300"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Bandung</h3>
            </div>
          </div>
        </div>
        
        <div class="flex justify-center">
          <button 
            class="text-gray-600 text-sm underline hover:text-gray-800 transition-colors"
            @click="handleLocationClick('Indonesia')"
          >
            Lihat semua
          </button>
        </div>
      </div>
    </section>

    <!-- Info Cards Section -->
    <section class="bg-gray-50 py-12">
      <div class="container mx-auto px-8">
        <div class="grid grid-cols-2 gap-8">
          <!-- Left Card - Co-Working Space -->
          <div class="bg-blue-100 rounded-lg p-8 flex items-center">
            <div class="flex-1">
              <h3 class="text-lg font-sans mb-2">
                Kelola <span class="text-blue-600">Co-Working Space</span> lebih <span class="text-blue-600">Mudah, Cepat</span>, 
                dan <span class="text-blue-600">Teorganisir</span>. Bangun pengalaman terbaik 
                untuk penyewa dan pengelola - semua dalam satu platform
              </h3>
            </div>
            <div class="ml-6">
              <div class="w-20 h-20 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Right Card - Platform Features -->
          <div class="bg-orange-100 rounded-lg p-8 flex items-center">
            <div class="mr-6">
              <div class="w-20 h-20 bg-orange-500 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-sans">
                <span class="text-orange-600">Platform all-in-one</span> untuk mengelola reservasi, 
                ruang kerja, pembayaran, dan fasilitas tambahan. 
                Cocok untuk <span class="text-orange-600">freelancer, startup, dan tim kerja</span> 
                yang dinamis.
              </h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
      <OnlyFooter />
  </div>
</template>
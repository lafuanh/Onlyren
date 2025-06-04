<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import RoomCard from '@/components/RoomCard.vue'
import { fetchRooms } from '@/api/room'
import OnlyFooter from '@/components/OnlyFooter.vue'
import OnlyHeader from '@/components/OnlyHeader.vue'

// Search and filter state
const searchQuery = ref('')
const rooms = ref([])
const filters = ref({
  type: '',
  priceRange: [0, 1000000],
  amenities: [],
  period: 'Harian' // Default to daily
})

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const totalResults = ref(0)

// Loading and error states
const isLoading = ref(false)
const error = ref(null)

// Auth state (you'll need to implement proper auth)
const isAuthenticated = ref(false)

// Router
const router = useRouter()
const route = useRoute()

// Computed property for display results count
const displayResultsCount = computed(() => {
  return rooms.value.length
})

// Search and filter methods
const performSearch = async () => {
  if (!searchQuery.value || searchQuery.value.trim() === '') {
    // If search is empty, just return without making API call
    return
  }
  
  isLoading.value = true
  error.value = null
  
  try {
    const searchParams = {
      query: searchQuery.value.trim(),
      type: filters.value.type,
      priceRange: filters.value.priceRange,
      amenities: filters.value.amenities,
      period: filters.value.period,
      page: currentPage.value,
      per_page: 12
    }
    
    const response = await fetchRooms(searchParams)
    
    // Handle response safely
    if (response && response.data) {
      rooms.value = response.data
      totalPages.value = response.meta?.last_page || 1
      totalResults.value = response.meta?.total || response.data.length
    } else {
      rooms.value = []
      totalPages.value = 1
      totalResults.value = 0
    }
    
  } catch (err) {
    console.error('Search error:', err)
    error.value = err.message || 'Failed to fetch rooms'
    rooms.value = []
  } finally {
    isLoading.value = false
  }
}

// Filter methods
const applyFilters = async () => {
  currentPage.value = 1
  await performSearch()
}

const resetFilters = async () => {
  filters.value = {
    type: '',
    priceRange: [0, 1000000],
    amenities: [],
    period: 'Harian'
  }
  currentPage.value = 1
  await performSearch()
}

// Pagination
const changePage = async (page) => {
  if (page !== currentPage.value && page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    await performSearch()
  }
}

// Navigate to search page or perform search
const handleSearch = async () => {
  if (!searchQuery.value || searchQuery.value.trim() === '') {
    return
  }

  // Reset to first page when doing new search
  currentPage.value = 1
  
  if (route.name !== 'Search') {
    // Navigate to search page with query
    await router.push({ 
      name: 'Search', 
      query: { query: searchQuery.value.trim() } 
    })
  } else {
    // Perform search if already on search page
    if (!isLoading.value) {
      await performSearch()
    }
  }
}

// Navigate to room detail
const goToRoomDetail = (roomId) => {
  if (roomId) {
    router.push({ name: 'RoomDetail', params: { id: roomId } })
  }
}

// Handle Enter key on search input
const handleSearchKeydown = (event) => {
  if (event.key === 'Enter') {
    handleSearch()
  }
}

// Auth methods (implement based on your auth system)
const logout = () => {
  // Clear auth token
  localStorage.removeItem('auth_token')
  sessionStorage.removeItem('auth_token')
  isAuthenticated.value = false
  
  // Redirect to home or login
  router.push('/')
}

// Check authentication status
const checkAuth = () => {
  const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
  isAuthenticated.value = !!token
}

// Price formatting helper
const formatPrice = (price) => {
  if (!price) return '50.000'
  return new Intl.NumberFormat('id-ID').format(price)
}

// Initial load
onMounted(async () => {
  checkAuth()
  
  // If there's a query in URL, perform search
  if (route.query.query) {
    searchQuery.value = route.query.query
    await performSearch()
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-8 py-4 flex justify-between items-center">
        <div class="flex items-center">
          <router-link to="/" class="flex items-center">
            <div class="flex items-center justify-center">
              <img 
                src="@/assets/images/ss.jpg" 
                alt="Workspace" 
                class="w-[55px] h-[55px] py-2 px-2 object-cover" 
              />
              <div class="flex-grow text-center">
                <span class="text-3xl font-koulen text-gray-800">ONLYREN</span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- Search Bar Section -->
        <div class="bg-white rounded-lg shadow-md flex items-center p-2">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari space" 
            class="flex-grow w-[300px] px-2 py-2 text-sm focus:outline-none" 
            :disabled="isLoading"
            @keydown="handleSearchKeydown"
          />
          <button 
            @click="handleSearch" 
            class="bg-orange-500 text-white p-2 rounded-lg hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" 
            :disabled="isLoading || !searchQuery.trim()"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-5 w-5" 
              viewBox="0 0 20 20" 
              fill="currentColor"
            >
              <path 
                fill-rule="evenodd" 
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" 
                clip-rule="evenodd" 
              />
            </svg>
          </button>
        </div>

        <!-- User Profile and Messages Section -->
        <div class="flex items-center space-x-4">
          <router-link to="/messages" class="text-gray-600 hover:text-gray-800 transition-colors">
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-12 w-12" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" 
              />
            </svg>
          </router-link>

          <!-- Conditionally render Login or User Profile link -->
          <template v-if="!isAuthenticated">
            <router-link 
              to="/login" 
              class="bg-white border border-gray-300 rounded px-6 py-2 text-sm hover:bg-gray-50 transition-colors"
            >
              Masuk
            </router-link>
          </template>
          
          <template v-else>
            <router-link 
              to="/user" 
              class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
            >
              User Profile
            </router-link>
            <button 
              @click="logout" 
              class="bg-red-500 text-white rounded px-6 py-2 text-sm hover:bg-red-600 transition-colors"
            >
              Logout
            </button>
          </template>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
      <div class="flex gap-6">
        <!-- Sidebar Filters -->
        <div class="w-64 flex-shrink-0">
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
            <!-- Location Filter -->
            <div class="mb-6">
              <h3 class="font-semibold text-gray-900 mb-3">Lokasi</h3>
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Semarang"
                class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500"
                @input="applyFilters"
              />
            </div>

            <!-- Price Range Filter -->
            <div class="mb-6">
              <h3 class="font-semibold text-gray-900 mb-3">Harga</h3>
              <div class="space-y-3">
                <input 
                  v-model.number="filters.priceRange[0]"
                  type="number" 
                  placeholder="Minimal"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500"
                  @input="applyFilters"
                />
                <input 
                  v-model.number="filters.priceRange[1]"
                  type="number" 
                  placeholder="Maximal"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-orange-500"
                  @input="applyFilters"
                />
              </div>
            </div>

            <!-- Period Filter -->
            <div class="mb-6">
              <h3 class="font-semibold text-gray-900 mb-3">Periode</h3>
              <div class="space-y-2">
                <label class="flex items-center cursor-pointer">
                  <input 
                    v-model="filters.period"
                    type="radio" 
                    value="Harian"
                    class="mr-2 text-orange-500 focus:ring-orange-500"
                    @change="applyFilters"
                  />
                  <span class="text-gray-700">Harian</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input 
                    v-model="filters.period"
                    type="radio" 
                    value="Mingguan"
                    class="mr-2 text-orange-500 focus:ring-orange-500"
                    @change="applyFilters"
                  />
                  <span class="text-gray-700">Mingguan</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input 
                    v-model="filters.period"
                    type="radio" 
                    value="Bulanan"
                    class="mr-2 text-orange-500 focus:ring-orange-500"
                    @change="applyFilters"
                  />
                  <span class="text-gray-700">Bulanan</span>
                </label>
              </div>
            </div>

            <!-- Reset Button -->
            <button 
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
              @click="resetFilters"
              :disabled="isLoading"
            >
              Reset Filter
            </button>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1">
          <!-- Results Header -->
          <div class="mb-4" v-if="searchQuery">
            <p class="text-gray-600">
              Menampilkan {{ displayResultsCount }} dari {{ totalResults }} hasil untuk "{{ searchQuery }}"
            </p>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
            <p class="mt-2 text-gray-600">Mencari ruangan...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg mb-4">
            <p class="font-medium">Terjadi kesalahan:</p>
            <p class="text-sm mt-1">{{ error }}</p>
          </div>

          <!-- Rooms Grid -->
          <div v-else-if="rooms.length" class="space-y-4">
            <div 
              v-for="room in rooms" 
              :key="room.id"
              class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
              @click="goToRoomDetail(room.id)"
            >
              <div class="flex">
                <!-- Room Image -->
                <div class="w-48 h-32 bg-orange-400 flex-shrink-0">
                  <img 
                    v-if="room.image || room.featured_image" 
                    :src="room.image || room.featured_image" 
                    :alt="room.name || 'Room image'"
                    class="w-full h-full object-cover"
                    @error="$event.target.style.display = 'none'"
                  />
                  <div 
                    v-else 
                    class="w-full h-full bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center"
                  >
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0V9a2 2 0 012-2h4a2 2 0 012 2v12"></path>
                    </svg>
                  </div>
                </div>
                
                <!-- Room Details -->
                <div class="flex-1 p-4 flex justify-between">
                  <div class="flex-1">
                    <h3 class="font-semibold text-lg text-gray-900 mb-1">
                      {{ room.name || `Ruang di ${searchQuery || 'Kota'}` }}
                    </h3>
                    <div class="flex items-center mb-2">
                      <svg class="w-4 h-4 text-orange-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                      <span class="text-gray-600">
                        {{ room.rating || '4.7' }} ({{ room.review_count || room.reviews || '20' }} ulasan)
                      </span>
                    </div>
                    <p class="text-gray-600 text-sm mb-1">
                      Kapasitas: {{ room.capacity || '1-12 orang' }}
                    </p>
                    <p class="text-gray-500 text-sm">
                      {{ room.location || searchQuery || 'Lokasi tersedia' }}
                    </p>
                  </div>
                  
                  <!-- Price -->
                  <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900">
                      Rp {{ formatPrice(room.price || room.price_per_day || 50000) }}
                      <span class="text-sm font-normal text-gray-600">/{{ filters.period.toLowerCase() }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Rooms State -->
          <div v-else-if="searchQuery" class="text-center py-12">
            <div class="text-gray-400 mb-4">
              <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0V9a2 2 0 012-2h4a2 2 0 012 2v12"></path>
              </svg>
            </div>
            <p class="text-gray-600 text-lg mb-2">Tidak ada ruangan ditemukan</p>
            <p class="text-gray-500 text-sm">Coba ubah kata kunci pencarian atau filter Anda</p>
          </div>

          <!-- Empty Search State -->
          <div v-else class="text-center py-12">
            <div class="text-gray-400 mb-4">
              <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <p class="text-gray-600 text-lg mb-2">Mulai pencarian Anda</p>
            <p class="text-gray-500 text-sm">Masukkan kata kunci untuk mencari ruangan yang tersedia</p>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex justify-center mt-8 space-x-2">
            <button 
              v-if="currentPage > 1"
              @click="changePage(currentPage - 1)"
              class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Previous
            </button>
            
            <button 
              v-for="page in Math.min(5, totalPages)" 
              :key="page"
              :class="{
                'px-4 py-2 rounded-lg transition-colors': true,
                'bg-orange-500 text-white': currentPage === page,
                'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50': currentPage !== page
              }"
              @click="changePage(page)"
            >
              {{ page }}
            </button>
            
            <button 
              v-if="currentPage < totalPages"
              @click="changePage(currentPage + 1)"
              class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <OnlyFooter />
  </div>
</template>

<style scoped>
/* Custom radio button styling */
input[type="radio"] {
  accent-color: #f97316; /* orange-500 */
}

/* Loading animation enhancement */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
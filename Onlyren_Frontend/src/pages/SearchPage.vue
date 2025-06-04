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
            <!-- Debug Info -->
            <div class="mb-4 p-2 bg-gray-100 rounded text-xs" v-if="showDebug">
              <p>Rooms count: {{ rooms.length }}</p>
              <p>Total results: {{ totalResults }}</p>
              <p>Loading: {{ isLoading }}</p>
              <p>Error: {{ error }}</p>
              <p>Search query: {{ searchQuery }}</p>
              <p>Has searched: {{ hasSearched }}</p>
            </div>

            <!-- Test Button -->
            <button 
              @click="testWithMockData" 
              class="w-full mb-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm"
            >
              Test with Mock Data
            </button>

            <!-- Location Filter -->
            <div class="mb-6">
              <h3 class="font-semibold text-gray-900 mb-3">Lokasi</h3>
              <input 
                v-model="locationFilter"
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
          <div class="mb-4" v-if="searchQuery || hasSearched">
            <p class="text-gray-600">
              Menampilkan {{ displayResultsCount }} dari {{ totalResults }} hasil
              <span v-if="searchQuery">untuk "{{ searchQuery }}"</span>
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
            <button 
              @click="retrySearch" 
              class="mt-2 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
            >
              Coba Lagi
            </button>
          </div>

          <!-- Rooms Grid -->
          <div v-else-if="rooms && rooms.length > 0" class="space-y-4">
            <div 
              v-for="room in rooms" 
              :key="room.id || room.name"
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
                    @error="handleImageError"
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
          <div v-else-if="hasSearched" class="text-center py-12">
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

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { fetchRooms } from '@/api/room'
import OnlyFooter from '@/components/OnlyFooter.vue'

// Search and filter state
const searchQuery = ref('')
const locationFilter = ref('')
const rooms = ref([])
const hasSearched = ref(false)
const showDebug = ref(true) // Set to false in production

const filters = ref({
  type: '',
  priceRange: [0, 1000000],
  amenities: [],
  period: 'Harian'
})

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const totalResults = ref(0)

// Loading and error states
const isLoading = ref(false)
const error = ref(null)

// Auth state
const isAuthenticated = ref(false)

// Router
const router = useRouter()
const route = useRoute()

// Mock data for testing
const mockRooms = [
  {
    id: 1,
    name: 'Meeting Room Semarang',
    location: 'Semarang',
    type: 'Meeting Room',
    capacity: '1-10 orang',
    price: 75000,
    price_per_day: 75000,
    image: 'https://via.placeholder.com/300x200/f97316/ffffff?text=Meeting+Room',
    rating: 4.5,
    review_count: 15,
    amenities: ['WiFi', 'AC', 'Projector']
  },
  {
    id: 2,
    name: 'Co-working Space Semarang',
    location: 'Semarang',
    type: 'Co-working',
    capacity: '5-20 orang',
    price: 125000,
    price_per_day: 125000,
    image: 'https://via.placeholder.com/300x200/f97316/ffffff?text=Co-working',
    rating: 4.7,
    review_count: 23,
    amenities: ['WiFi', 'AC', 'Coffee']
  },
  {
    id: 3,
    name: 'Event Hall Semarang',
    location: 'Semarang',
    type: 'Event Hall',
    capacity: '50-100 orang',
    price: 350000,
    price_per_day: 350000,
    image: 'https://via.placeholder.com/300x200/f97316/ffffff?text=Event+Hall',
    rating: 4.8,
    review_count: 32,
    amenities: ['Sound System', 'Stage', 'Parking']
  }
]

// Computed properties
const displayResultsCount = computed(() => {
  return rooms.value ? rooms.value.length : 0
})

// Test function
const testWithMockData = () => {
  console.log('Loading mock data...')
  rooms.value = [...mockRooms] // Create a copy to avoid reference issues
  totalResults.value = mockRooms.length
  totalPages.value = 1
  hasSearched.value = true
  isLoading.value = false
  error.value = null
  console.log('Mock data loaded successfully:', rooms.value)
}

// Search methods
const performSearch = async (resetPage = true) => {
  if (!searchQuery.value || searchQuery.value.trim() === '') {
    console.log('No search query provided')
    return
  }
  
  if (resetPage) {
    currentPage.value = 1
  }
  
  hasSearched.value = true
  isLoading.value = true
  error.value = null
  
  try {
    const searchParams = {
      query: searchQuery.value.trim(),
      location: locationFilter.value || searchQuery.value.trim(),
      type: filters.value.type,
      priceRange: filters.value.priceRange,
      amenities: filters.value.amenities,
      period: filters.value.period,
      page: currentPage.value,
      per_page: 12
    }
    
    console.log('Search params:', searchParams)
    
    const response = await fetchRooms(searchParams)
    
    console.log('API Response:', response)
    
    // Handle different response structures
    if (response) {
      if (response.data && Array.isArray(response.data)) {
        // Standard pagination response
        rooms.value = response.data
        totalPages.value = response.meta?.last_page || 1
        totalResults.value = response.meta?.total || response.data.length
      } else if (Array.isArray(response)) {
        // Direct array response
        rooms.value = response
        totalPages.value = 1
        totalResults.value = response.length
      } else if (response.rooms && Array.isArray(response.rooms)) {
        // Nested rooms response
        rooms.value = response.rooms
        totalPages.value = response.pagination?.total_pages || 1
        totalResults.value = response.pagination?.total || response.rooms.length
      } else {
        // Unexpected response structure
        console.warn('Unexpected response structure:', response)
        rooms.value = []
        totalPages.value = 1
        totalResults.value = 0
      }
    } else {
      rooms.value = []
      totalPages.value = 1
      totalResults.value = 0
    }
    
    console.log('Processed rooms:', rooms.value)
    console.log('Total results:', totalResults.value)
    
  } catch (err) {
    console.error('Search error:', err)
    error.value = err.message || 'Failed to fetch rooms'
    rooms.value = []
    totalResults.value = 0
    
    // Fallback to mock data for testing
    if (err.message && (err.message.includes('Network') || err.message.includes('timeout') || err.message.includes('fetch'))) {
      console.log('Network error - using mock data for testing')
      setTimeout(() => {
        testWithMockData()
      }, 1000)
    }
  } finally {
    isLoading.value = false
  }
}

// Handle search button click
const handleSearch = async () => {
  console.log('Search button clicked with query:', searchQuery.value)
  await performSearch()
}

// Handle search input keydown
const handleSearchKeydown = async (event) => {
  if (event.key === 'Enter') {
    console.log('Enter key pressed in search input')
    await handleSearch()
  }
}

// Retry search
const retrySearch = () => {
  console.log('Retrying search...')
  performSearch(false)
}

// Filter methods
const applyFilters = async () => {
  console.log('Applying filters...')
  if (hasSearched.value) {
    currentPage.value = 1
    await performSearch(false)
  }
}

const resetFilters = async () => {
  console.log('Resetting filters...')
  filters.value = {
    type: '',
    priceRange: [0, 1000000],
    amenities: [],
    period: 'Harian'
  }
  locationFilter.value = ''
  currentPage.value = 1
  
  if (hasSearched.value) {
    await performSearch(false)
  }
}

// Pagination
const changePage = async (page) => {
  if (page !== currentPage.value && page >= 1 && page <= totalPages.value) {
    console.log('Changing to page:', page)
    currentPage.value = page
    await performSearch(false)
  }
}

// Navigation methods
const goToRoomDetail = (roomId) => {
  if (roomId) {
    console.log('Navigating to room detail:', roomId)
    router.push(`/rooms/${roomId}`)
  }
}

// Utility methods
const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('id-ID').format(price)
}

const handleImageError = (event) => {
  console.warn('Image failed to load:', event.target.src)
  event.target.style.display = 'none'
}

// Auth methods
const logout = () => {
  console.log('Logging out...')
  isAuthenticated.value = false
  // Add your logout logic here
  router.push('/login')
}

// Watch for route changes
watch(() => route.query, (newQuery) => {
  if (newQuery.q && newQuery.q !== searchQuery.value) {
    searchQuery.value = newQuery.q
    performSearch()
  }
}, { immediate: true })

// Initialize component
onMounted(() => {
  console.log('Component mounted')
  
  // Check if there's a search query in the route
  if (route.query.q) {
    searchQuery.value = route.query.q
    performSearch()
  }
  
  // Check authentication status
  // Add your auth check logic here
  isAuthenticated.value = false // Replace with actual auth check
})

// Debugging: Watch rooms array changes
watch(rooms, (newRooms) => {
  console.log('Rooms array changed:', newRooms)
}, { deep: true })

// Debugging: Watch hasSearched changes
watch(hasSearched, (newValue) => {
  console.log('hasSearched changed to:', newValue)
})
</script>

<style scoped>
/* Add any component-specific styles here */
.font-koulen {
  font-family: 'Koulen', cursive;
}

/* Loading animation */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .flex-col-mobile {
    flex-direction: column;
  }
  
  .w-64 {
    width: 100%;
  }
}
</style>
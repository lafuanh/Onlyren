<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import RoomCard from '@/components/RoomCard.vue'
import { fetchRooms } from '@/api/room'

import OnlyHeader from '@/components/OnlyHeader.vue'

// Search and filter state
const searchQuery = ref('')
const rooms = ref([])
const filters = ref({
  type: '',
  priceRange: [0, 1000000],
  amenities: []
})

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)

// Loading and error states
const isLoading = ref(false)
const error = ref(null)

// Search and filter methods
const performSearch = async () => {
  isLoading.value = true
  error.value = null
  try {
    const response = await fetchRooms({
      query: searchQuery.value,
      ...filters.value,
      page: currentPage.value
    })
    rooms.value = response.data
    totalPages.value = response.meta.last_page
  } catch (err) {
    error.value = 'Failed to fetch rooms'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Filter methods
const applyFilters = () => {
  currentPage.value = 1
  performSearch()
}

const resetFilters = () => {
  filters.value = {
    type: '',
    priceRange: [0, 1000000],
    amenities: []
  }
  searchQuery.value = ''
  performSearch()
}

// Pagination
const changePage = (page) => {
  currentPage.value = page
  performSearch()
}

// Navigation
const router = useRouter()
const goToRoomDetail = (roomId) => {
  router.push({ name: 'RoomDetail', params: { id: roomId } })
}

// Initial load
onMounted(performSearch)
</script>

<template>
      <OnlyHeader />
  <div class="container mx-auto px-4 py-6">
    <!-- Search Bar -->
   

    <!-- Filters -->
    <div class="mb-6 flex space-x-4">
      <!-- Room Type Filter -->
      <select 
        v-model="filters.type" 
        class="px-4 py-2 border rounded-lg"
        @change="applyFilters"
      >
        <option value="">All Types</option>
        <option value="meeting">Meeting Room</option>
        <option value="workspace">Workspace</option>
        <option value="private">Private Office</option>
      </select>

      <!-- Price Range Filter -->
      <div class="flex items-center space-x-2">
        <input 
          v-model.number="filters.priceRange[0]"
          type="number" 
          placeholder="Min Price"
          class="w-24 px-2 py-1 border rounded-lg"
        />
        <span>-</span>
        <input 
          v-model.number="filters.priceRange[1]"
          type="number" 
          placeholder="Max Price"
          class="w-24 px-2 py-1 border rounded-lg"
        />
        <button 
          class="px-3 py-1 bg-orange-500 text-white rounded-lg"
          @click="applyFilters"
        >
          Apply
        </button>
      </div>

      <!-- Reset Filters -->
      <button 
        class="px-4 py-2 bg-gray-200 rounded-lg"
        @click="resetFilters"
      >
        Reset
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-6">
      Loading rooms...
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
      {{ error }}
    </div>

    <!-- Rooms Grid -->
    <div 
      v-else-if="rooms.length" 
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
    >
      <RoomCard 
        v-for="room in rooms" 
        :key="room.id"
        :room="room"
        @click="goToRoomDetail(room.id)"
      />
    </div>

    <!-- No Rooms State -->
    <div v-else class="text-center py-6">
      No rooms found matching your search
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center mt-6 space-x-2">
      <button 
        v-for="page in totalPages" 
        :key="page"
        :class="{
          'px-4 py-2 rounded-lg': true,
          'bg-blue-500 text-white': currentPage === page,
          'bg-gray-200': currentPage !== page
        }"
        @click="changePage(page)"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>



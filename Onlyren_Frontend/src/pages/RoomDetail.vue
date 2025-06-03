<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchRoomDetails, createReservation } from '@/api/room'
import ReviewSection from '@/components/ReviewSection.vue'
import ImageGallery from '@/components/ImageGallery.vue'
import OnlyHeader from '@/components/OnlyHeader.vue'
import OnlyFooter from '@/components/OnlyFooter.vue'

// Component state
const room = ref(null)
const isLoading = ref(true)
const error = ref(null)

// Reservation state
const reservationForm = ref({
  date: '',
  start_time: '',
  end_time: '',
  guests: 1
})
const reservationError = ref(null)

// Route and navigation
const route = useRoute()
const router = useRouter()

// Fetch room details
const loadRoomDetails = async () => {
  isLoading.value = true
  try {
    room.value = await fetchRoomDetails(route.params.id)
  } catch (err) {
    error.value = 'Failed to load room details'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Reservation submission
const submitReservation = async () => {
  reservationError.value = null
  try {
    await createReservation({
      room_id: route.params.id,
      ...reservationForm.value
    })
    router.push('/reservations/success')
  } catch (err) {
    reservationError.value = 'Reservation failed. Please try again.'
    console.error(err)
  }
}

// Lifecycle hook
onMounted(loadRoomDetails)

// Utility methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(value)
}
</script>

<template>
  <!-- Header -->
  <OnlyHeader />

  <div v-if="isLoading" class="text-center py-6">
    Loading room details...
  </div>

  <div v-else-if="error" class="bg-red-100 text-red-800 p-4 rounded-lg">
    {{ error }}
  </div>

  <div v-else-if="room" class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
      <!-- Main Content Grid -->
      <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Room Image and Details -->
        <div class="lg:col-span-2">
          <!-- Room Image -->
          <div class="mb-6">
            <!-- <ImageGallery :images="room.images"  />
            PLEASE CHANGE ABOVE -->
            <img src="https://www.ikea.com/images/an-open-plan-cafe-shop-and-co-working-space-framed-by-open-g-cc39d1c39508ca5cbe043d4bf962f798.jpg?f=xxxl" class="rounded-2xl overflow-hidden">
          </div>

          <!-- Room Header -->
          <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">{{ room.name }}</h1>
            <div class="flex items-center text-gray-600 mb-4">
              <span>{{ room.location }}</span>
              <span class="mx-2">•</span>
              <span>{{ room.type }}</span>
            </div>
            
            <!-- Rating and Reviews -->
            <div class="flex items-center mb-4">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                  <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                </svg>
                <span class="ml-1 font-semibold">{{ room.rating || '4.7' }}</span>
                <span class="ml-1 text-gray-500">({{ room.review_count || '20' }})</span>
              </div>
              
              <!-- Action Buttons -->
              <div class="ml-auto flex items-center space-x-3">
                <button class="flex items-center text-gray-600 hover:text-gray-800">
                  <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                  Simpan
                </button>
                <button class="flex items-center text-gray-600 hover:text-gray-800">
                  <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                  </svg>
                  Bagi
                </button>
              </div>
            </div>

            <!-- Capacity -->
            <div class="text-gray-700 mb-6">
              <span class="font-medium">Kapasitas: </span>
              <span>{{ room.capacity || '1-12' }}</span>
            </div>
          </div>

          <!-- Pricing Section -->
          <div class="bg-white rounded-xl p-6 mb-6 shadow-sm">
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-2xl font-bold">{{ formatCurrency(room.price_per_hour || 50000) }}</span>
                <span class="text-gray-600">/Hari</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xl font-semibold">{{ formatCurrency(room.price_per_month || 320000) }}</span>
                <span class="text-gray-600">/Minggu</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xl font-semibold">{{ formatCurrency(room.price_per_year || 1200000) }}</span>
                <span class="text-gray-600">/Bulan</span>
              </div>
            </div>
          </div>

          <!-- Specifications and Facilities -->
          <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Specifications -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
              <h3 class="text-lg font-semibold mb-4">Spesifikasi ruang</h3>
              <p class="text-gray-600">{{ room.specifications || '20 × 10.4 meter' }}</p>
            </div>

            <!-- Facilities -->
            <div class="bg-white rounded-xl p-6 shadow-sm">
              <h3 class="text-lg font-semibold mb-4">Fasilitas ruang</h3>
              <ul class="text-gray-600 space-y-1">
                <li v-for="amenity in room.amenities" :key="amenity">
                  {{ amenity }}
                </li>
                <li v-if="!room.amenities || room.amenities.length === 0">
                  Wifi (100 mbps)<br>
                  4 Meja panjang<br>
                  12 Kursi
                </li>
              </ul>
            </div>
          </div>

          <!-- Room Description -->
          <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="text-xl font-semibold mb-4">Room Description</h2>
            <p class="text-gray-700 leading-relaxed">{{ room.description }}</p>
          </div>
        </div>

        <!-- Right Column: Reservation Form -->
        <div class="lg:col-span-1">
          <div class="sticky top-6">
            <div class="bg-white rounded-xl p-6 shadow-lg">
              <div class="flex space-x-2 mb-6">
                <button class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg font-medium">
                  Tanya
                </button>
                <button class="flex-1 bg-orange-500 text-white py-2 px-4 rounded-lg font-medium">
                  Ajukan Sewa
                </button>
              </div>

              <form @submit.prevent="submitReservation">
                <h3 class="text-lg font-semibold mb-4">Make a Reservation</h3>

                <!-- Date Input -->
                <div class="mb-4">
                  <label class="block text-sm font-medium mb-2">Date</label>
                  <input 
                    v-model="reservationForm.date"
                    type="date" 
                    required
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  />
                </div>

                <!-- Time Inputs -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium mb-2">Start Time</label>
                    <input 
                      v-model="reservationForm.start_time"
                      type="time" 
                      required
                      class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-2">End Time</label>
                    <input 
                      v-model="reservationForm.end_time"
                      type="time" 
                      required
                      class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    />
                  </div>
                </div>

                <!-- Guests Input -->
                <div class="mb-6">
                  <label class="block text-sm font-medium mb-2">Number of Guests</label>
                  <input 
                    v-model.number="reservationForm.guests"
                    type="number" 
                    min="1"
                    max="10"
                    required
                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  />
                </div>

                <!-- Reservation Error -->
                <div 
                  v-if="reservationError" 
                  class="bg-red-100 text-red-800 p-3 rounded-lg mb-4 text-sm"
                >
                  {{ reservationError }}
                </div>

                <!-- Submit Button -->
                <button 
                  type="submit" 
                  class="w-full bg-orange-500 text-white py-3 rounded-lg hover:bg-orange-600 transition-colors font-medium"
                >
                  Book Now
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews Section -->
      <!-- <div class="mt-12">
        <ReviewSection :room-id="room.id" />
      </div> -->
    </div>
  </div>

  <OnlyFooter />
</template>
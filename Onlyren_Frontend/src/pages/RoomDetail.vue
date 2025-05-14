<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchRoomDetails, createReservation } from '@/api/room'
import ReviewSection from '@/components/ReviewSection.vue'
import ImageGallery from '@/components/ImageGallery.vue'
import OnlyHeader from '@/components/OnlyHeader.vue'

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
    // Show success modal or redirect
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

  <div v-else-if="room" class="container mx-auto px-4 py-6">
    <!-- Room Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-2">{{ room.name }}</h1>
      <div class="flex items-center text-gray-600">
        <span>{{ room.location }}</span>
        <span class="mx-2">•</span>
        <span>{{ room.type }}</span>
      </div>
    </div>

    <!-- Image Gallery -->
    <ImageGallery :images="room.images" class="mb-6" />

    <!-- Room Details -->
    <div class="grid md:grid-cols-2 gap-6">
      <!-- Left Column: Room Information -->
      <div>
        <h2 class="text-xl font-semibold mb-4">Room Description</h2>
        <p class="mb-4">{{ room.description }}</p>

        <h3 class="text-lg font-semibold mb-2">Amenities</h3>
        <ul class="list-disc list-inside mb-4">
          <li v-for="amenity in room.amenities" :key="amenity">
            {{ amenity }}
          </li>
        </ul>

        <div class="bg-gray-100 p-4 rounded-lg">
          <p class="text-xl font-bold">
            {{ formatCurrency(room.price_per_hour) }} / hour
          </p>
        </div>
      </div>

      <!-- Right Column: Reservation Form -->
      <div>
        <form @submit.prevent="submitReservation" class="bg-white shadow-md rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Make a Reservation</h2>

          <!-- Date Input -->
          <div class="mb-4">
            <label class="block mb-2">Date</label>
            <input 
              v-model="reservationForm.date"
              type="date" 
              required
              class="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <!-- Time Inputs -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block mb-2">Start Time</label>
              <input 
                v-model="reservationForm.start_time"
                type="time" 
                required
                class="w-full px-3 py-2 border rounded-lg"
              />
            </div>
            <div>
              <label class="block mb-2">End Time</label>
              <input 
                v-model="reservationForm.end_time"
                type="time" 
                required
                class="w-full px-3 py-2 border rounded-lg"
              />
            </div>
          </div>

          <!-- Guests Input -->
          <div class="mb-4">
            <label class="block mb-2">Number of Guests</label>
            <input 
              v-model.number="reservationForm.guests"
              type="number" 
              min="1"
              max="10"
              required
              class="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <!-- Reservation Error -->
          <div 
            v-if="reservationError" 
            class="bg-red-100 text-red-800 p-3 rounded-lg mb-4"
          >
            {{ reservationError }}
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition"
          >
            Book Now
          </button>
        </form>
      </div>
    </div>

    <!-- Reviews Section -->
    <ReviewSection 
      :room-id="room.id" 
      class="mt-8"
    />
  </div>
</template>
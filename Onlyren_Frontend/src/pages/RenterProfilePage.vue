<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchRenterProfile, updateRenterProfile } from '@/api/renter'
import { fetchRenterRooms, createNewRoom, updateRoomStatus } from '@/api/room'
import { fetchRenterReservations, updateReservationStatus } from '@/api/reservation'
import { logout } from '@/api/auth'

// [Previous script setup remains the same]

// Add method to update reservation status
const handleReservationStatusChange = async (reservationId, newStatus) => {
  try {
    await updateReservationStatus(reservationId, newStatus)
    await loadRenterReservations()
    successMessage.value = 'Reservation status updated successfully'
  } catch (err) {
    error.value = 'Failed to update reservation status'
    console.error(err)
  }
}

// Rest of the previous script setup remains the same
</script>

<template>
  <!-- [Previous template remains the same] -->

  <!-- Reservations Tab (replacing the previous incomplete section) -->
  <div v-if="activeTab === 'reservations'" class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Room Reservations</h2>

    <!-- No Reservations State -->
    <div v-if="reservations.length === 0" class="text-center py-6 text-gray-500">
      You have no current reservations
    </div>

    <!-- Reservations List -->
    <div v-else class="space-y-4">
      <div 
        v-for="reservation in reservations" 
        :key="reservation.id" 
        class="border rounded-lg p-4 flex justify-between items-center"
      >
        <div class="flex-grow">
          <div class="flex items-center mb-2">
            <h3 class="font-semibold mr-4">{{ reservation.room.name }}</h3>
            <span 
              :class="{
                'px-2 py-1 rounded-full text-sm': true,
                'bg-yellow-100 text-yellow-800': reservation.status === 'pending',
                'bg-green-100 text-green-800': reservation.status === 'confirmed',
                'bg-red-100 text-red-800': reservation.status === 'cancelled'
              }
              "
            >
              {{ reservation.status }}
            </span>
          </div>
          
          <div class="text-gray-600 mb-2">
            <p>
              {{ formatDate(reservation.date) }} 
              · {{ reservation.start_time }} - {{ reservation.end_time }}
            </p>
            <p>Guest: {{ reservation.user.name }}</p>
          </div>
          
          <p class="font-bold">
            {{ formatCurrency(reservation.total_price) }}
          </p>
        </div>

        <!-- Reservation Actions -->
        <div class="flex flex-col space-y-2">
          <button 
            v-if="reservation.status === 'pending'"
            @click="handleReservationStatusChange(reservation.id, 'confirmed')"
            class="px-3 py-1 bg-green-500 text-white rounded-lg"
          >
            Confirm
          </button>
          
          <button 
            v-if="reservation.status === 'pending' || reservation.status === 'confirmed'"
            @click="handleReservationStatusChange(reservation.id, 'cancelled')"
            class="px-3 py-1 bg-red-500 text-white rounded-lg"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Add Room Modal -->
    <div 
      v-if="isAddRoomModalOpen" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-semibold mb-4">Add New Room</h2>
        
        <form @submit.prevent="submitNewRoom">
          <!-- Room Name -->
          <div class="mb-4">
            <label class="block mb-2">Room Name</label>
            <input 
              v-model="newRoomForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <!-- Room Type -->
          <div class="mb-4">
            <label class="block mb-2">Room Type</label>
            <select 
              v-model="newRoomForm.type"
              required
              class="w-full px-3 py-2 border rounded-lg"
            >
              <option value="">Select Room Type</option>
              <option value="meeting">Meeting Room</option>
              <option value="workspace">Workspace</option>
              <option value="private">Private Office</option>
            </select>
          </div>

          <!-- Capacity -->
          <div class="mb-4">
            <label class="block mb-2">Capacity</label>
            <input 
              v-model.number="newRoomForm.capacity"
              type="number"
              min="1"
              max="50"
              required
              class="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <!-- Price per Hour -->
          <div class="mb-4">
            <label class="block mb-2">Price per Hour (IDR)</label>
            <input 
              v-model.number="newRoomForm.price_per_hour"
              type="number"
              min="0"
              required
              class="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <!-- Description -->
          <div class="mb-4">
            <label class="block mb-2">Description</label>
            <textarea 
              v-model="newRoomForm.description"
              required
              class="w-full px-3 py-2 border rounded-lg min-h-[100px]"
            ></textarea>
          </div>

          <!-- Amenities -->
          <div class="mb-4">
            <label class="block mb-2">Amenities</label>
            <div class="grid grid-cols-2 gap-2">
              <label 
                v-for="amenity in [
                  'WiFi', 'Whiteboard', 'Projector', 
                  'Air Conditioning', 'Power Outlets', 
                  'Meeting Phone', 'Printer'
                ]" 
                :key="amenity"
                class="inline-flex items-center"
              >
                <input 
                  type="checkbox"
                  :value="amenity"
                  v-model="newRoomForm.amenities"
                  class="mr-2"
                />
                {{ amenity }}
              </label>
            </div>
          </div>

          <!-- Room Images -->
          <div class="mb-4">
            <label class="block mb-2">Room Images</label>
            <input 
              type="file"
              multiple
              accept="image/*"
              @change="handleRoomImagesUpload"
              class="w-full px-3 py-2 border rounded-lg"
            />
            
            <!-- Image Previews -->
            <div v-if="newRoomForm.images.length" class="mt-2 flex space-x-2">
              <img 
                v-for="(image, index) in newRoomForm.images" 
                :key="index"
                :src="image"
                alt="Room Preview"
                class="w-20 h-20 object-cover rounded-lg"
              />
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="flex justify-end space-x-4 mt-6">
            <button 
              type="button"
              @click="isAddRoomModalOpen = false"
              class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg"
            >
              Add Room
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
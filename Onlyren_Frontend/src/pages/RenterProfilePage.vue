<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchRenterProfile, updateRenterProfile } from '@/api/renter'
import { fetchRenterRooms, createNewRoom, updateRoomStatus } from '@/api/room'
import { fetchRenterReservations, updateReservationStatus } from '@/api/reservation'
import { logout } from '@/api/auth'

// [Previous script setup remains the same]

// Profile state
const profile = ref({
  name: '',
  email: '',
  phone: '',
  avatar: null
})

// Form state
const isEditing = ref(false)
const isLoading = ref(true)
const error = ref(null)
const successMessage = ref(null)

// File upload
const avatarFile = ref(null)

// Reservations
const reservations = ref([])
const activeTab = ref('profile')

// Fetch user profile
const loadUserProfile = async () => {
  isLoading.value = true
  try {
    profile.value = await fetchUserProfile()
  } catch (err) {
    error.value = 'Failed to load profile'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Load reservations
const loadReservations = async () => {
  try {
    reservations.value = await fetchUserReservations()
  } catch (err) {
    error.value = 'Failed to load reservations'
    console.error(err)
  }
}

// Update profile
const saveProfile = async () => {
  error.value = null
  successMessage.value = null
  
  try {
    // Prepare form data for upload
    const formData = new FormData()
    formData.append('name', profile.value.name)
    formData.append('phone', profile.value.phone)
    
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value)
    }

    await updateUserProfile(formData)
    
    successMessage.value = 'Profile updated successfully'
    isEditing.value = false
    
    // Reload profile to get updated data
    await loadUserProfile()
  } catch (err) {
    error.value = 'Failed to update profile'
    console.error(err)
  }
}

// Handle avatar upload
const handleAvatarUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    avatarFile.value = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      profile.value.avatar = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Logout
const router = useRouter()
const handleLogout = async () => {
  try {
    await logout()
    router.push('/login')
  } catch (err) {
    error.value = 'Logout failed'
    console.error(err)
  }
}

// Tab change handler
const changeTab = (tab) => {
  activeTab.value = tab
  
  // Load reservations when switching to reservations tab
  if (tab === 'reservations') {
    loadReservations()
  }
}

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(value)
}

// Lifecycle hook
onMounted(loadUserProfile)

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
  <OnlyHeader />
  <div class="container mx-auto px-4 py-6">
    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-6">
      Loading profile...
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-100 text-red-800 p-4 rounded-lg mb-4">
      {{ error }}
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="bg-green-100 text-green-800 p-4 rounded-lg mb-4">
      {{ successMessage }}
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6 border-b">
      <nav class="flex space-x-4">
        <button 
          @click="changeTab('profile')"
          :class="{
            'pb-2 border-b-2': true,
            'border-blue-500 text-blue-600': activeTab === 'profile',
            'border-transparent text-gray-500': activeTab !== 'profile'
          }"
        >
          Profile
        </button>
        <button 
          @click="changeTab('reservations')"
          :class="{
            'pb-2 border-b-2': true,
            'border-blue-500 text-blue-600': activeTab === 'reservations',
            'border-transparent text-gray-500': activeTab !== 'reservations'
          }"
        >
          My Reservations
        </button>
      </nav>
    </div>

    <!-- Profile Tab -->
    <div v-if="activeTab === 'profile'" class="bg-white shadow-md rounded-lg p-6">
      <!-- Avatar Section -->
      <div class="flex items-center mb-6">
        <div class="mr-6">
          <img 
            :src="profile.avatar || '/default-avatar.png'"
            alt="Profile Avatar"
            class="w-24 h-24 rounded-full object-cover"
          />
        </div>
        
        <!-- Avatar Upload -->
        <div>
          <input 
            type="file" 
            ref="avatarInput"
            @change="handleAvatarUpload"
            accept="image/*"
            class="hidden"
          />
          <button 
            @click="$refs.avatarInput.click()"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg"
          >
            Change Avatar
          </button>
        </div>
      </div>

      <!-- Profile Form -->
      <form @submit.prevent="saveProfile">
        <div class="grid md:grid-cols-2 gap-4">
          <!-- Name Input -->
          <div>
            <label class="block mb-2">Name</label>
            <input 
              v-model="profile.name"
              :disabled="!isEditing"
              type="text"
              class="w-full px-3 py-2 border rounded-lg"
              :class="{ 'bg-gray-100': !isEditing }"
            />
          </div>

          <!-- Email Input (Read Only) -->
          <div>
            <label class="block mb-2">Email</label>
            <input 
              v-model="profile.email"
              type="email"
              disabled
              class="w-full px-3 py-2 border rounded-lg bg-gray-100"
            />
          </div>

          <!-- Phone Input -->
          <div>
            <label class="block mb-2">Phone Number</label>
            <input 
              v-model="profile.phone"
              :disabled="!isEditing"
              type="tel"
              class="w-full px-3 py-2 border rounded-lg"
              :class="{ 'bg-gray-100': !isEditing }"
            />
          </div>
        </div>

        <!-- Edit/Save Buttons -->
        <div class="mt-6 flex space-x-4">
          <button 
            v-if="!isEditing"
            type="button"
            @click="isEditing = true"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg"
          >
            Edit Profile
          </button>
          <template v-else>
            <button 
              type="submit"
              class="px-4 py-2 bg-green-500 text-white rounded-lg"
            >
              Save Changes
            </button>
            <button 
              type="button"
              @click="isEditing = false"
              class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg"
            >
              Cancel
            </button>
          </template>
        </div>
      </form>

      <!-- Logout Button -->
      <div class="mt-6">
        <button 
          @click="handleLogout"
          class="px-4 py-2 bg-red-500 text-white rounded-lg"
        >
          Logout
        </button>
      </div>
    </div>

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
  </div>
</template>
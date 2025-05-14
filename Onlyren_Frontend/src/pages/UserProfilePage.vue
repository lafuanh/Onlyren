<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchUserProfile, updateUserProfile } from '@/api/user'
import { fetchUserReservations } from '@/api/reservation'
import { logout } from '@/api/auth'
import OnlyHeader from '@/components/OnlyHeader.vue'

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
</script>

<template>
      <!-- Header -->
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

    <!-- Reservations Tab -->
    <div v-if="activeTab === 'reservations'" class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">My Reservations</h2>

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
          <div>
            <h3 class="font-semibold">{{ reservation.room.name }}</h3>
            <p class="text-gray-600">
              {{ formatDate(reservation.date) }} 
              · {{ reservation.start_time }} - {{ reservation.end_time }}
            </p>
          </div>
          <div class="text-right">
            <p class="font-bold">
              {{ formatCurrency(reservation.total_price) }}
            </p>
            <span 
              :class="{
                'px-2 py-1 rounded-full text-sm': true,
                'bg-green-100 text-green-800': reservation.status === 'confirmed',
                'bg-yellow-100 text-yellow-800': reservation.status === 'pending',
                'bg-red-100 text-red-800': reservation.status === 'cancelled'
              }"
            >
              {{ reservation.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
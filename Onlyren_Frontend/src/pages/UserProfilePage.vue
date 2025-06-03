<!-- UserProfile.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <OnlyHeader />

    <div class="flex">
      <!-- Sidebar -->
      <div class="w-64 bg-white shadow-lg min-h-screen">
        <div class="p-6">
          <!-- User Profile Section -->
          <div class="flex items-center mb-8">
            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold">
              {{ getInitials(profile.name) }}
            </div>
            <div class="ml-3">
              <h3 class="font-semibold text-gray-800">{{ profile.name || 'User' }}</h3>
              <p class="text-sm text-gray-600">Akun</p>
            </div>
          </div>

          <!-- Navigation Menu -->
          <nav class="space-y-2">
            <button 
              @click="activeTab = 'profile'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'profile' 
                  ? 'bg-orange-100 text-orange-600 border-l-4 border-orange-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-user mr-3"></i>Profil
            </button>
            <button 
              @click="activeTab = 'reservations'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'reservations' 
                  ? 'bg-orange-100 text-orange-600 border-l-4 border-orange-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-calendar-alt mr-3"></i>Reservasi
            </button>
            <button 
              @click="activeTab = 'payments'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'payments' 
                  ? 'bg-orange-100 text-orange-600 border-l-4 border-orange-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-credit-card mr-3"></i>Pembayaran
            </button>
            <button 
              @click="activeTab = 'messages'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'messages' 
                  ? 'bg-orange-100 text-orange-600 border-l-4 border-orange-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-comments mr-3"></i>Chat
            </button>
          </nav>

          <!-- Logout Button -->
          <div class="mt-8 pt-4 border-t border-gray-200">
            <button 
              @click="handleLogout"
              class="w-full text-left px-4 py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition-colors"
            >
              <i class="fas fa-sign-out-alt mr-3"></i>Logout
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 p-6">
        <!-- Success/Error Messages -->
        <div v-if="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
          <i class="fas fa-exclamation-circle mr-2"></i>{{ error }}
        </div>
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
          <i class="fas fa-check-circle mr-2"></i>{{ successMessage }}
        </div>

        <!-- Profile Tab -->
        <div v-if="activeTab === 'profile'">
          <ProfileForm :profile="profile" @update="handleProfileUpdate" />
        </div>

        <!-- Reservations Tab -->
        <div v-if="activeTab === 'reservations'">
          <UserReservations :reservations="reservations" @cancel="handleCancelReservation" />
        </div>

        <!-- Payments Tab -->
        <div v-if="activeTab === 'payments'">
          <UserPayments :payments="payments" @pay="handlePayment" />
        </div>

        <!-- Messages Tab -->
        <div v-if="activeTab === 'messages'">
          <UserMessages :conversations="conversations" @send="handleSendMessage" />
        </div>
      </div>
    </div>

    <OnlyFooter />
  </div>
</template>

<script setup>
import OnlyHeader from '@/components/OnlyHeader.vue'
import OnlyFooter from '@/components/OnlyFooter.vue';
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ProfileForm from '@/components/ProfileForm.vue'
import UserReservations from '@/components/UserReservations.vue'
import UserPayments from '@/components/UserPayments.vue'
import UserMessages from '@/components/UserMessages.vue'
import { fetchUserProfile, fetchUserReservations, fetchUserPayments, fetchUserConversations, logout } from '@/api/user'

const activeTab = ref('profile')
const profile = ref({})
const reservations = ref([])
const payments = ref([])
const conversations = ref([])

const error = ref(null)
const successMessage = ref(null)

const router = useRouter()

const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const showMessage = (message, type = 'success') => {
  if (type === 'success') {
    successMessage.value = message
    error.value = null
  } else {
    error.value = message
    successMessage.value = null
  }
  setTimeout(() => {
    error.value = null
    successMessage.value = null
  }, 5000)
}

const loadProfile = async () => {
  try {
    profile.value = await fetchUserProfile()
  } catch (err) {
    showMessage('Failed to load profile', 'error')
  }
}

const loadReservations = async () => {
  try {
    reservations.value = await fetchUserReservations()
  } catch (err) {
    showMessage('Failed to load reservations', 'error')
  }
}

const loadPayments = async () => {
  try {
    payments.value = await fetchUserPayments()
  } catch (err) {
    showMessage('Failed to load payments', 'error')
  }
}

const loadConversations = async () => {
  try {
    conversations.value = await fetchUserConversations()
  } catch (err) {
    showMessage('Failed to load conversations', 'error')
  }
}

const handleProfileUpdate = async (updatedProfile) => {
  try {
    profile.value = { ...profile.value, ...updatedProfile }
    showMessage('Profile updated successfully')
  } catch (err) {
    showMessage('Failed to update profile', 'error')
  }
}

const handleCancelReservation = async (reservationId) => {
  try {
    // API call to cancel reservation
    await loadReservations()
    showMessage('Reservation cancelled successfully')
  } catch (err) {
    showMessage('Failed to cancel reservation', 'error')
  }
}

const handlePayment = async (paymentId) => {
  try {
    // API call to process payment
    await loadPayments()
    showMessage('Payment processed successfully')
  } catch (err) {
    showMessage('Failed to process payment', 'error')
  }
}

const handleSendMessage = async (messageData) => {
  try {
    // API call to send message
    await loadConversations()
    showMessage('Message sent successfully')
  } catch (err) {
    showMessage('Failed to send message', 'error')
  }
}

const handleLogout = async () => {
  try {
    await logout()
    router.push('/login')
  } catch (err) {
    showMessage('Failed to logout', 'error')
  }
}

onMounted(() => {
  loadProfile()
  loadReservations()
  loadPayments()
  loadConversations()
})
</script>
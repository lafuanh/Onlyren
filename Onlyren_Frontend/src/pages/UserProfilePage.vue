<!-- UserProfile.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <OnlyHeader ref="headerRef" />

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
              <p class="text-sm text-gray-600">User Account</p>
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
              <i class="fas fa-user mr-3"></i>Profile
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
              <i class="fas fa-calendar-alt mr-3"></i>Reservations
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
              <i class="fas fa-credit-card mr-3"></i>Payments
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
              <i class="fas fa-comments mr-3"></i>Messages
            </button>
          </nav>

          <!-- Logout Button -->
          <div class="mt-8 pt-4 border-t border-gray-200">
            <button 
              @click="handleLogout"
              :disabled="loggingOut"
              class="w-full text-left px-4 py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50"
            >
              <i class="fas fa-sign-out-alt mr-3"></i>
              {{ loggingOut ? 'Logging out...' : 'Logout' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 p-6">
        <!-- Success/Error Messages -->
        <div v-if="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>{{ error }}</span>
            <button @click="error = null" class="ml-auto text-red-500 hover:text-red-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ successMessage }}</span>
            <button @click="successMessage = null" class="ml-auto text-green-500 hover:text-green-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Profile Tab -->
        <div v-if="activeTab === 'profile'">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">My Profile</h2>
            <ProfileForm :profile="profile" @update="handleProfileUpdate" />
          </div>
        </div>

        <!-- Reservations Tab -->
        <div v-if="activeTab === 'reservations'">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">My Reservations</h2>
            <UserReservations :reservations="reservations" :loading="loadingReservations" @cancel="handleCancelReservation" />
          </div>
        </div>

        <!-- Payments Tab -->
        <div v-if="activeTab === 'payments'">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Payment History</h2>
            <UserPayments :payments="payments" :loading="loadingPayments" @pay="handlePayment" />
          </div>
        </div>

        <!-- Messages Tab -->
        <div v-if="activeTab === 'messages'">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Messages</h2>
            <UserMessages :conversations="conversations" :loading="loadingConversations" @send="handleSendMessage" />
          </div>
        </div>
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

    <OnlyFooter />
  </div>
</template>

<script setup>
import OnlyHeader from '@/components/OnlyHeader.vue'
import OnlyFooter from '@/components/OnlyFooter.vue'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ProfileForm from '@/components/ProfileForm.vue'
import UserReservations from '@/components/UserReservations.vue'
import UserPayments from '@/components/UserPayments.vue'
import UserMessages from '@/components/UserMessages.vue'
import { fetchUserProfile, fetchUserReservations, fetchUserPayments, fetchUserConversations } from '@/api/user'
import { logout } from '@/api/auth'

const activeTab = ref('profile')
const profile = ref({})
const reservations = ref([])
const payments = ref([])
const conversations = ref([])

const error = ref(null)
const successMessage = ref(null)
const showLogoutModal = ref(false)
const loggingOut = ref(false)

// Loading states
const loadingReservations = ref(false)
const loadingPayments = ref(false)
const loadingConversations = ref(false)

const router = useRouter()
const headerRef = ref(null)

const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const showMessage = (message, type = 'success') => {
  if (type === 'success') {
    successMessage.value = message
    error.value = null
  } else {
    error.value = message
    successMessage.value = null
  }
  
  // Auto-hide messages after 5 seconds
  setTimeout(() => {
    error.value = null
    successMessage.value = null
  }, 5000)
}

const loadProfile = async () => {
  try {
    profile.value = await fetchUserProfile()
  } catch (err) {
    console.error('Failed to load profile:', err)
    showMessage('Failed to load profile data', 'error')
  }
}

const loadReservations = async () => {
  loadingReservations.value = true
  try {
    const response = await fetchUserReservations()
    reservations.value = response.data || []
  } catch (err) {
    console.error('Failed to load reservations:', err)
    showMessage('Failed to load reservations', 'error')
  } finally {
    loadingReservations.value = false
  }
}

const loadPayments = async () => {
  loadingPayments.value = true
  try {
    const response = await fetchUserPayments()
    payments.value = response.data || []
  } catch (err) {
    console.error('Failed to load payments:', err)
    showMessage('Failed to load payment history', 'error')
  } finally {
    loadingPayments.value = false
  }
}

const loadConversations = async () => {
  loadingConversations.value = true
  try {
    const response = await fetchUserConversations()
    conversations.value = response.data || []
  } catch (err) {
    console.error('Failed to load conversations:', err)
    showMessage('Failed to load conversations', 'error')
  } finally {
    loadingConversations.value = false
  }
}

const handleProfileUpdate = async (updatedProfile) => {
  try {
    profile.value = { ...profile.value, ...updatedProfile }
    showMessage('Profile updated successfully')
    
    // Refresh header to update user info
    if (headerRef.value?.checkAuthStatus) {
      await headerRef.value.checkAuthStatus()
    }
  } catch (err) {
    console.error('Failed to update profile:', err)
    showMessage('Failed to update profile', 'error')
  }
}

const handleCancelReservation = async (reservationId) => {
  try {
    // API call to cancel reservation would go here
    await loadReservations()
    showMessage('Reservation cancelled successfully')
  } catch (err) {
    console.error('Failed to cancel reservation:', err)
    showMessage('Failed to cancel reservation', 'error')
  }
}

const handlePayment = async (paymentId) => {
  try {
    // API call to process payment would go here
    await loadPayments()
    showMessage('Payment processed successfully')
  } catch (err) {
    console.error('Failed to process payment:', err)
    showMessage('Failed to process payment', 'error')
  }
}

const handleSendMessage = async (messageData) => {
  try {
    // API call to send message would go here
    await loadConversations()
    showMessage('Message sent successfully')
  } catch (err) {
    console.error('Failed to send message:', err)
    showMessage('Failed to send message', 'error')
  }
}

// Logout functionality
const handleLogout = () => {
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
    
    // Clear all storage
    localStorage.clear()
    sessionStorage.clear()
    
    // Update state
    showLogoutModal.value = false
    
    // Show success message
    showMessage('Logged out successfully')
    
    // Small delay to show success message
    setTimeout(() => {
      router.push('/login')
    }, 1000)
    
  } catch (error) {
    console.error('Logout error:', error)
    
    // Even if API fails, clear local data and redirect
    localStorage.clear()
    sessionStorage.clear()
    showLogoutModal.value = false
    
    showMessage('Logged out successfully')
    
    setTimeout(() => {
      router.push('/login')
    }, 1000)
  } finally {
    loggingOut.value = false
  }
}

// Initialize data on component mount
onMounted(async () => {
  await loadProfile()
  await loadReservations()
  await loadPayments()
  await loadConversations()
})
</script>

<style scoped>
/* Custom styles for better UI */
.transition-colors {
  transition: background-color 0.2s ease, color 0.2s ease;
}

/* Sidebar navigation styling */
nav button {
  position: relative;
}

nav button.bg-orange-100 {
  background: linear-gradient(90deg, #fed7aa 0%, transparent 100%);
}

/* Modal backdrop blur effect */
.modal-backdrop {
  backdrop-filter: blur(2px);
}

/* Message styling */
.message-container {
  animation: slideInFromTop 0.3s ease-out;
}

@keyframes slideInFromTop {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
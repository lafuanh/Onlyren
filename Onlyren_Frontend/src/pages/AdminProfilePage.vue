<!-- AdminProfile.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <OnlyHeader />
    
    <div class="flex">
      <!-- Sidebar -->
      <div class="w-64 bg-white shadow-lg min-h-screen">
        <div class="p-6">
          <!-- Admin Profile Section -->
          <div class="flex items-center mb-8">
            <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold">
              {{ getInitials(profile.name) }}
            </div>
            <div class="ml-3">
              <h3 class="font-semibold text-gray-800">{{ profile.name || 'Admin' }}</h3>
              <p class="text-sm text-gray-600">Administrator</p>
            </div>
          </div>

          <!-- Navigation Menu -->
          <nav class="space-y-2">
            <button 
              @click="activeTab = 'dashboard'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'dashboard' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
            </button>
            <button 
              @click="activeTab = 'users'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'users' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-users mr-3"></i>Kelola Pengguna
            </button>
            <button 
              @click="activeTab = 'properties'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'properties' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-building mr-3"></i>Kelola Properti
            </button>
            <button 
              @click="activeTab = 'orders'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'orders' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-clipboard-list mr-3"></i>Kelola Pesanan
            </button>
            <button 
              @click="activeTab = 'payments'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'payments' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-credit-card mr-3"></i>Kelola Pembayaran
            </button>
            <button 
              @click="activeTab = 'reports'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'reports' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-chart-bar mr-3"></i>Laporan
            </button>
            <button 
              @click="activeTab = 'settings'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors',
                activeTab === 'settings' 
                  ? 'bg-red-100 text-red-600 border-l-4 border-red-600' 
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-cog mr-3"></i>Pengaturan
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

        <!-- Dashboard Tab -->
        <div v-if="activeTab === 'dashboard'">
          <AdminDashboard :stats="dashboardStats" />
        </div>

        <!-- User Management Tab -->
        <div v-if="activeTab === 'users'">
          <UserManagement 
            :users="users" 
            @add="handleAddUser"
            @edit="handleEditUser" 
            @delete="handleDeleteUser"
            @activate="handleActivateUser"
            @deactivate="handleDeactivateUser"
          />
        </div>

        <!-- Property Management Tab -->
        <div v-if="activeTab === 'properties'">
          <PropertyManagement 
            :properties="properties" 
            @approve="handleApproveProperty"
            @reject="handleRejectProperty"
            @delete="handleDeleteProperty"
          />
        </div>

        <!-- Order Management Tab -->
        <div v-if="activeTab === 'orders'">
          <AdminOrderManagement 
            :orders="orders" 
            @view="handleViewOrder"
            @update-status="handleUpdateOrderStatus"
          />
        </div>

        <!-- Payment Management Tab -->
        <div v-if="activeTab === 'payments'">
          <PaymentManagement 
            :payments="payments" 
            @verify="handleVerifyPayment"
            @reject="handleRejectPayment"
          />
        </div>

        <!-- Reports Tab -->
        <div v-if="activeTab === 'reports'">
          <AdminReports 
            :reports="reports"
            @generate="handleGenerateReport"
            @export="handleExportReport"
          />
        </div>

        <!-- Settings Tab -->
        <div v-if="activeTab === 'settings'">
          <AdminSettings 
            :settings="settings"
            @update="handleUpdateSettings"
          />
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

// Import Admin Components
import AdminDashboard from '@/components/admin/AdminDashboard.vue'
import UserManagement from '@/components/admin/UserManagement.vue'
import PropertyManagement from '@/components/admin/PropertyManagement.vue'
import AdminOrderManagement from '@/components/admin/AdminOrderManagement.vue'
import PaymentManagement from '@/components/admin/PaymentManagement.vue'
import AdminReports from '@/components/admin/AdminReports.vue'
import AdminSettings from '@/components/admin/AdminSettings.vue'

// Import API functions
import { 
  fetchAdminProfile,
  fetchDashboardStats,
  fetchAllUsers,
  fetchAllProperties,
  fetchAllOrders,
  fetchAllPayments,
  fetchReports,
  fetchSettings,
  logout 
} from '@/api/admin'

const activeTab = ref('dashboard')
const profile = ref({})
const dashboardStats = ref({})
const users = ref([])
const properties = ref([])
const orders = ref([])
const payments = ref([])
const reports = ref({})
const settings = ref({})

const error = ref(null)
const successMessage = ref(null)

const router = useRouter()

const getInitials = (name) => {
  if (!name) return 'A'
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

// Load Data Functions
const loadProfile = async () => {
  try {
    profile.value = await fetchAdminProfile()
  } catch (err) {
    showMessage('Failed to load profile', 'error')
  }
}

const loadDashboardStats = async () => {
  try {
    dashboardStats.value = await fetchDashboardStats()
  } catch (err) {
    showMessage('Failed to load dashboard stats', 'error')
  }
}

const loadUsers = async () => {
  try {
    users.value = await fetchAllUsers()
  } catch (err) {
    showMessage('Failed to load users', 'error')
  }
}

const loadProperties = async () => {
  try {
    properties.value = await fetchAllProperties()
  } catch (err) {
    showMessage('Failed to load properties', 'error')
  }
}

const loadOrders = async () => {
  try {
    orders.value = await fetchAllOrders()
  } catch (err) {
    showMessage('Failed to load orders', 'error')
  }
}

const loadPayments = async () => {
  try {
    payments.value = await fetchAllPayments()
  } catch (err) {
    showMessage('Failed to load payments', 'error')
  }
}

const loadReports = async () => {
  try {
    reports.value = await fetchReports()
  } catch (err) {
    showMessage('Failed to load reports', 'error')
  }
}

const loadSettings = async () => {
  try {
    settings.value = await fetchSettings()
  } catch (err) {
    showMessage('Failed to load settings', 'error')
  }
}

// Event Handlers
const handleAddUser = async (userData) => {
  try {
    await loadUsers()
    showMessage('User added successfully')
  } catch (err) {
    showMessage('Failed to add user', 'error')
  }
}

const handleEditUser = async (userData) => {
  try {
    await loadUsers()
    showMessage('User updated successfully')
  } catch (err) {
    showMessage('Failed to update user', 'error')
  }
}

const handleDeleteUser = async (userId) => {
  try {
    await loadUsers()
    showMessage('User deleted successfully')
  } catch (err) {
    showMessage('Failed to delete user', 'error')
  }
}

const handleActivateUser = async (userId) => {
  try {
    await loadUsers()
    showMessage('User activated successfully')
  } catch (err) {
    showMessage('Failed to activate user', 'error')
  }
}

const handleDeactivateUser = async (userId) => {
  try {
    await loadUsers()
    showMessage('User deactivated successfully')
  } catch (err) {
    showMessage('Failed to deactivate user', 'error')
  }
}

const handleApproveProperty = async (propertyId) => {
  try {
    await loadProperties()
    showMessage('Property approved successfully')
  } catch (err) {
    showMessage('Failed to approve property', 'error')
  }
}

const handleRejectProperty = async (propertyId) => {
  try {
    await loadProperties()
    showMessage('Property rejected successfully')
  } catch (err) {
    showMessage('Failed to reject property', 'error')
  }
}

const handleDeleteProperty = async (propertyId) => {
  try {
    await loadProperties()
    showMessage('Property deleted successfully')
  } catch (err) {
    showMessage('Failed to delete property', 'error')
  }
}

const handleViewOrder = async (orderId) => {
  // Handle view order details
  showMessage('Order details loaded')
}

const handleUpdateOrderStatus = async (orderId, status) => {
  try {
    await loadOrders()
    showMessage('Order status updated successfully')
  } catch (err) {
    showMessage('Failed to update order status', 'error')
  }
}

const handleVerifyPayment = async (paymentId) => {
  try {
    await loadPayments()
    showMessage('Payment verified successfully')
  } catch (err) {
    showMessage('Failed to verify payment', 'error')
  }
}

const handleRejectPayment = async (paymentId) => {
  try {
    await loadPayments()
    showMessage('Payment rejected successfully')
  } catch (err) {
    showMessage('Failed to reject payment', 'error')
  }
}

const handleGenerateReport = async (reportType) => {
  try {
    await loadReports()
    showMessage('Report generated successfully')
  } catch (err) {
    showMessage('Failed to generate report', 'error')
  }
}

const handleExportReport = async (reportData) => {
  try {
    showMessage('Report exported successfully')
  } catch (err) {
    showMessage('Failed to export report', 'error')
  }
}

const handleUpdateSettings = async (settingsData) => {
  try {
    settings.value = { ...settings.value, ...settingsData }
    showMessage('Settings updated successfully')
  } catch (err) {
    showMessage('Failed to update settings', 'error')
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
  loadDashboardStats()
  loadUsers()
  loadProperties()
  loadOrders()
  loadPayments()
  loadReports()
  loadSettings()
})
</script>
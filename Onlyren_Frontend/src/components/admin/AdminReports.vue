<!-- AdminReports.vue -->
<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Laporan</h2>
      <div class="flex space-x-4">
        <select v-model="selectedPeriod" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
          <option value="daily">Harian</option>
          <option value="weekly">Mingguan</option>
          <option value="monthly">Bulanan</option>
          <option value="yearly">Tahunan</option>
        </select>
        <input 
          v-model="dateRange.start" 
          type="date" 
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
        >
        <input 
          v-model="dateRange.end" 
          type="date" 
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
        >
        <button 
          @click="generateReport"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
        >
          <i class="fas fa-chart-line mr-2"></i>Generate
        </button>
      </div>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-100 text-sm">Total Revenue</p>
            <p class="text-2xl font-bold">{{ formatCurrency(reports.totalRevenue || 0) }}</p>
            <p class="text-blue-100 text-sm mt-1">
              <i class="fas fa-arrow-up mr-1"></i>+12% dari periode sebelumnya
            </p>
          </div>
          <i class="fas fa-dollar-sign text-3xl text-blue-200"></i>
        </div>
      </div>

      <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm">Total Orders</p>
            <p class="text-2xl font-bold">{{ reports.totalOrders || 0 }}</p>
            <p class="text-green-100 text-sm mt-1">
              <i class="fas fa-arrow-up mr-1"></i>+8% dari periode sebelumnya
            </p>
          </div>
          <i class="fas fa-shopping-cart text-3xl text-green-200"></i>
        </div>
      </div>

      <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-100 text-sm">Active Properties</p>
            <p class="text-2xl font-bold">{{ reports.activeProperties || 0 }}</p>
            <p class="text-purple-100 text-sm mt-1">
              <i class="fas fa-arrow-up mr-1"></i>+15% dari periode sebelumnya
            </p>
          </div>
          <i class="fas fa-building text-3xl text-purple-200"></i>
        </div>
      </div>

      <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-lg text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-orange-100 text-sm">New Customers</p>
            <p class="text-2xl font-bold">{{ reports.newCustomers || 0 }}</p>
            <p class="text-orange-100 text-sm mt-1">
              <i class="fas fa-arrow-up mr-1"></i>+25% dari periode sebelumnya
            </p>
          </div>
          <i class="fas fa-users text-3xl text-orange-200"></i>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Revenue Chart -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Trend</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
          <div class="text-center">
            <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
            <p class="text-gray-500">Revenue chart akan ditampilkan di sini</p>
          </div>
        </div>
      </div>

      <!-- Orders Chart -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Status Distribution</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
          <div class="text-center">
            <i class="fas fa-chart-pie text-4xl text-gray-400 mb-2"></i>
            <p class="text-gray-500">Order status chart akan ditampilkan di sini</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Reports Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Properties -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Top Properties</h3>
          <button 
            @click="exportReport('top-properties')"
            class="text-sm text-red-600 hover:text-red-800"
          >
            <i class="fas fa-download mr-1"></i>Export
          </button>
        </div>
        <div class="space-y-4">
          <div v-for="(property, index) in topProperties" :key="property.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">
                {{ index + 1 }}
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ property.title }}</p>
                <p class="text-sm text-gray-500">{{ property.location }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-semibold text-gray-900">{{ property.orders }} orders</p>
              <p class="text-sm text-gray-500">{{ formatCurrency(property.revenue) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
          <button 
            @click="exportReport('activities')"
            class="text-sm text-red-600 hover:text-red-800"
          >
            <i class="fas fa-download mr-1"></i>Export
          </button>
        </div>
        <div class="space-y-4">
          <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start space-x-3">
            <div :class="getActivityIconClass(activity.type)" class="w-8 h-8 rounded-full flex items-center justify-center text-sm">
              <i :class="getActivityIcon(activity.type)"></i>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
              <p class="text-sm text-gray-500">{{ activity.description }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(activity.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Export Reports</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button 
          @click="exportReport('revenue')"
          class="flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-file-excel text-green-600 mr-2"></i>
          <span>Revenue Report (Excel)</span>
        </button>
        <button 
          @click="exportReport('orders')"
          class="flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-file-pdf text-red-600 mr-2"></i>
          <span>Orders Report (PDF)</span>
        </button>
        <button 
          @click="exportReport('customers')"
          class="flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-file-csv text-blue-600 mr-2"></i>
          <span>Customers Report (CSV)</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits, onMounted } from 'vue'

const props = defineProps({
  reports: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['generate', 'export'])

const selectedPeriod = ref('monthly')
const dateRange = ref({
  start: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
})

const topProperties = ref([
  {
    id: 1,
    title: 'Villa Sunset Paradise',
    location: 'Bali',
    orders: 45,
    revenue: 150000000
  },
  {
    id: 2,
    title: 'Mountain View Resort',
    location: 'Bandung',
    orders: 38,
    revenue: 120000000
  },
  {
    id: 3,
    title: 'Beach House Anyer',
    location: 'Anyer',
    orders: 32,
    revenue: 95000000
  },
  {
    id: 4,
    title: 'City Center Apartment',
    location: 'Jakarta',
    orders: 28,
    revenue: 85000000
  },
  {
    id: 5,
    title: 'Cozy Cabin Bogor',
    location: 'Bogor',
    orders: 25,
    revenue: 75000000
  }
])

const recentActivities = ref([
  {
    id: 1,
    type: 'order',
    title: 'New Order Received',
    description: 'Order #12345 from John Doe for Villa Sunset Paradise',
    created_at: new Date().toISOString()
  },
  {
    id: 2,
    type: 'payment',
    title: 'Payment Verified',
    description: 'Payment for Order #12344 has been verified',
    created_at: new Date(Date.now() - 3600000).toISOString()
  },
  {
    id: 3,
    type: 'property',
    title: 'New Property Listed',
    description: 'Ocean View Villa has been approved and listed',
    created_at: new Date(Date.now() - 7200000).toISOString()
  },
  {
    id: 4,
    type: 'user',
    title: 'New User Registration',
    description: 'Jane Smith has registered as a new customer',
    created_at: new Date(Date.now() - 10800000).toISOString()
  },
  {
    id: 5,
    type: 'review',
    title: 'New Review Posted',
    description: '5-star review posted for Mountain View Resort',
    created_at: new Date(Date.now() - 14400000).toISOString()
  }
])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getActivityIcon = (type) => {
  const icons = {
    'order': 'fas fa-shopping-cart',
    'payment': 'fas fa-credit-card',
    'property': 'fas fa-building',
    'user': 'fas fa-user-plus',
    'review': 'fas fa-star'
  }
  return icons[type] || 'fas fa-circle'
}

const getActivityIconClass = (type) => {
  const classes = {
    'order': 'bg-blue-100 text-blue-600',
    'payment': 'bg-green-100 text-green-600',
    'property': 'bg-purple-100 text-purple-600',
    'user': 'bg-orange-100 text-orange-600',
    'review': 'bg-yellow-100 text-yellow-600'
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

const generateReport = () => {
  const reportData = {
    period: selectedPeriod.value,
    dateRange: dateRange.value
  }
  emit('generate', reportData)
}

const exportReport = (type) => {
  const exportData = {
    type,
    period: selectedPeriod.value,
    dateRange: dateRange.value
  }
  emit('export', exportData)
}

onMounted(() => {
  generateReport()
})
</script>
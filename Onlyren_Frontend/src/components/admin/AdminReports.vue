<!-- AdminReports.vue -->
<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="text-gray-600">View detailed reports and analytics</p>
      </div>
      
      <!-- Date Range Filter -->
      <div class="flex space-x-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input 
            v-model="filters.start_date" 
            type="date" 
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            @change="loadReports"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input 
            v-model="filters.end_date" 
            type="date" 
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            @change="loadReports"
          />
        </div>
        <div class="flex items-end">
          <button 
            @click="loadReports"
            :disabled="loading"
            class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'Apply Filter' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
      <p class="text-gray-600">Loading reports...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      {{ error }}
    </div>

    <!-- Reports Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Reservation Report -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservation Report</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Reservations</span>
            <span class="font-semibold">{{ reports.reservations?.summary?.total_reservations || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Pending</span>
            <span class="text-yellow-600 font-semibold">{{ reports.reservations?.summary?.pending_reservations || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Confirmed</span>
            <span class="text-blue-600 font-semibold">{{ reports.reservations?.summary?.confirmed_reservations || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Completed</span>
            <span class="text-green-600 font-semibold">{{ reports.reservations?.summary?.completed_reservations || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Cancelled</span>
            <span class="text-red-600 font-semibold">{{ reports.reservations?.summary?.cancelled_reservations || 0 }}</span>
          </div>
          <div class="flex justify-between items-center border-t pt-2">
            <span class="text-gray-600 font-medium">Total Revenue</span>
            <span class="font-semibold text-green-600">{{ formatCurrency(reports.reservations?.summary?.total_revenue || 0) }}</span>
          </div>
        </div>
      </div>

      <!-- Payment Report -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Report</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Payments</span>
            <span class="font-semibold">{{ reports.payments?.summary?.total_payments || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Pending Payments</span>
            <span class="text-yellow-600 font-semibold">{{ reports.payments?.summary?.pending_payments || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Successful Payments</span>
            <span class="text-green-600 font-semibold">{{ reports.payments?.summary?.paid_payments || 0 }}</span>
          </div>
          <div class="flex justify-between items-center border-t pt-2">
            <span class="text-gray-600 font-medium">Total Revenue</span>
            <span class="font-semibold text-green-600">{{ formatCurrency(reports.payments?.summary?.total_revenue || 0) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Reports Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Reservations -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Reservations</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="reservation in reports.reservations?.reservations?.slice(0, 5)" :key="reservation.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ reservation.room?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ reservation.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(reservation.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ reservation.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(reservation.total_amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Payments -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Payments</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payment in reports.payments?.payments?.slice(0, 5)" :key="payment.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ payment.reservation?.room?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ payment.reservation?.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ payment.method || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(payment.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Reports</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button 
          @click="exportReport('reservations')"
          class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-file-excel text-green-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Export Reservations</p>
        </button>
        <button 
          @click="exportReport('payments')"
          class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-file-pdf text-red-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Export Payments</p>
        </button>
        <button 
          @click="exportReport('analytics')"
          class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <i class="fas fa-chart-line text-blue-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Export Analytics</p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getReservationReport, getPaymentReport } from '@/api/admin'

const loading = ref(false)
const error = ref(null)
const reports = ref({
  reservations: null,
  payments: null
})

const filters = ref({
  start_date: '',
  end_date: ''
})

const loadReports = async () => {
  loading.value = true
  error.value = null
  
  try {
    const params = {}
    if (filters.value.start_date) params.start_date = filters.value.start_date
    if (filters.value.end_date) params.end_date = filters.value.end_date
    
    const [reservationData, paymentData] = await Promise.all([
      getReservationReport(params),
      getPaymentReport(params)
    ])
    
    reports.value = {
      reservations: reservationData.data,
      payments: paymentData.data
    }
  } catch (err) {
    error.value = err.message || 'Failed to load reports'
    console.error('Error loading reports:', err)
  } finally {
    loading.value = false
  }
}

const exportReport = (type) => {
  // TODO: Implement export functionality
  console.log(`Exporting ${type} report...`)
  alert(`Export ${type} report functionality will be implemented soon!`)
}

const formatCurrency = (value) => {
  if (typeof value !== 'number') return 'Rp 0'
  return new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
  }).format(value)
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'Payment': 'bg-blue-100 text-blue-800',
    'Completed': 'bg-green-100 text-green-800',
    'Cancelled': 'bg-red-100 text-red-800',
    'paid': 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  loadReports()
})
</script>
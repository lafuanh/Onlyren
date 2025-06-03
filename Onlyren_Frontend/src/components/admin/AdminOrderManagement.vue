<!-- AdminOrderManagement.vue -->
<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Kelola Pesanan</h2>
      <div class="flex space-x-4">
        <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="processing">Processing</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari pesanan..." 
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
        >
      </div>
    </div>

    <!-- Orders Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
        <div class="flex items-center">
          <i class="fas fa-clock text-blue-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-blue-600">Pending</p>
            <p class="text-xl font-bold text-blue-700">{{ getOrderCountByStatus('pending') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
        <div class="flex items-center">
          <i class="fas fa-check-circle text-yellow-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-yellow-600">Confirmed</p>
            <p class="text-xl font-bold text-yellow-700">{{ getOrderCountByStatus('confirmed') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-500">
        <div class="flex items-center">
          <i class="fas fa-cogs text-purple-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-purple-600">Processing</p>
            <p class="text-xl font-bold text-purple-700">{{ getOrderCountByStatus('processing') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
        <div class="flex items-center">
          <i class="fas fa-check-double text-green-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-green-600">Completed</p>
            <p class="text-xl font-bold text-green-700">{{ getOrderCountByStatus('completed') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
        <div class="flex items-center">
          <i class="fas fa-times-circle text-red-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-red-600">Cancelled</p>
            <p class="text-xl font-bold text-red-700">{{ getOrderCountByStatus('cancelled') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Properti</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              #{{ order.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium text-gray-700 mr-3">
                  {{ getInitials(order.customer.name) }}
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ order.customer.name }}</div>
                  <div class="text-sm text-gray-500">{{ order.customer.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ order.property.title }}</div>
              <div class="text-sm text-gray-500">{{ order.property.location }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatDate(order.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ formatCurrency(order.total_amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(order.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ getStatusText(order.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button 
                @click="$emit('view', order.id)"
                class="text-blue-600 hover:text-blue-900"
              >
                <i class="fas fa-eye"></i>
              </button>
              <select 
                :value="order.status"
                @change="updateOrderStatus(order.id, $event.target.value)"
                class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-red-500 focus:border-transparent"
              >
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="filteredOrders.length === 0" class="text-center py-12">
      <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada pesanan</h3>
      <p class="text-gray-500">Belum ada pesanan yang sesuai dengan filter Anda.</p>
    </div>

    <!-- Pagination -->
    <div v-if="filteredOrders.length > 0" class="flex items-center justify-between mt-6">
      <div class="flex items-center text-sm text-gray-700">
        Menampilkan {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredOrders.length) }} 
        dari {{ filteredOrders.length }} pesanan
      </div>
      <div class="flex space-x-2">
        <button 
          @click="currentPage--" 
          :disabled="currentPage <= 1"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button 
          @click="currentPage++" 
          :disabled="currentPage >= totalPages"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  orders: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['view', 'update-status'])

const searchQuery = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredOrders = computed(() => {
  let filtered = props.orders

  if (searchQuery.value) {
    filtered = filtered.filter(order => 
      order.id.toString().includes(searchQuery.value.toLowerCase()) ||
      order.customer.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      order.customer.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      order.property.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (filterStatus.value) {
    filtered = filtered.filter(order => order.status === filterStatus.value)
  }

  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filtered.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(props.orders.length / itemsPerPage.value)
})

const getOrderCountByStatus = (status) => {
  return props.orders.filter(order => order.status === status).length
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(amount)
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-blue-100 text-blue-800',
    'confirmed': 'bg-yellow-100 text-yellow-800',
    'processing': 'bg-purple-100 text-purple-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    'pending': 'Pending',
    'confirmed': 'Dikonfirmasi',
    'processing': 'Diproses',
    'completed': 'Selesai',
    'cancelled': 'Dibatalkan'
  }
  return texts[status] || status
}

const updateOrderStatus = (orderId, newStatus) => {
  emit('update-status', orderId, newStatus)
}
</script>
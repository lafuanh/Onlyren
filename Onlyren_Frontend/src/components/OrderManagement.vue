<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Pesanan</h2>
    
    <!-- Filter Tabs -->
    <div class="flex space-x-4 mb-6">
      <button 
        v-for="status in statusTabs"
        :key="status.value"
        @click="activeStatus = status.value"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-colors',
          activeStatus === status.value 
            ? 'bg-blue-100 text-blue-600' 
            : 'text-gray-600 hover:bg-gray-100'
        ]"
      >
        {{ status.label }}
        <span 
          v-if="getOrderCount(status.value) > 0"
          class="ml-2 px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs"
        >
          {{ getOrderCount(status.value) }}
        </span>
      </button>
    </div>
    
    <div v-if="filteredOrders.length === 0" class="text-center py-8">
      <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
      <p class="text-gray-500">Belum ada pesanan {{ getStatusLabel(activeStatus) }}</p>
    </div>
    
    <div v-else class="space-y-4">
      <div 
        v-for="order in filteredOrders" 
        :key="order.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold text-gray-800">{{ order.roomName }}</h3>
            <p class="text-sm text-gray-600">Pesanan #{{ order.id }}</p>
            <p class="text-sm text-gray-600">Oleh: {{ order.userName }}</p>
          </div>
          <span 
            :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              getStatusClass(order.status)
            ]"
          >
            {{ getStatusText(order.status) }}
          </span>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
          <div>
            <span class="text-gray-500">Tanggal:</span>
            <p class="font-medium">{{ formatDate(order.date) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Waktu:</span>
            <p class="font-medium">{{ order.startTime }} - {{ order.endTime }}</p>
          </div>
          <div>
            <span class="text-gray-500">Durasi:</span>
            <p class="font-medium">{{ order.duration }} jam</p>
          </div>
          <div>
            <span class="text-gray-500">Total:</span>
            <p class="font-medium text-blue-600">Rp {{ formatPrice(order.totalPrice) }}</p>
          </div>
        </div>
        
        <div v-if="order.notes" class="mb-4">
          <span class="text-gray-500 text-sm">Catatan:</span>
          <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded mt-1">{{ order.notes }}</p>
        </div>
        
        <div class="flex justify-end space-x-2">
          <button 
            v-if="order.status === 'pending'"
            @click="approveOrder(order.id)"
            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
          >
            <i class="fas fa-check mr-1"></i>Setujui
          </button>
          <button 
            v-if="order.status === 'pending'"
            @click="rejectOrder(order.id)"
            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
          >
            <i class="fas fa-times mr-1"></i>Tolak
          </button>
          <button 
            v-if="order.status === 'approved'"
            @click="completeOrder(order.id)"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
          >
            <i class="fas fa-flag-checkered mr-1"></i>Selesaikan
          </button>
          <button 
            @click="viewOrderDetail(order)"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            <i class="fas fa-eye mr-1"></i>Detail
          </button>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="showDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Detail Pesanan #{{ selectedOrder?.id }}</h3>
            <button @click="showDetailModal = false" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div v-if="selectedOrder" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nama Pemesan</label>
                <p class="text-gray-900">{{ selectedOrder.userName }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <p class="text-gray-900">{{ selectedOrder.userEmail }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                <p class="text-gray-900">{{ selectedOrder.userPhone }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(selectedOrder.status)]">
                  {{ getStatusText(selectedOrder.status) }}
                </span>
              </div>
            </div>
            
            <div class="border-t pt-4">
              <h4 class="font-medium text-gray-800 mb-2">Detail Booking</h4>
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-gray-500">Ruangan:</span>
                    <p class="font-medium">{{ selectedOrder.roomName }}</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Tanggal:</span>
                    <p class="font-medium">{{ formatDate(selectedOrder.date) }}</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Waktu:</span>
                    <p class="font-medium">{{ selectedOrder.startTime }} - {{ selectedOrder.endTime }}</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Durasi:</span>
                    <p class="font-medium">{{ selectedOrder.duration }} jam</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Harga per jam:</span>
                    <p class="font-medium">Rp {{ formatPrice(selectedOrder.pricePerHour) }}</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Total:</span>
                    <p class="font-medium text-blue-600">Rp {{ formatPrice(selectedOrder.totalPrice) }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="selectedOrder.notes" class="border-t pt-4">
              <h4 class="font-medium text-gray-800 mb-2">Catatan</h4>
              <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ selectedOrder.notes }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  orders: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['approve', 'reject', 'complete'])

const activeStatus = ref('all')
const showDetailModal = ref(false)
const selectedOrder = ref(null)

const statusTabs = [
  { value: 'all', label: 'Semua' },
  { value: 'pending', label: 'Menunggu' },
  { value: 'approved', label: 'Disetujui' },
  { value: 'rejected', label: 'Ditolak' },
  { value: 'completed', label: 'Selesai' }
]

const filteredOrders = computed(() => {
  if (activeStatus.value === 'all') return props.orders
  return props.orders.filter(order => order.status === activeStatus.value)
})

const getOrderCount = (status) => {
  if (status === 'all') return props.orders.length
  return props.orders.filter(order => order.status === status).length
}

const getStatusLabel = (status) => {
  const labels = {
    all: '',
    pending: 'menunggu',
    approved: 'disetujui', 
    rejected: 'ditolak',
    completed: 'selesai'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    completed: 'Selesai'
  }
  return texts[status] || status
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID')
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const approveOrder = (orderId) => {
  emit('approve', orderId)
}

const rejectOrder = (orderId) => {
  if (confirm('Apakah Anda yakin ingin menolak pesanan ini?')) {
    emit('reject', orderId)
  }
}

const completeOrder = (orderId) => {
  if (confirm('Apakah Anda yakin pesanan ini sudah selesai?')) {
    emit('complete', orderId)
  }
}

const viewOrderDetail = (order) => {
  selectedOrder.value = order
  showDetailModal.value = true
}
</script>
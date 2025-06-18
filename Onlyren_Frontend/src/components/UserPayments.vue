<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Pembayaran</h2>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
      <p class="text-gray-500">Memuat riwayat pembayaran...</p>
    </div>
    
    <!-- Empty State -->
    <div v-else-if="payments.length === 0" class="text-center py-8">
      <i class="fas fa-credit-card text-gray-400 text-4xl mb-4"></i>
      <p class="text-gray-500">Belum ada riwayat pembayaran</p>
    </div>
    
    <!-- Payments List -->
    <div v-else class="space-y-4">
      <div 
        v-for="payment in payments" 
        :key="payment.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">{{ payment.reservation?.room?.name || 'Reservasi' }}</h3>
            <p class="text-sm text-gray-600">Reservasi #{{ payment.reservation?.id || payment.reservation_id }}</p>
            <p class="text-xs text-gray-500">ID Transaksi: {{ payment.transaction_id }}</p>
          </div>
          <span 
            :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              getPaymentStatusClass(payment.status)
            ]"
          >
            {{ getPaymentStatusText(payment.status) }}
          </span>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
          <div>
            <span class="text-gray-500">Jumlah:</span>
            <p class="font-medium text-lg text-orange-600">Rp {{ formatPrice(payment.amount) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Tanggal:</span>
            <p class="font-medium">{{ formatDate(payment.created_at) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Metode:</span>
            <p class="font-medium">{{ payment.method || 'Belum dipilih' }}</p>
          </div>
          <div>
            <span class="text-gray-500">Status:</span>
            <p class="font-medium">{{ getPaymentStatusText(payment.status) }}</p>
          </div>
        </div>
        
        <div class="flex justify-end space-x-2">
          <button 
            v-if="payment.status === 'pending'"
            @click="processPayment(payment.id)"
            :disabled="processing === payment.id"
            class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50"
          >
            {{ processing === payment.id ? 'Memproses...' : 'Bayar Sekarang' }}
          </button>
          <button 
            @click="viewDetails(payment.id)"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            Detail
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  payments: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['pay'])
const router = useRouter()
const processing = ref(null)

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusText = (status) => {
  const texts = {
    pending: 'Belum Dibayar',
    paid: 'Sudah Dibayar',
    failed: 'Gagal',
    cancelled: 'Dibatalkan'
  }
  return texts[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('id-ID').format(price)
}

const processPayment = async (id) => {
  processing.value = id
  try {
    await emit('pay', id)
  } finally {
    processing.value = null
  }
}

const viewDetails = (paymentId) => {
  router.push(`/payments/${paymentId}`)
}
</script>
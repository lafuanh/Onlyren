<!-- UserReservations.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Reservasi Saya</h2>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
      <p class="text-gray-500">Memuat reservasi...</p>
    </div>
    
    <!-- Empty State -->
    <div v-else-if="reservations.length === 0" class="text-center py-8">
      <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
      <p class="text-gray-500">Belum ada reservasi</p>
    </div>
    
    <!-- Reservations List -->
    <div v-else class="space-y-4">
      <div 
        v-for="reservation in reservations" 
        :key="reservation.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">{{ reservation.room?.name || 'Room' }}</h3>
            <p class="text-sm text-gray-600">{{ reservation.room?.location || 'Location not specified' }}</p>
          </div>
          <span 
            :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              getStatusClass(reservation.status)
            ]"
          >
            {{ getStatusText(reservation.status) }}
          </span>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="text-gray-500">Tanggal:</span>
            <p class="font-medium">{{ formatDate(reservation.start_date) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Waktu:</span>
            <p class="font-medium">{{ formatTime(reservation.start_time) }} - {{ formatTime(reservation.end_time) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Durasi:</span>
            <p class="font-medium">{{ reservation.duration }} jam</p>
          </div>
          <div>
            <span class="text-gray-500">Total:</span>
            <p class="font-medium text-orange-600">Rp {{ formatPrice(reservation.total_amount) }}</p>
          </div>
        </div>
        
        <div class="flex justify-end mt-4 space-x-2">
          <button 
            v-if="reservation.status === 'pending'"
            @click="cancelReservation(reservation.id)"
            :disabled="cancelling === reservation.id"
            class="px-4 py-2 text-red-600 border border-red-600 rounded-lg hover:bg-red-50 transition-colors disabled:opacity-50"
          >
            {{ cancelling === reservation.id ? 'Membatalkan...' : 'Batalkan' }}
          </button>
          <button 
            v-if="reservation.status === 'pending' && reservation.payment"
            @click="goToPayment(reservation.id)"
            class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
          >
            Bayar
          </button>
          <button 
            @click="viewDetails(reservation.id)"
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
  reservations: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['cancel'])
const router = useRouter()
const cancelling = ref(null)

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    Payment: 'bg-blue-100 text-blue-800',
    Completed: 'bg-green-100 text-green-800',
    Cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Menunggu',
    Payment: 'Menunggu Pembayaran',
    Completed: 'Selesai',
    Cancelled: 'Dibatalkan'
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

const formatTime = (timeString) => {
  if (!timeString) return 'N/A'
  // Remove seconds if present
  return timeString.substring(0, 5)
}

const formatPrice = (price) => {
  if (!price) return '0'
  return new Intl.NumberFormat('id-ID').format(price)
}

const cancelReservation = async (id) => {
  if (confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')) {
    cancelling.value = id
    try {
      await emit('cancel', id)
    } finally {
      cancelling.value = null
    }
  }
}

const goToPayment = (reservationId) => {
  router.push(`/payments/${reservationId}`)
}

const viewDetails = (reservationId) => {
  router.push(`/reservations/${reservationId}`)
}
</script>
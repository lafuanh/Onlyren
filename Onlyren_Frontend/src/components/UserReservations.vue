<!-- UserReservations.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Reservasi Saya</h2>
    
    <div v-if="reservations.length === 0" class="text-center py-8">
      <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
      <p class="text-gray-500">Belum ada reservasi</p>
    </div>
    
    <div v-else class="space-y-4">
      <div 
        v-for="reservation in reservations" 
        :key="reservation.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">{{ reservation.roomName }}</h3>
            <p class="text-sm text-gray-600">{{ reservation.location }}</p>
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
            <p class="font-medium">{{ formatDate(reservation.date) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Waktu:</span>
            <p class="font-medium">{{ reservation.startTime }} - {{ reservation.endTime }}</p>
          </div>
          <div>
            <span class="text-gray-500">Durasi:</span>
            <p class="font-medium">{{ reservation.duration }} jam</p>
          </div>
          <div>
            <span class="text-gray-500">Total:</span>
            <p class="font-medium text-orange-600">Rp {{ formatPrice(reservation.totalPrice) }}</p>
          </div>
        </div>
        
        <div class="flex justify-end mt-4 space-x-2">
          <button 
            v-if="reservation.status === 'pending'"
            @click="cancelReservation(reservation.id)"
            class="px-4 py-2 text-red-600 border border-red-600 rounded-lg hover:bg-red-50 transition-colors"
          >
            Batalkan
          </button>
          <button 
            class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
          >
            Detail
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  reservations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['cancel'])

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Menunggu',
    confirmed: 'Dikonfirmasi',
    cancelled: 'Dibatalkan',
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

const cancelReservation = (id) => {
  if (confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')) {
    emit('cancel', id)
  }
}
</script>
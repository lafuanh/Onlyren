<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Pembayaran</h2>
    
    <div v-if="payments.length === 0" class="text-center py-8">
      <i class="fas fa-credit-card text-gray-400 text-4xl mb-4"></i>
      <p class="text-gray-500">Belum ada riwayat pembayaran</p>
    </div>
    
    <div v-else class="space-y-4">
      <div 
        v-for="payment in payments" 
        :key="payment.id"
        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-semibold text-gray-800">{{ payment.description }}</h3>
            <p class="text-sm text-gray-600">Reservasi #{{ payment.reservationId }}</p>
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
            <p class="font-medium">{{ formatDate(payment.createdAt) }}</p>
          </div>
          <div>
            <span class="text-gray-500">Metode:</span>
            <p class="font-medium">{{ payment.paymentMethod }}</p>
          </div>
          <div>
            <span class="text-gray-500">Jatuh Tempo:</span>
            <p class="font-medium">{{ formatDate(payment.dueDate) }}</p>
          </div>
        </div>
        
        <div class="flex justify-end space-x-2">
          <button 
            v-if="payment.status === 'pending'"
            @click="processPayment(payment.id)"
            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
          >
            Bayar Sekarang
          </button>
          <button 
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
const props = defineProps({
  payments: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['pay'])

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    expired: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusText = (status) => {
  const texts = {
    pending: 'Belum Dibayar',
    paid: 'Sudah Dibayar',
    failed: 'Gagal',
    expired: 'Kadaluarsa'
  }
  return texts[status] || status
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID')
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const processPayment = (id) => {
  emit('pay', id)
}
</script>
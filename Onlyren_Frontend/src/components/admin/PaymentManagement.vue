<!-- PaymentManagement.vue -->
<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Kelola Pembayaran</h2>
      <div class="flex space-x-4">
        <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="verified">Verified</option>
          <option value="rejected">Rejected</option>
          <option value="expired">Expired</option>
        </select>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari pembayaran..." 
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
        >
      </div>
    </div>

    <!-- Payment Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
        <div class="flex items-center">
          <i class="fas fa-clock text-yellow-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-yellow-600">Pending</p>
            <p class="text-xl font-bold text-yellow-700">{{ getPaymentCountByStatus('pending') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
        <div class="flex items-center">
          <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-green-600">Verified</p>
            <p class="text-xl font-bold text-green-700">{{ getPaymentCountByStatus('verified') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
        <div class="flex items-center">
          <i class="fas fa-times-circle text-red-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-red-600">Rejected</p>
            <p class="text-xl font-bold text-red-700">{{ getPaymentCountByStatus('rejected') }}</p>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-gray-500">
        <div class="flex items-center">
          <i class="fas fa-hourglass-end text-gray-500 text-xl mr-3"></i>
          <div>
            <p class="text-sm text-gray-600">Expired</p>
            <p class="text-xl font-bold text-gray-700">{{ getPaymentCountByStatus('expired') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pembayaran</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              #{{ payment.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm font-medium text-gray-700 mr-3">
                  {{ getInitials(payment.customer.name) }}
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ payment.customer.name }}</div>
                  <div class="text-sm text-gray-500">{{ payment.customer.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              #{{ payment.order_id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <i :class="getPaymentMethodIcon(payment.method)" class="mr-2"></i>
                <span class="text-sm text-gray-900">{{ payment.method }}</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ formatCurrency(payment.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatDate(payment.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(payment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ getStatusText(payment.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
              <button 
                v-if="payment.proof_image"
                @click="showProofModal(payment)"
                class="text-blue-600 hover:text-blue-900"
                title="Lihat Bukti"
              >
                <i class="fas fa-image"></i>
              </button>
              <button 
                v-if="payment.status === 'pending'"
                @click="$emit('verify', payment.id)"
                class="text-green-600 hover:text-green-900"
                title="Verifikasi"
              >
                <i class="fas fa-check"></i>
              </button>
              <button 
                v-if="payment.status === 'pending'"
                @click="$emit('reject', payment.id)"
                class="text-red-600 hover:text-red-900"
                title="Tolak"
              >
                <i class="fas fa-times"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="filteredPayments.length === 0" class="text-center py-12">
      <i class="fas fa-credit-card text-gray-400 text-4xl mb-4"></i>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada pembayaran</h3>
      <p class="text-gray-500">Belum ada pembayaran yang sesuai dengan filter Anda.</p>
    </div>

    <!-- Pagination -->
    <div v-if="filteredPayments.length > 0" class="flex items-center justify-between mt-6">
      <div class="flex items-center text-sm text-gray-700">
        Menampilkan {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredPayments.length) }} 
        dari {{ filteredPayments.length }} pembayaran
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

    <!-- Proof Modal -->
    <div v-if="showProof" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-900">Bukti Pembayaran</h3>
          <button 
            @click="closeProofModal"
            class="text-gray-400 hover:text-gray-600"
          >
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">Payment ID: #{{ selectedPayment?.id }}</p>
          <p class="text-sm text-gray-600 mb-2">Customer: {{ selectedPayment?.customer.name }}</p>
          <p class="text-sm text-gray-600 mb-4">Amount: {{ formatCurrency(selectedPayment?.amount) }}</p>
        </div>
        <div class="text-center">
          <img 
            :src="selectedPayment?.proof_image" 
            :alt="`Bukti pembayaran #${selectedPayment?.id}`"
            class="max-w-full h-auto rounded-lg shadow-md mx-auto"
          >
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            @click="$emit('reject', selectedPayment.id); closeProofModal()"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            <i class="fas fa-times mr-2"></i>Tolak
          </button>
          <button 
            @click="$emit('verify', selectedPayment.id); closeProofModal()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            <i class="fas fa-check mr-2"></i>Verifikasi
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  payments: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['verify', 'reject'])

const searchQuery = ref('')
const filterStatus = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const showProof = ref(false)
const selectedPayment = ref(null)

const filteredPayments = computed(() => {
  let filtered = props.payments

  if (searchQuery.value) {
    filtered = filtered.filter(payment => 
      payment.id.toString().includes(searchQuery.value.toLowerCase()) ||
      payment.order_id.toString().includes(searchQuery.value.toLowerCase()) ||
      payment.customer.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      payment.customer.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (filterStatus.value) {
    filtered = filtered.filter(payment => payment.status === filterStatus.value)
  }

  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filtered.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(props.payments.length / itemsPerPage.value)
})

const getPaymentCountByStatus = (status) => {
  return props.payments.filter(payment => payment.status === status).length
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
    'pending': 'bg-yellow-100 text-yellow-800',
    'verified': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800',
    'expired': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    'pending': 'Pending',
    'verified': 'Terverifikasi',
    'rejected': 'Ditolak',
    'expired': 'Kedaluwarsa'
  }
  return texts[status] || status
}

const getPaymentMethodIcon = (method) => {
  const icons = {
    'Bank Transfer': 'fas fa-university text-blue-600',
    'Credit Card': 'fas fa-credit-card text-green-600',
    'E-Wallet': 'fas fa-mobile-alt text-purple-600',
    'Cash': 'fas fa-money-bill text-yellow-600'
  }
  return icons[method] || 'fas fa-credit-card text-gray-600'
}

const showProofModal = (payment) => {
  selectedPayment.value = payment
  showProof.value = true
}

const closeProofModal = () => {
  showProof.value = false
  selectedPayment.value = null
}
</script>
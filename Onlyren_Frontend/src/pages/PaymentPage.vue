<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fetchReservationDetails, processPayment } from '@/api/payment'
import OnlyHeader from '@/components/OnlyHeader.vue'
import OnlyFooter from '@/components/OnlyFooter.vue'

// Component state
const reservation = ref(null)
const isLoading = ref(true)
const error = ref(null)

// Payment state
const selectedPaymentMethod = ref('')
const paymentForm = ref({
  email: '',
  phone: '',
  notes: ''
})
const paymentError = ref(null)
const isProcessing = ref(false)

// Route and navigation
const route = useRoute()
const router = useRouter()

// Payment methods
const paymentMethods = ref([
  {
    id: 'bank_transfer',
    name: 'Transfer Bank',
    icon: '🏦',
    description: 'BCA, Mandiri, BNI, BRI'
  },
  {
    id: 'e_wallet',
    name: 'E-Wallet',
    icon: '📱',
    description: 'GoPay, OVO, DANA, ShopeePay'
  },
  {
    id: 'credit_card',
    name: 'Kartu Kredit/Debit',
    icon: '💳',
    description: 'Visa, Mastercard'
  },
  {
    id: 'qris',
    name: 'QRIS',
    icon: '📋',
    description: 'Scan QR untuk bayar'
  }
])

// Fetch reservation details
const loadReservationDetails = async () => {
  isLoading.value = true
  try {
    reservation.value = await fetchReservationDetails(route.params.id)
  } catch (err) {
    error.value = 'Failed to load reservation details'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Calculate total price
const totalPrice = computed(() => {
  if (!reservation.value) return 0
  
  const startTime = new Date(`2000-01-01T${reservation.value.start_time}`)
  const endTime = new Date(`2000-01-01T${reservation.value.end_time}`)
  const hours = (endTime - startTime) / (1000 * 60 * 60)
  
  return hours * (reservation.value.room?.price_per_hour || 50000)
})

// Process payment
const submitPayment = async () => {
  if (!selectedPaymentMethod.value) {
    paymentError.value = 'Please select a payment method'
    return
  }

  paymentError.value = null
  isProcessing.value = true

  try {
    const paymentData = {
      reservation_id: route.params.id,
      payment_method: selectedPaymentMethod.value,
      amount: totalPrice.value,
      ...paymentForm.value
    }

    const payment = await processPayment(paymentData)
    
    // Redirect to confirmation page
    router.push(`/confirmation/${payment.id}`)
  } catch (err) {
    paymentError.value = 'Payment processing failed. Please try again.'
    console.error(err)
  } finally {
    isProcessing.value = false
  }
}

// Lifecycle hook
onMounted(loadReservationDetails)

// Utility methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(value)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

<template>
  <!-- Header -->
  <OnlyHeader />

  <div v-if="isLoading" class="text-center py-6">
    Loading payment details...
  </div>

  <div v-else-if="error" class="bg-red-100 text-red-800 p-4 rounded-lg">
    {{ error }}
  </div>

  <div v-else-if="reservation" class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pembayaran</h1>
        <p class="text-gray-600">Selesaikan pembayaran untuk mengkonfirmasi reservasi Anda</p>
      </div>

      <!-- Main Content Grid -->
      <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Payment Methods -->
        <div class="lg:col-span-2">
          <!-- Payment Methods -->
          <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold mb-6">Pilih Metode Pembayaran</h2>
            
            <div class="space-y-4">
              <div 
                v-for="method in paymentMethods" 
                :key="method.id"
                class="border border-gray-200 rounded-lg p-4 cursor-pointer transition-all hover:border-orange-300"
                :class="{ 'border-orange-500 bg-orange-50': selectedPaymentMethod === method.id }"
                @click="selectedPaymentMethod = method.id"
              >
                <div class="flex items-center">
                  <div class="flex-shrink-0 text-2xl mr-4">{{ method.icon }}</div>
                  <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ method.name }}</h3>
                    <p class="text-sm text-gray-500">{{ method.description }}</p>
                  </div>
                  <div class="flex-shrink-0">
                    <div 
                      class="w-5 h-5 rounded-full border-2 transition-all"
                      :class="selectedPaymentMethod === method.id 
                        ? 'border-orange-500 bg-orange-500' 
                        : 'border-gray-300'"
                    >
                      <div 
                        v-if="selectedPaymentMethod === method.id"
                        class="w-2 h-2 bg-white rounded-full m-0.5"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="text-xl font-semibold mb-6">Informasi Kontak</h2>
            
            <form class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input 
                  v-model="paymentForm.email"
                  type="email" 
                  required
                  placeholder="your@email.com"
                  class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Nomor Telepon</label>
                <input 
                  v-model="paymentForm.phone"
                  type="tel" 
                  required
                  placeholder="08xxxxxxxxxx"
                  class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-2">Catatan (Opsional)</label>
                <textarea 
                  v-model="paymentForm.notes"
                  rows="3"
                  placeholder="Tambahkan catatan khusus untuk reservasi Anda..."
                  class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                ></textarea>
              </div>
            </form>
          </div>
        </div>

        <!-- Right Column: Booking Summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-6">
            <div class="bg-white rounded-xl p-6 shadow-lg">
              <h3 class="text-lg font-semibold mb-4">Ringkasan Pesanan</h3>

              <!-- Room Info -->
              <div class="mb-6">
                <img 
                  src="https://www.ikea.com/images/an-open-plan-cafe-shop-and-co-working-space-framed-by-open-g-cc39d1c39508ca5cbe043d4bf962f798.jpg?f=xxxl" 
                  alt="Room"
                  class="w-full h-32 object-cover rounded-lg mb-3"
                />
                <h4 class="font-medium">{{ reservation.room?.name || 'Meeting Room' }}</h4>
                <p class="text-sm text-gray-500">{{ reservation.room?.location || 'Jakarta' }}</p>
              </div>

              <!-- Booking Details -->
              <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                <div class="flex justify-between">
                  <span class="text-gray-600">Tanggal</span>
                  <span class="font-medium">{{ formatDate(reservation.date) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Waktu</span>
                  <span class="font-medium">{{ reservation.start_time }} - {{ reservation.end_time }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Tamu</span>
                  <span class="font-medium">{{ reservation.guests }} orang</span>
                </div>
              </div>

              <!-- Price Breakdown -->
              <div class="space-y-3 mb-6">
                <div class="flex justify-between">
                  <span class="text-gray-600">Harga per jam</span>
                  <span>{{ formatCurrency(reservation.room?.price_per_hour || 50000) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Durasi</span>
                  <span>{{ Math.ceil((new Date(`2000-01-01T${reservation.end_time}`) - new Date(`2000-01-01T${reservation.start_time}`)) / (1000 * 60 * 60)) }} jam</span>
                </div>
                <div class="flex justify-between font-semibold text-lg pt-3 border-t border-gray-200">
                  <span>Total</span>
                  <span class="text-orange-600">{{ formatCurrency(totalPrice) }}</span>
                </div>
              </div>

              <!-- Payment Error -->
              <div 
                v-if="paymentError" 
                class="bg-red-100 text-red-800 p-3 rounded-lg mb-4 text-sm"
              >
                {{ paymentError }}
              </div>

              <!-- Payment Button -->
              <button 
                @click="submitPayment"
                :disabled="isProcessing || !selectedPaymentMethod"
                class="w-full py-3 rounded-lg font-medium transition-colors"
                :class="isProcessing || !selectedPaymentMethod 
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                  : 'bg-orange-500 text-white hover:bg-orange-600'"
              >
                <span v-if="isProcessing">Memproses...</span>
                <span v-else>Bayar Sekarang</span>
              </button>

              <!-- Security Notice -->
              <div class="mt-4 text-xs text-gray-500 text-center">
                <div class="flex items-center justify-center mb-1">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  Pembayaran Aman
                </div>
                <p>Data Anda dilindungi dengan enkripsi SSL</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <OnlyFooter />
</template>

<style scoped>
/* Additional custom styles if needed */
.payment-method-card {
  transition: all 0.2s ease;
}

.payment-method-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
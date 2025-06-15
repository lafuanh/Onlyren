<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchReservationForPayment, processPayment } from '@/api/payment'; 
import OnlyHeader from '@/components/OnlyHeader.vue';
import OnlyFooter from '@/components/OnlyFooter.vue';

// Component state
const reservation = ref(null);
const isLoading = ref(true);
const error = ref(null);
const isProcessing = ref(false);

// Payment state
const selectedPaymentMethod = ref('');
const paymentForm = ref({
  email: '',
  phone: '',
  notes: ''
});
const paymentError = ref(null);

const route = useRoute();
const router = useRouter();

// Payment methods
const paymentMethods = ref([
    { id: 'Bank Transfer', name: 'Transfer Bank', icon: '🏦', description: 'BCA, Mandiri, BNI, BRI' },
    { id: 'QRIS', name: 'QRIS', icon: '📋', description: 'Scan QR untuk bayar' },
    { id: 'Cash', name: 'Cash', icon: '💵', description: 'Bayar di tempat' }
]);

// --- Load real reservation data ---
const loadReservation = async () => {
  isLoading.value = true;
  try {
    const reservationId = route.params.id;
    const data = await fetchReservationForPayment(reservationId);
    reservation.value = data;
    // Pre-fill user email if available
    if(data.user?.email) {
        paymentForm.value.email = data.user.email;
    }
  } catch (err) {
    error.value = "Gagal memuat detail reservasi. Mungkin sudah tidak valid.";
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(loadReservation);

// Calculate total price
const totalPrice = computed(() => {
  return reservation.value?.total_amount || 0;
});

// Submit payment
const submitPayment = async () => {
  if (!selectedPaymentMethod.value) {
    paymentError.value = 'Silakan pilih metode pembayaran.';
    return;
  }

  paymentError.value = null;
  isProcessing.value = true;

  try {
    const paymentData = {
      method: selectedPaymentMethod.value,
      notes: paymentForm.value.notes
    };

    const result = await processPayment(reservation.value.id, paymentData);
    
    // Redirect to a confirmation page with the payment or reservation ID
    router.push(`/confirmation/${result.id}`); 
    
  } catch (err) {
    paymentError.value = err.message || 'Gagal memproses pembayaran. Silakan coba lagi.';
    console.error(err);
  } finally {
    isProcessing.value = false;
  }
};

// Utility methods
const formatCurrency = (value) => {
    if (typeof value !== 'number') return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};
const formatTime = (timeString) => {
    if (!timeString) return '';
    // Assuming time is in 'HH:mm:ss' format, just show 'HH:mm'
    return timeString.substring(0, 5);
};
</script>
<template>
  <!-- Header -->
  <OnlyHeader />

  <!-- Loading State -->
  <div v-if="isLoading" class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
      <p class="text-gray-600">Memuat detail pembayaran...</p>
    </div>
  </div>

  <!-- Error State -->
  <div v-else-if="error" class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
      <div class="text-red-500 text-6xl mb-4">⚠️</div>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Terjadi Kesalahan</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <button 
        @click="loadReservation" 
        class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600"
      >
        Coba Lagi
      </button>
    </div>
  </div>

  <!-- Main Content - Only show when reservation data is loaded -->
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
                  :src="reservation.room?.image || 'https://www.ikea.com/images/an-open-plan-cafe-shop-and-co-working-space-framed-by-open-g-cc39d1c39508ca5cbe043d4bf962f798.jpg?f=xxxl'" 
                  :alt="reservation.room?.name || 'Room'"
                  class="w-full h-32 object-cover rounded-lg mb-3"
                />
                <h4 class="font-medium">{{ reservation.room?.name || 'Meeting Room' }}</h4>
                <p class="text-sm text-gray-500">{{ reservation.room?.location || 'Location not specified' }}</p>
              </div>

              <!-- Booking Details -->
              <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                <div class="flex justify-between">
                  <span class="text-gray-600">Tanggal</span>
                  <span class="font-medium">{{ formatDate(reservation.date || reservation.start_date) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Waktu</span>
                  <span class="font-medium">
                    {{ formatTime(reservation.start_time) }} - {{ formatTime(reservation.end_time) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Tamu</span>
                  <span class="font-medium">{{ reservation.guests || 1 }} orang</span>
                </div>
              </div>

              <!-- Price Breakdown -->
              <div class="space-y-3 mb-6">
                <div class="flex justify-between">
                  <span class="text-gray-600">Harga per jam</span>
                  <span>{{ formatCurrency(reservation.room?.price_per_hour || reservation.price_per_hour || 0) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Durasi</span>
                  <span>
                    {{ 
                      reservation.duration_hours || 
                      Math.ceil((new Date(`2000-01-01T${reservation.end_time}`) - new Date(`2000-01-01T${reservation.start_time}`)) / (1000 * 60 * 60)) 
                    }} jam
                  </span>
                </div>
                <div v-if="reservation.service_fee" class="flex justify-between">
                  <span class="text-gray-600">Biaya layanan</span>
                  <span>{{ formatCurrency(reservation.service_fee) }}</span>
                </div>
                <div v-if="reservation.tax" class="flex justify-between">
                  <span class="text-gray-600">Pajak</span>
                  <span>{{ formatCurrency(reservation.tax) }}</span>
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
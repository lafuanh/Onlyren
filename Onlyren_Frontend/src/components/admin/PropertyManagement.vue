<!-- PropertyManagement.vue -->
<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">Kelola Properti</h1>
      <div class="flex space-x-3">
        <select 
          v-model="viewMode"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="all">Semua Properti</option>
          <option value="pending">Menunggu Persetujuan</option>
          <option value="approved">Disetujui</option>
          <option value="rejected">Ditolak</option>
        </select>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-md">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cari Properti</label>
          <input 
            v-model="searchTerm"
            type="text" 
            placeholder="Nama properti..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
          <select 
            v-model="filterCategory"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Semua Kategori</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
          <input 
            v-model="filterLocation"
            type="text" 
            placeholder="Kota/Provinsi..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Harga Maksimal</label>
          <input 
            v-model="filterMaxPrice"
            type="number" 
            placeholder="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
        </div>
        <div class="flex items-end">
          <button 
            @click="resetFilters"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            Reset Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Properties Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="property in filteredProperties" 
        :key="property.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
      >
        <!-- Property Image -->
        <div class="relative h-48 bg-gray-200">
          <img 
            v-if="property.images && property.images[0]"
            :src="property.images[0]" 
            :alt="property.name"
            class="w-full h-full object-cover"
          >
          <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
            <i class="fas fa-image text-4xl"></i>
          </div>
          
          <!-- Status Badge -->
          <div class="absolute top-3 left-3">
            <span :class="[
              'px-2 py-1 text-xs font-semibold rounded-full',
              getStatusClass(property.status)
            ]">
              {{ getStatusText(property.status) }}
            </span>
          </div>

          <!-- Category Badge -->
          <div class="absolute top-3 right-3">
            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
              {{ getCategoryText(property.category) }}
            </span>
          </div>
        </div>

        <!-- Property Details -->
        <div class="p-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ property.name }}</h3>
          <p class="text-sm text-gray-600 mb-2">
            <i class="fas fa-map-marker-alt mr-1"></i>{{ property.location }}
          </p>
          <p class="text-lg font-bold text-blue-600 mb-3">
            Rp {{ formatCurrency(property.price) }}
            <span class="text-sm font-normal text-gray-500">/{{ property.priceUnit }}</span>
          </p>

          <!-- Property Info -->
          <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
            <div class="flex items-center space-x-3">
              <span v-if="property.bedrooms">
                <i class="fas fa-bed mr-1"></i>{{ property.bedrooms }}
              </span>
              <span v-if="property.bathrooms">
                <i class="fas fa-bath mr-1"></i>{{ property.bathrooms }}
              </span>
              <span v-if="property.area">
                <i class="fas fa-expand-arrows-alt mr-1"></i>{{ property.area }}m²
              </span>
            </div>
          </div>

          <!-- Owner Info -->
          <div class="flex items-center mb-4 p-2 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
              {{ getInitials(property.ownerName) }}
            </div>
            <div class="ml-2">
              <p class="text-sm font-medium text-gray-900">{{ property.ownerName }}</p>
              <p class="text-xs text-gray-500">Pemilik</p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-2">
            <button 
              @click="viewProperty(property)"
              class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 text-sm rounded-lg hover:bg-blue-200 transition-colors"
            >
              <i class="fas fa-eye mr-1"></i>Detail
            </button>
            
            <button 
              v-if="property.status === 'pending'"
              @click="$emit('approve', property.id)"
              class="flex-1 px-3 py-2 bg-green-100 text-green-700 text-sm rounded-lg hover:bg-green-200 transition-colors"
            >
              <i class="fas fa-check mr-1"></i>Setujui
            </button>
            
            <button 
              v-if="property.status === 'pending'"
              @click="rejectProperty(property)"
              class="flex-1 px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200 transition-colors"
            >
              <i class="fas fa-times mr-1"></i>Tolak
            </button>

            <button 
              @click="deleteProperty(property)"
              class="px-3 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200 transition-colors"
            >
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredProperties.length === 0" class="text-center py-12">
      <i class="fas fa-building text-gray-400 text-6xl mb-4"></i>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada properti ditemukan</h3>
      <p class="text-gray-500">Coba ubah filter pencarian Anda</p>
    </div>

    <!-- Property Detail Modal -->
    <div v-if="showDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Detail Properti</h3>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div v-if="selectedProperty">
          <!-- Property Images -->
          <div v-if="selectedProperty.images && selectedProperty.images.length > 0" class="mb-4">
            <div class="grid grid-cols-2 gap-2">
              <img 
                v-for="(image, index) in selectedProperty.images.slice(0, 4)" 
                :key="index"
                :src="image" 
                :alt="`${selectedProperty.name} ${index + 1}`"
                class="w-full h-32 object-cover rounded-lg"
              >
            </div>
          </div>

          <!-- Property Info -->
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-gray-900">{{ selectedProperty.name }}</h4>
              <p class="text-gray-600">{{ selectedProperty.location }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Harga</label>
                <p class="text-lg font-bold text-blue-600">
                  Rp {{ formatCurrency(selectedProperty.price) }}/{{ selectedProperty.priceUnit }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                <p>{{ getCategoryText(selectedProperty.category) }}</p>
              </div>
            </div>

            <div v-if="selectedProperty.description">
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
              <p class="text-gray-600">{{ selectedProperty.description }}</p>
            </div>

            <div v-if="selectedProperty.facilities && selectedProperty.facilities.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-2">Fasilitas</label>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="facility in selectedProperty.facilities" 
                  :key="facility"
                  class="px-2 py-1 bg-gray-100 text-gray-700 text-sm rounded-lg"
                >
                  {{ facility }}
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Pemilik</label>
              <p>{{ selectedProperty.ownerName }}</p>
              <p class="text-sm text-gray-500">{{ selectedProperty.ownerEmail }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tolak Properti</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
            <textarea 
              v-model="rejectReason"
              rows="4"
              placeholder="Berikan alasan penolakan..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            ></textarea>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            @click="showRejectModal = false"
            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
          >
            Batal
          </button>
          <button 
            @click="confirmReject"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Tolak Properti
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  properties: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['approve', 'reject', 'delete'])

const searchTerm = ref('')
const filterCategory = ref('')
const filterLocation = ref('')
const filterMaxPrice = ref('')
const viewMode = ref('all')
const showDetailModal = ref(false)
const showRejectModal = ref(false)
const selectedProperty = ref(null)
const rejectingProperty = ref(null)
const rejectReason = ref('')

const filteredProperties = computed(() => {
  let filtered = props.properties

  // Filter by view mode
  if (viewMode.value !== 'all') {
    filtered = filtered.filter(property => property.status === viewMode.value)
  }

  // Filter by search term
  if (searchTerm.value) {
    filtered = filtered.filter(property => 
      property.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      property.location.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  }

  // Filter by category
  if (filterCategory.value) {
    filtered = filtered.filter(property => property.category === filterCategory.value)
  }

  // Filter by location
  if (filterLocation.value) {
    filtered = filtered.filter(property => 
      property.location.toLowerCase().includes(filterLocation.value.toLowerCase())
    )
  }

  // Filter by max price
  if (filterMaxPrice.value) {
    filtered = filtered.filter(property => property.price <= parseInt(filterMaxPrice.value))
  }

  return filtered
})

const getInitials = (name) => {
  if (!name) return 'P'
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return texts[status] || 'Unknown'
}

const getCategoryText = (category) => {
  const texts = {
    villa: 'Villa',
    apartment: 'Apartemen',
    house: 'Rumah',
    room: 'Kamar'
  }
  return texts[category] || category
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID').format(amount)
}

const resetFilters = () => {
  searchTerm.value = ''
  filterCategory.value = ''
  filterLocation.value = ''
  filterMaxPrice.value = ''
}

const viewProperty = (property) => {
  selectedProperty.value = property
  showDetailModal.value = true
}

const rejectProperty = (property) => {
  rejectingProperty.value = property
  showRejectModal.value = true
}

const confirmReject = () => {
  if (rejectReason.value.trim()) {
    emit('reject', rejectingProperty.value.id, rejectReason.value)
    showRejectModal.value = false
    rejectReason.value = ''
    rejectingProperty.value = null
  }
}

const deleteProperty = (property) => {
  if (confirm(`Apakah Anda yakin ingin menghapus properti ${property.name}?`)) {
    emit('delete', property.id)
  }
}
</script>
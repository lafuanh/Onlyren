<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/" class="flex items-center">
              <div class="text-2xl font-bold text-blue-600">Logo</div>
            </router-link>
          </div>
          <nav class="flex items-center space-x-6">
            <router-link to="/renter/profile" class="text-gray-600 hover:text-blue-600">Profile</router-link>
            <router-link to="/renter/rooms" class="text-gray-600 hover:text-blue-600">My Rooms</router-link>
            <router-link to="/reservations" class="text-gray-600 hover:text-blue-600">Reservations</router-link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Ruang</h1>
        <p class="text-gray-600 mt-2">Update informasi ruang kerja Anda</p>
      </div>

      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Left Column -->
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ruang</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Masukkan nama ruang"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 h-32 resize-none"
                placeholder="Deskripsikan ruang kerja Anda"
                required
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Ruang</label>
              <select
                v-model="form.type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Pilih tipe ruang</option>
                <option value="private_office">Private Office</option>
                <option value="meeting_room">Meeting Room</option>
                <option value="hot_desk">Hot Desk</option>
                <option value="dedicated_desk">Dedicated Desk</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas</label>
              <input
                v-model.number="form.capacity"
                type="number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Jumlah orang"
                min="1"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Jam</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                <input
                  v-model.number="form.price_per_hour"
                  type="number"
                  class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0"
                  min="0"
                  required
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
              <textarea
                v-model="form.address"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 h-24 resize-none"
                placeholder="Masukkan alamat lengkap"
                required
              ></textarea>
            </div>

            <!-- Current Images -->
            <div v-if="currentImages.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Saat Ini</label>
              <div class="grid grid-cols-2 gap-4">
                <div v-for="(image, index) in currentImages" :key="index" class="relative">
                  <img
                    :src="image.url"
                    :alt="`Room image ${index + 1}`"
                    class="w-full h-24 object-cover rounded-lg"
                  />
                  <button
                    type="button"
                    @click="removeImage(index)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                  >
                    ×
                  </button>
                </div>
              </div>
            </div>

            <!-- New Images Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Gambar Baru</label>
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                <input
                  ref="fileInput"
                  type="file"
                  multiple
                  accept="image/*"
                  @change="handleFileSelect"
                  class="hidden"
                />
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                  <button
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="text-blue-600 hover:text-blue-500"
                  >
                    Upload gambar
                  </button>
                  atau drag & drop
                </p>
                <p class="text-xs text-gray-500">PNG, JPG up to 10MB</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Amenities Section -->
        <div class="mt-8">
          <label class="block text-sm font-medium text-gray-700 mb-4">Fasilitas</label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <label v-for="amenity in availableAmenities" :key="amenity.id" class="flex items-center">
              <input
                type="checkbox"
                :value="amenity.id"
                v-model="form.amenities"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">{{ amenity.name }}</span>
            </label>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between">
          <button
            type="button"
            @click="confirmDelete"
            class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors"
          >
            Hapus Ruang
          </button>
          
          <div class="flex gap-4">
            <button
              type="button"
              @click="$router.go(-1)"
              class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-6 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Update Ruang' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
        <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus ruang ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex gap-4 justify-end">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Batal
          </button>
          <button
            @click="deleteRoom"
            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const isLoading = ref(true)
const isSubmitting = ref(false)
const showDeleteModal = ref(false)
const fileInput = ref(null)
const currentImages = ref([])

const form = reactive({
  name: '',
  description: '',
  type: '',
  capacity: null,
  price_per_hour: null,
  address: '',
  status: 'active',
  amenities: [],
  newImages: []
})

const availableAmenities = [
  { id: 1, name: 'WiFi' },
  { id: 2, name: 'AC' },
  { id: 3, name: 'Printer' },
  { id: 4, name: 'Projector' },
  { id: 5, name: 'Whiteboard' },
  { id: 6, name: 'Coffee/Tea' }
]

const loadRoomData = async () => {
  try {
    // Load room data from API
    const roomId = route.params.id
    // const room = await roomAPI.getById(roomId)
    
    // Mock data for demonstration
    const room = {
      id: roomId,
      name: 'Meeting Room A',
      description: 'Spacious meeting room with modern facilities',
      type: 'meeting_room',
      capacity: 8,
      price_per_hour: 150000,
      address: 'Jl. Ahmad Yani No. 123, Jakarta Pusat',
      status: 'active',
      amenities: [1, 2, 4],
      images: [
        { url: 'https://example.com/image1.jpg' },
        { url: 'https://example.com/image2.jpg' }
      ]
    }
    
    // Populate form
    Object.assign(form, room)
    currentImages.value = room.images
    
  } catch (error) {
    console.error('Error loading room data:', error)
  } finally {
    isLoading.value = false
  }
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  form.newImages = files
}

const removeImage = (index) => {
  currentImages.value.splice(index, 1)
}

const confirmDelete = () => {
  showDeleteModal.value = true
}

const deleteRoom = async () => {
  try {
    // Call API to delete room
    // await roomAPI.delete(route.params.id)
    
    showDeleteModal.value = false
    router.push('/renter/rooms')
  } catch (error) {
    console.error('Error deleting room:', error)
  }
}

const handleSubmit = async () => {
  try {
    isSubmitting.value = true
    
    // Create FormData for file upload
    const formData = new FormData()
    Object.keys(form).forEach(key => {
      if (key === 'newImages') {
        form.newImages.forEach(image => {
          formData.append('new_images[]', image)
        })
      } else if (key === 'amenities') {
        formData.append('amenities', JSON.stringify(form.amenities))
      } else {
        formData.append(key, form[key])
      }
    })
    
    // Add current images to keep
    formData.append('current_images', JSON.stringify(currentImages.value))
    
    // Call API to update room
    // await roomAPI.update(route.params.id, formData)
    
    // Redirect to rooms list
    router.push('/renter/rooms')
  } catch (error) {
    console.error('Error updating room:', error)
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadRoomData()
})
</script>
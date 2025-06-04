<template>
  <div class="space-y-6">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Manajemen Ruangan</h2>
      <button 
        @click="showAddModal = true"
        class="px-4 py-2 bg-orange-500 text-white rounded-lg transition-colors"
      >
        <i class="fas fa-plus mr-2"></i>Tambah Ruangan
      </button>
    </div>

    <!-- Rooms Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="room in rooms" 
        :key="room.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
      >
        <div class="relative">
          <img 
            :src="room.image || '/api/placeholder/300/200'" 
            :alt="room.name"
            class="w-full h-48 object-cover"
          />
          <span 
            :class="[
              'absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium',
              room.isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            ]"
          >
            {{ room.isActive ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>
        
        <div class="p-4">
          <h3 class="font-semibold text-gray-800 mb-2">{{ room.name }}</h3>
          <p class="text-sm text-gray-600 mb-3">{{ room.description }}</p>
          
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Kapasitas:</span>
              <span class="font-medium">{{ room.capacity }} orang</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Harga/jam:</span>
              <span class="font-medium text-blue-600">Rp {{ formatPrice(room.pricePerHour) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Fasilitas:</span>
              <span class="font-medium">{{ room.facilities?.join(', ') || '-' }}</span>
            </div>
          </div>
          
          <div class="flex justify-end space-x-2 mt-4">
            <button 
              @click="editRoom(room)"
              class="px-3 py-1 text-blue-600 border border-blue-600 rounded hover:bg-blue-50 transition-colors"
            >
              <i class="fas fa-edit mr-1"></i>Edit
            </button>
            <button 
              @click="deleteRoom(room.id)"
              class="px-3 py-1 text-red-600 border border-red-600 rounded hover:bg-red-50 transition-colors"
            >
              <i class="fas fa-trash mr-1"></i>Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Room Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">
              {{ showAddModal ? 'Tambah Ruangan' : 'Edit Ruangan' }}
            </h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <form @submit.prevent="saveRoom" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ruangan</label>
                <input 
                  v-model="roomForm.name"
                  type="text" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas</label>
                <input 
                  v-model.number="roomForm.capacity"
                  type="number" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Jam</label>
                <input 
                  v-model.number="roomForm.pricePerHour"
                  type="number" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                <input 
                  v-model="roomForm.location"
                  type="text" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
              <textarea 
                v-model="roomForm.description"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Fasilitas</label>
              <input 
                v-model="facilitiesInput"
                type="text" 
                placeholder="Pisahkan dengan koma (AC, WiFi, Proyektor)"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div class="flex items-center">
              <input 
                v-model="roomForm.isActive"
                type="checkbox" 
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-700">Ruangan Aktif</label>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4">
              <button 
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Batal
              </button>
              <button 
                type="submit"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
              >
                {{ showAddModal ? 'Tambah' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  rooms: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['add', 'edit', 'delete'])

const showAddModal = ref(false)
const showEditModal = ref(false)
const editingRoom = ref(null)

const roomForm = ref({
  name: '',
  description: '',
  capacity: 1,
  pricePerHour: 0,
  location: '',
  facilities: [],
  isActive: true
})

const facilitiesInput = computed({
  get: () => roomForm.value.facilities?.join(', ') || '',
  set: (value) => {
    roomForm.value.facilities = value.split(',').map(f => f.trim()).filter(f => f)
  }
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const editRoom = (room) => {
  editingRoom.value = room
  roomForm.value = { ...room }
  showEditModal.value = true
}

const deleteRoom = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus ruangan ini?')) {
    emit('delete', id)
  }
}

const saveRoom = () => {
  if (showAddModal.value) {
    emit('add', { ...roomForm.value })
  } else {
    emit('edit', { ...roomForm.value, id: editingRoom.value.id })
  }
  closeModal()
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingRoom.value = null
  roomForm.value = {
    name: '',
    description: '',
    capacity: 1,
    pricePerHour: 0,
    location: '',
    facilities: [],
    isActive: true
  }
}
</script>
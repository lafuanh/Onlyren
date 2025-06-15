<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-800">Manajemen Ruangan</h2>
      <button 
        @click="showAddModal = true"
        class="px-4 py-2 bg-orange-500 text-white rounded-lg transition-colors"
      >
        <i class="fas fa-plus mr-2"></i>Tambah Ruangan
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="room in rooms" 
        :key="room.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
      >
        <div class="relative">
          <img 
            :src="room.featured_image ? '/storage/' + room.featured_image : '/api/placeholder/300/200'" 
            :alt="room.name"
            class="w-full h-48 object-cover"
          />
          <span 
            :class="[
              'absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium',
              room.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            ]"
          >
            {{ room.is_available ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>
        
        <div class="p-4">
          <h3 class="font-semibold text-gray-800 mb-2">{{ room.name }}</h3>
          <p class="text-sm text-gray-600 mb-3">{{ room.description }}</p>
          
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Tipe:</span> <span class="font-medium">{{ room.type }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Kapasitas:</span>
              <span class="font-medium">{{ room.capacity }} orang</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Harga/jam:</span> <span class="font-medium text-orange-600">Rp {{ formatPrice(room.price_per_hour) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Harga/hari:</span> <span class="font-medium text-blue-600">Rp {{ formatPrice(room.price_per_day) }}</span>
            </div>
            <div class="flex justify-between" v-if="room.price_per_week">
              <span class="text-gray-500">Harga/minggu:</span>
              <span class="font-medium text-blue-600">Rp {{ formatPrice(room.price_per_week) }}</span>
            </div>
            <div class="flex justify-between" v-if="room.price_per_month">
              <span class="text-gray-500">Harga/bulan:</span>
              <span class="font-medium text-blue-600">Rp {{ formatPrice(room.price_per_month) }}</span>
            </div>
          </div>
          
          <div class="flex justify-end space-x-2 mt-4">
            <button @click="editRoom(room)" class="px-3 py-1 text-blue-600 border border-blue-600 rounded hover:bg-blue-50 transition-colors">
              <i class="fas fa-edit mr-1"></i>Edit
            </button>
            <button @click="deleteRoom(room.id)" class="px-3 py-1 text-red-600 border border-red-600 rounded hover:bg-red-50 transition-colors">
              <i class="fas fa-trash mr-1"></i>Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">{{ showAddModal ? 'Tambah Ruangan' : 'Edit Ruangan' }}</h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
          </div>
          
          <form @submit.prevent="saveRoom" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ruangan</label>
                <input v-model="roomForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                <input v-model="roomForm.location" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Ruangan</label>
                <select v-model="roomForm.type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                  <option value="" disabled>Pilih Tipe</option>
                  <option value="Meeting Room">Meeting Room</option>
                  <option value="Co-working Space">Co-working Space</option>
                  <option value="Private Office">Private Office</option>
                  <option value="Event Space">Event Space</option>
                  <option value="Studio">Studio</option>
                  <option value="General">Lainnya (General)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas (misal: 1-10 orang)</label>
                <input v-model="roomForm.capacity" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Jam (Rp)</label>
                <input v-model.number="roomForm.price_per_hour" type="number" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Hari (Rp)</label>
                <input v-model.number="roomForm.price_per_day" type="number" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Minggu (Rp)</label>
                <input v-model.number="roomForm.price_per_week" type="number" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Bulan (Rp)</label>
                <input v-model.number="roomForm.price_per_month" type="number" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>
            
            <textarea v-model="roomForm.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Deskripsi"></textarea>
            <textarea v-model="roomForm.specifications" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Spesifikasi (Opsional)"></textarea>
            <input v-model="amenitiesInput" type="text" placeholder="Fasilitas (Contoh: AC, WiFi, Proyektor)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
            <input type="file" @change="handleImageUpload" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            <div class="flex items-center"><input v-model="roomForm.is_available" id="is_available" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" /> <label for="is_available" class="ml-2 block text-sm text-gray-700">Ruangan Tersedia</label></div>
            
            <div class="flex justify-end space-x-2 pt-4">
              <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
              <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">{{ showAddModal ? 'Tambah' : 'Simpan' }}</button>
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
  rooms: { type: Array, default: () => [] }
})

const emit = defineEmits(['add', 'edit', 'delete'])

const showAddModal = ref(false)
const showEditModal = ref(false)
const editingRoom = ref(null)

// [REVISED] roomForm state now includes price_per_hour
const getInitialFormState = () => ({
  name: '',
  description: '',
  location: '',
  type: '',
  capacity: '',
  price_per_hour: null, // <<< ADDED
  price_per_day: null,
  price_per_week: null,
  price_per_month: null,
  specifications: '',
  amenities: [],
  featured_image: null,
  is_available: true,
});

const roomForm = ref(getInitialFormState());

const amenitiesInput = computed({
  get: () => roomForm.value.amenities?.join(', ') || '',
  set: (value) => {
    roomForm.value.amenities = value.split(',').map(f => f.trim()).filter(Boolean)
  }
})

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) roomForm.value.featured_image = file
}

const formatPrice = (price) => new Intl.NumberFormat('id-ID').format(price || 0)

// [REVISED] editRoom function now handles price_per_hour
const editRoom = (room) => {
  editingRoom.value = room;
  roomForm.value = {
    name: room.name || '',
    description: room.description || '',
    location: room.location || '',
    type: room.type || '',
    capacity: String(room.capacity) || '',
    specifications: room.specifications || '',
    price_per_hour: room.price_per_hour ?? null, // <<< ADDED
    price_per_day: room.price_per_day ?? null,
    price_per_week: room.price_per_week ?? null,
    price_per_month: room.price_per_month ?? null,
    amenities: Array.isArray(room.amenities) ? room.amenities : [],
    is_available: typeof room.is_available === 'boolean' ? room.is_available : true,
    featured_image: null,
  }
  showEditModal.value = true;
};

const deleteRoom = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus ruangan ini?')) {
    emit('delete', id)
  }
}

// [REVISED] saveRoom function now sends price_per_hour
const saveRoom = () => {
  const prepareFormData = (form) => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('description', form.description);
    formData.append('location', form.location);
    formData.append('type', form.type);
    formData.append('capacity', form.capacity);
    formData.append('price_per_hour', parseFloat(form.price_per_hour) || 0); // <<< ADDED
    formData.append('price_per_day', parseFloat(form.price_per_day) || 0);
    formData.append('price_per_week', parseFloat(form.price_per_week) || 0);
    formData.append('price_per_month', parseFloat(form.price_per_month) || 0);
    formData.append('specifications', form.specifications || '');
    formData.append('is_available', form.is_available ? 1 : 0);
    formData.append('amenities', JSON.stringify(form.amenities));
    if (form.featured_image instanceof File) {
      formData.append('featured_image', form.featured_image);
    }
    return formData;
  }

  if (showAddModal.value) {
    emit('add', prepareFormData(roomForm.value));
  } else {
    const formData = prepareFormData(roomForm.value);
    formData.append('_method', 'PUT'); // Laravel needs this for FormData PUT requests
    emit('edit', { id: editingRoom.value.id, data: formData });
  }
  closeModal();
}

// [REVISED] closeModal resets the new form state
const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingRoom.value = null
  roomForm.value = getInitialFormState();
}
</script>
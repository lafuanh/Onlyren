<!-- RenterProfileForm.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Profil Penyewa</h2>
    
    <form @submit.prevent="updateProfile" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama Bisnis</label>
          <input 
            v-model="formData.businessName"
            type="text" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Masukkan nama bisnis"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik</label>
          <input 
            v-model="formData.ownerName"
            type="text" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Masukkan nama pemilik"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input 
            v-model="formData.email"
            type="email" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Masukkan email"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
          <input 
            v-model="formData.phone"
            type="tel" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Masukkan nomor telepon"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nomor SIUP</label>
          <input 
            v-model="formData.siupNumber"
            type="text" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Masukkan nomor SIUP"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">NIB</label>
          <input 
            v-model="formData.nibNumber"
            type="text" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Masukkan NIB"
          />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Bisnis</label>
        <textarea 
          v-model="formData.businessAddress"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Masukkan alamat bisnis lengkap"
        ></textarea>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Bisnis</label>
        <textarea 
          v-model="formData.businessDescription"
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Jelaskan tentang bisnis Anda"
        ></textarea>
      </div>
      
      <div class="flex justify-end">
        <button 
          type="submit"
          class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
        >
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  profile: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update'])

const formData = ref({
  businessName: '',
  ownerName: '',
  email: '',
  phone: '',
  siupNumber: '',
  nibNumber: '',
  businessAddress: '',
  businessDescription: ''
})

watch(() => props.profile, (newProfile) => {
  formData.value = { ...newProfile }
}, { immediate: true, deep: true })

const updateProfile = () => {
  emit('update', formData.value)
}
</script>


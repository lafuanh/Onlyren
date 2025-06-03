<!-- ProfileForm.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Profil Saya</h2>
    
    <form @submit.prevent="updateProfile" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
          <input 
            v-model="formData.name"
            type="text" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="Masukkan nama lengkap"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input 
            v-model="formData.email"
            type="email" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="Masukkan email"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
          <input 
            v-model="formData.phone"
            type="tel" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="Masukkan nomor telepon"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
          <input 
            v-model="formData.birthDate"
            type="date" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
        <textarea 
          v-model="formData.address"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          placeholder="Masukkan alamat lengkap"
        ></textarea>
      </div>
      
      <div class="flex justify-end">
        <button 
          type="submit"
          class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
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
  name: '',
  email: '',
  phone: '',
  birthDate: '',
  address: ''
})

watch(() => props.profile, (newProfile) => {
  formData.value = { ...newProfile }
}, { immediate: true, deep: true })

const updateProfile = () => {
  emit('update', formData.value)
}
</script>
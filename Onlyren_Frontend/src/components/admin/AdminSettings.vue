<!-- AdminSettings.vue -->
<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Pengaturan Sistem</h2>
      <p class="text-gray-600">Kelola pengaturan aplikasi dan konfigurasi sistem</p>
    </div>

    <!-- Settings Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8 px-6">
          <button
            v-for="tab in settingsTabs"
            :key="tab.id"
            @click="activeSettingsTab = tab.id"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeSettingsTab === tab.id
                ? 'border-red-600 text-red-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <i :class="tab.icon + ' mr-2'"></i>{{ tab.name }}
          </button>
        </nav>
      </div>

      <div class="p-6">
        <!-- General Settings -->
        <div v-if="activeSettingsTab === 'general'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Umum</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aplikasi</label>
              <input
                v-model="localSettings.appName"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="Masukkan nama aplikasi"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Aplikasi</label>
              <input
                v-model="localSettings.appDescription"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="Masukkan deskripsi aplikasi"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email Kontak</label>
              <input
                v-model="localSettings.contactEmail"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="admin@example.com"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
              <input
                v-model="localSettings.contactPhone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="+62 XXX XXXX XXXX"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Perusahaan</label>
            <textarea
              v-model="localSettings.companyAddress"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              placeholder="Masukkan alamat lengkap perusahaan"
            ></textarea>
          </div>

          <div class="flex items-center">
            <input
              v-model="localSettings.maintenanceMode"
              type="checkbox"
              id="maintenanceMode"
              class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
            >
            <label for="maintenanceMode" class="ml-2 text-sm font-medium text-gray-700">
              Mode Maintenance
            </label>
            <span class="ml-2 text-xs text-gray-500">(Hanya admin yang dapat mengakses)</span>
          </div>
        </div>

        <!-- User Settings -->
        <div v-if="activeSettingsTab === 'users'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Pengguna</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center">
              <input
                v-model="localSettings.allowUserRegistration"
                type="checkbox"
                id="allowUserRegistration"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
              <label for="allowUserRegistration" class="ml-2 text-sm font-medium text-gray-700">
                Izinkan Registrasi Pengguna Baru
              </label>
            </div>

            <div class="flex items-center">
              <input
                v-model="localSettings.requireEmailVerification"
                type="checkbox"
                id="requireEmailVerification"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
              <label for="requireEmailVerification" class="ml-2 text-sm font-medium text-gray-700">
                Wajib Verifikasi Email
              </label>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Password Minimum Length</label>
              <input
                v-model.number="localSettings.minPasswordLength"
                type="number"
                min="6"
                max="50"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (menit)</label>
              <input
                v-model.number="localSettings.sessionTimeout"
                type="number"
                min="30"
                max="1440"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>
          </div>

          <div class="flex items-center">
            <input
              v-model="localSettings.enableTwoFactorAuth"
              type="checkbox"
              id="enableTwoFactorAuth"
              class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
            >
            <label for="enableTwoFactorAuth" class="ml-2 text-sm font-medium text-gray-700">
              Aktifkan Two-Factor Authentication
            </label>
          </div>
        </div>

        <!-- Property Settings -->
        <div v-if="activeSettingsTab === 'properties'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Properti</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center">
              <input
                v-model="localSettings.requirePropertyApproval"
                type="checkbox"
                id="requirePropertyApproval"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
              <label for="requirePropertyApproval" class="ml-2 text-sm font-medium text-gray-700">
                Wajib Persetujuan Admin untuk Properti Baru
              </label>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Maksimal Upload Gambar</label>
              <input
                v-model.number="localSettings.maxPropertyImages"
                type="number"
                min="1"
                max="20"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Maksimal File (MB)</label>
              <input
                v-model.number="localSettings.maxFileSize"
                type="number"
                min="1"
                max="10"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Komisi Platform (%)</label>
              <input
                v-model.number="localSettings.platformCommission"
                type="number"
                min="0"
                max="30"
                step="0.1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Format Gambar yang Diizinkan</label>
            <div class="flex flex-wrap gap-4">
              <label v-for="format in imageFormats" :key="format" class="flex items-center">
                <input
                  v-model="localSettings.allowedImageFormats"
                  :value="format"
                  type="checkbox"
                  class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
                >
                <span class="ml-2 text-sm text-gray-700">{{ format.toUpperCase() }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Notification Settings -->
        <div v-if="activeSettingsTab === 'notifications'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Notifikasi</h3>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <h4 class="font-medium text-gray-800">Email Notifikasi</h4>
                <p class="text-sm text-gray-600">Kirim notifikasi melalui email</p>
              </div>
              <input
                v-model="localSettings.emailNotifications"
                type="checkbox"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <h4 class="font-medium text-gray-800">SMS Notifikasi</h4>
                <p class="text-sm text-gray-600">Kirim notifikasi melalui SMS</p>
              </div>
              <input
                v-model="localSettings.smsNotifications"
                type="checkbox"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <h4 class="font-medium text-gray-800">Push Notifikasi</h4>
                <p class="text-sm text-gray-600">Kirim push notification ke aplikasi mobile</p>
              </div>
              <input
                v-model="localSettings.pushNotifications"
                type="checkbox"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <h4 class="font-medium text-gray-800">Notifikasi Admin untuk Properti Baru</h4>
                <p class="text-sm text-gray-600">Beritahu admin ketika ada properti baru yang perlu disetujui</p>
              </div>
              <input
                v-model="localSettings.adminPropertyNotifications"
                type="checkbox"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <h4 class="font-medium text-gray-800">Notifikasi Pembayaran</h4>
                <p class="text-sm text-gray-600">Beritahu admin ketika ada pembayaran yang perlu diverifikasi</p>
              </div>
              <input
                v-model="localSettings.paymentNotifications"
                type="checkbox"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
            </div>
          </div>
        </div>

        <!-- Payment Settings -->
        <div v-if="activeSettingsTab === 'payments'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Pembayaran</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mata Uang Default</label>
              <select
                v-model="localSettings.defaultCurrency"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
                <option value="IDR">Rupiah (IDR)</option>
                <option value="USD">US Dollar (USD)</option>
                <option value="EUR">Euro (EUR)</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Timeout Pembayaran (jam)</label>
              <input
                v-model.number="localSettings.paymentTimeout"
                type="number"
                min="1"
                max="72"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Gateway Pembayaran Aktif</label>
            <div class="space-y-3">
              <div v-for="gateway in paymentGateways" :key="gateway.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                  <img :src="gateway.logo" :alt="gateway.name" class="w-8 h-8 mr-3">
                  <div>
                    <h4 class="font-medium text-gray-800">{{ gateway.name }}</h4>
                    <p class="text-sm text-gray-600">{{ gateway.description }}</p>
                  </div>
                </div>
                <input
                  v-model="localSettings.activePaymentGateways"
                  :value="gateway.id"
                  type="checkbox"
                  class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Security Settings -->
        <div v-if="activeSettingsTab === 'security'" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Keamanan</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
              <input
                v-model.number="localSettings.maxLoginAttempts"
                type="number"
                min="3"
                max="10"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Account Lockout Duration (menit)</label>
              <input
                v-model.number="localSettings.lockoutDuration"
                type="number"
                min="15"
                max="1440"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              >
            </div>
          </div>

          <div class="space-y-4">
            <div class="flex items-center">
              <input
                v-model="localSettings.enableCaptcha"
                type="checkbox"
                id="enableCaptcha"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
              <label for="enableCaptcha" class="ml-2 text-sm font-medium text-gray-700">
                Aktifkan CAPTCHA pada Login
              </label>
            </div>

            <div class="flex items-center">
              <input
                v-model="localSettings.enableIPWhitelist"
                type="checkbox"
                id="enableIPWhitelist"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
              <label for="enableIPWhitelist" class="ml-2 text-sm font-medium text-gray-700">
                Aktifkan IP Whitelist untuk Admin
              </label>
            </div>

            <div class="flex items-center">
              <input
                v-model="localSettings.logSecurityEvents"
                type="checkbox"
                id="logSecurityEvents"
                class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500"
              >
              <label for="logSecurityEvents" class="ml-2 text-sm font-medium text-gray-700">
                Log Security Events
              </label>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-6 border-t border-gray-200">
          <button
            @click="saveSettings"
            :disabled="isSaving"
            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <i v-if="isSaving" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-save mr-2"></i>
            {{ isSaving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update'])

const activeSettingsTab = ref('general')
const isSaving = ref(false)

const settingsTabs = [
  { id: 'general', name: 'Umum', icon: 'fas fa-cog' },
  { id: 'users', name: 'Pengguna', icon: 'fas fa-users' },
  { id: 'properties', name: 'Properti', icon: 'fas fa-building' },
  { id: 'notifications', name: 'Notifikasi', icon: 'fas fa-bell' },
  { id: 'payments', name: 'Pembayaran', icon: 'fas fa-credit-card' },
  { id: 'security', name: 'Keamanan', icon: 'fas fa-shield-alt' }
]

const imageFormats = ['jpg', 'jpeg', 'png', 'webp', 'gif']

const paymentGateways = [
  {
    id: 'midtrans',
    name: 'Midtrans',
    description: 'Gateway pembayaran lokal Indonesia',
    logo: '/images/midtrans-logo.png'
  },
  {
    id: 'xendit',
    name: 'Xendit',
    description: 'Platform pembayaran digital',
    logo: '/images/xendit-logo.png'
  },
  {
    id: 'ovo',
    name: 'OVO',
    description: 'E-wallet OVO',
    logo: '/images/ovo-logo.png'
  },
  {
    id: 'gopay',
    name: 'GoPay',
    description: 'E-wallet GoPay',
    logo: '/images/gopay-logo.png'
  }
]

const localSettings = reactive({
  // General Settings
  appName: '',
  appDescription: '',
  contactEmail: '',
  contactPhone: '',
  companyAddress: '',
  maintenanceMode: false,

  // User Settings
  allowUserRegistration: true,
  requireEmailVerification: true,
  minPasswordLength: 8,
  sessionTimeout: 120,
  enableTwoFactorAuth: false,

  // Property Settings
  requirePropertyApproval: true,
  maxPropertyImages: 10,
  maxFileSize: 5,
  platformCommission: 5.0,
  allowedImageFormats: ['jpg', 'jpeg', 'png', 'webp'],

  // Notification Settings
  emailNotifications: true,
  smsNotifications: false,
  pushNotifications: true,
  adminPropertyNotifications: true,
  paymentNotifications: true,

  // Payment Settings
  defaultCurrency: 'IDR',
  paymentTimeout: 24,
  activePaymentGateways: ['midtrans', 'ovo'],

  // Security Settings
  maxLoginAttempts: 5,
  lockoutDuration: 30,
  enableCaptcha: true,
  enableIPWhitelist: false,
  logSecurityEvents: true
})

const saveSettings = async () => {
  isSaving.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
    emit('update', { ...localSettings })
  } catch (error) {
    console.error('Failed to save settings:', error)
  } finally {
    isSaving.value = false
  }
}

// Initialize local settings with props
watch(() => props.settings, (newSettings) => {
  Object.assign(localSettings, newSettings)
}, { immediate: true, deep: true })

onMounted(() => {
  // Initialize with default values if props.settings is empty
  if (Object.keys(props.settings).length === 0) {
    // Keep default values from localSettings
  }
})
</script>
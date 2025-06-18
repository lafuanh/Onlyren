<!-- AdminSettings.vue -->
<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
        <p class="text-gray-600">Manage platform settings and configurations</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- General Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
            <input 
              type="text" 
              v-model="settings.site_name"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
            <input 
              type="email" 
              v-model="settings.contact_email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Mode</label>
            <div class="flex items-center">
              <input 
                type="checkbox" 
                v-model="settings.maintenance_mode"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 text-sm text-gray-700">Enable maintenance mode</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Settings</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Commission Rate (%)</label>
            <input 
              type="number" 
              v-model="settings.commission_rate"
              min="0"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Methods</label>
            <div class="space-y-2">
              <div class="flex items-center">
                <input 
                  type="checkbox" 
                  v-model="settings.payment_methods.bank_transfer"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Bank Transfer</label>
              </div>
              <div class="flex items-center">
                <input 
                  type="checkbox" 
                  v-model="settings.payment_methods.qris"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">QRIS</label>
              </div>
              <div class="flex items-center">
                <input 
                  type="checkbox" 
                  v-model="settings.payment_methods.cash"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label class="ml-2 text-sm text-gray-700">Cash</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-end">
        <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
          Save Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({
      site_name: 'OnlyRent',
      contact_email: 'admin@onlyrent.com',
      maintenance_mode: false,
      commission_rate: 10,
      payment_methods: {
        bank_transfer: true,
        qris: true,
        cash: true
      }
    })
  }
})
</script>
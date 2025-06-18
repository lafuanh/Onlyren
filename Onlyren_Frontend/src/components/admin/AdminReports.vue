<!-- AdminReports.vue -->
<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="text-gray-600">View detailed reports and analytics</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Reservation Report -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservation Report</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Reservations</span>
            <span class="font-semibold">{{ reports.reservations?.total || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Pending</span>
            <span class="text-yellow-600 font-semibold">{{ reports.reservations?.pending || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Completed</span>
            <span class="text-green-600 font-semibold">{{ reports.reservations?.completed || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Cancelled</span>
            <span class="text-red-600 font-semibold">{{ reports.reservations?.cancelled || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Payment Report -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Report</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Revenue</span>
            <span class="font-semibold">{{ formatCurrency(reports.payments?.total_revenue || 0) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Pending Payments</span>
            <span class="text-yellow-600 font-semibold">{{ reports.payments?.pending || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Successful Payments</span>
            <span class="text-green-600 font-semibold">{{ reports.payments?.successful || 0 }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Failed Payments</span>
            <span class="text-red-600 font-semibold">{{ reports.payments?.failed || 0 }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Reports</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-file-excel text-green-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Export to Excel</p>
        </button>
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-file-pdf text-red-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Export to PDF</p>
        </button>
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-chart-line text-blue-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Generate Charts</p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  reports: {
    type: Object,
    default: () => ({})
  }
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}
</script>
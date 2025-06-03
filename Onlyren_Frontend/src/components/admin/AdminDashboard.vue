<!-- AdminDashboard.vue -->
<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
      <div class="text-sm text-gray-500">
        Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID') }}
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-users text-blue-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalUsers || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-building text-green-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Properti</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalProperties || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-clipboard-list text-yellow-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pesanan Aktif</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.activeOrders || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-rupiah-sign text-purple-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pendapatan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ formatCurrency(stats.monthlyRevenue || 0) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activities -->
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
        <div class="space-y-4">
          <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="flex-shrink-0">
              <i :class="getActivityIcon(activity.type)" class="text-gray-600"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">{{ activity.message }}</p>
              <p class="text-xs text-gray-500">{{ formatTime(activity.timestamp) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Approvals -->
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Menunggu Persetujuan</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
            <div>
              <p class="text-sm font-medium text-gray-900">Properti Baru</p>
              <p class="text-xs text-gray-500">{{ stats.pendingProperties || 0 }} properti menunggu review</p>
            </div>
            <button class="px-3 py-1 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600">
              Review
            </button>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div>
              <p class="text-sm font-medium text-gray-900">Pembayaran</p>
              <p class="text-xs text-gray-500">{{ stats.pendingPayments || 0 }} pembayaran menunggu verifikasi</p>
            </div>
            <button class="px-3 py-1 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600">
              Verifikasi
            </button>
          </div>

          <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
            <div>
              <p class="text-sm font-medium text-gray-900">Laporan</p>
              <p class="text-xs text-gray-500">{{ stats.pendingReports || 0 }} laporan menunggu tindakan</p>
            </div>
            <button class="px-3 py-1 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600">
              Tindak Lanjut
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Pendapatan (6 Bulan Terakhir)</h3>
        <div class="h-64 flex items-center justify-center text-gray-500">
          <!-- Placeholder for chart -->
          <div class="text-center">
            <i class="fas fa-chart-line text-4xl mb-2"></i>
            <p>Chart akan ditampilkan di sini</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Jenis Properti</h3>
        <div class="h-64 flex items-center justify-center text-gray-500">
          <!-- Placeholder for pie chart -->
          <div class="text-center">
            <i class="fas fa-chart-pie text-4xl mb-2"></i>
            <p>Pie Chart akan ditampilkan di sini</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})

const recentActivities = computed(() => [
  {
    id: 1,
    type: 'user',
    message: 'Pengguna baru mendaftar: John Doe',
    timestamp: new Date(Date.now() - 5 * 60 * 1000)
  },
  {
    id: 2,
    type: 'property',
    message: 'Properti baru ditambahkan: Villa Sunset',
    timestamp: new Date(Date.now() - 15 * 60 * 1000)
  },
  {
    id: 3,
    type: 'payment',
    message: 'Pembayaran diverifikasi untuk pesanan #1234',
    timestamp: new Date(Date.now() - 30 * 60 * 1000)
  },
  {
    id: 4,
    type: 'order',
    message: 'Pesanan baru diterima dari Jane Smith',
    timestamp: new Date(Date.now() - 45 * 60 * 1000)
  }
])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID').format(amount)
}

const formatTime = (timestamp) => {
  return new Intl.RelativeTimeFormat('id-ID').format(
    Math.round((timestamp - new Date()) / (1000 * 60)),
    'minute'
  )
}

const getActivityIcon = (type) => {
  const icons = {
    user: 'fas fa-user-plus',
    property: 'fas fa-building',
    payment: 'fas fa-credit-card',
    order: 'fas fa-shopping-cart'
  }
  return icons[type] || 'fas fa-info-circle'
}
</script>
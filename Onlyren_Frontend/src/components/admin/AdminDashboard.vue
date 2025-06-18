<!-- AdminDashboard.vue -->
<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600">Overview of your platform's performance</p>
      </div>
      <div class="text-sm text-gray-500">
        Last updated: {{ new Date().toLocaleString() }}
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Users -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <i class="fas fa-users text-xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Users</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_users || 0 }}</p>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> +12% from last month
          </span>
        </div>
      </div>

      <!-- Total Rooms -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600">
            <i class="fas fa-building text-xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Rooms</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_rooms || 0 }}</p>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> +8% from last month
          </span>
        </div>
      </div>

      <!-- Total Reservations -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
            <i class="fas fa-calendar-check text-xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Reservations</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_reservations || 0 }}</p>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> +15% from last month
          </span>
        </div>
      </div>

      <!-- Total Revenue -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100 text-purple-600">
            <i class="fas fa-dollar-sign text-xl"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.total_revenue || 0) }}</p>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> +20% from last month
          </span>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activities -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
        <div class="space-y-4">
          <div v-for="activity in stats.recent_activities || []" :key="activity.id" class="flex items-center">
            <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
            <div class="flex-1">
              <p class="text-sm text-gray-900">{{ activity.description }}</p>
              <p class="text-xs text-gray-500">{{ formatTime(activity.created_at) }}</p>
            </div>
          </div>
          <div v-if="!stats.recent_activities || stats.recent_activities.length === 0" class="text-center text-gray-500 py-4">
            No recent activities
          </div>
        </div>
      </div>

      <!-- Top Performing Rooms -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performing Rooms</h3>
        <div class="space-y-4">
          <div v-for="room in stats.top_rooms || []" :key="room.id" class="flex items-center justify-between">
            <div class="flex items-center">
              <img :src="room.image || '/default-room.jpg'" :alt="room.name" class="w-10 h-10 rounded-lg object-cover mr-3">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ room.name }}</p>
                <p class="text-xs text-gray-500">{{ room.location }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900">{{ room.reservation_count }} bookings</p>
              <p class="text-xs text-gray-500">{{ formatCurrency(room.revenue) }}</p>
            </div>
          </div>
          <div v-if="!stats.top_rooms || stats.top_rooms.length === 0" class="text-center text-gray-500 py-4">
            No rooms data available
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-user-plus text-blue-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Add User</p>
        </button>
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-building text-green-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Add Room</p>
        </button>
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-chart-bar text-purple-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">View Reports</p>
        </button>
        <button class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
          <i class="fas fa-cog text-gray-600 text-xl mb-2"></i>
          <p class="text-sm font-medium text-gray-900">Settings</p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  stats: {
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

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
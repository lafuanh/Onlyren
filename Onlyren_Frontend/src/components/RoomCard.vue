<script setup>
import { defineProps } from 'vue'

// Define the props that will be passed from the parent component
const props = defineProps({
  room: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="bg-white shadow-md rounded-lg overflow-hidden cursor-pointer hover:shadow-xl transition-shadow">
    <!-- Room Image -->
    <img 
      :src="room.coverImage" 
      :alt="room.name" 
      class="w-full h-48 object-cover"
    />

    <!-- Room Details -->
    <div class="p-4">
      <!-- Room Name -->
      <h3 class="text-xl font-semibold mb-2">{{ room.name }}</h3>

      <!-- Room Type and Location -->
      <div class="flex items-center text-gray-600 mb-2">
        <span>{{ room.type }}</span>
        <span class="mx-2">•</span>
        <span>{{ room.location }}</span>
      </div>

      <!-- Price -->
      <div class="flex justify-between items-center mb-2">
        <span class="text-lg font-semibold text-blue-600">
          Rp {{ room.price_per_hour.toLocaleString() }} / hour
        </span>
      </div>

      <!-- Amenities -->
      <div class="flex flex-wrap gap-2 mt-2">
        <span 
          v-for="(amenity, index) in room.amenities.slice(0, 3)" 
          :key="index"
          class="bg-blue-50 text-blue-600 px-2 py-1 rounded-full text-xs"
        >
          {{ amenity }}
        </span>
        <!-- Optional: Display more amenities -->
        <span 
          v-if="room.amenities.length > 3"
          class="text-blue-500 text-xs"
        >
          +{{ room.amenities.length - 3 }} more
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add your custom styles here */
</style>

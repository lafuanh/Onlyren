<!-- src/components/ImageGallery.vue -->
<template>
  <div class="space-x-4">
    <img 
      v-for="(image, index) in images" 
      :key="index" 
      :src="image" 
      alt="Room Image" 
      class="w-1/3 rounded-lg"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  images: {
    type: Array,
    required: true
  }
})
</script>

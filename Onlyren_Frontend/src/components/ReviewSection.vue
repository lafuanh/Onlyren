<!-- src/components/ReviewSection.vue -->
<template>
  <div>
    <h3 class="text-lg font-semibold mb-4">Reviews</h3>
    <div v-if="reviews.length === 0" class="text-gray-500">
      No reviews yet.
    </div>

    <div v-else>
      <div v-for="review in reviews" :key="review.id" class="mb-4 p-4 bg-gray-100 rounded-lg">
        <div class="flex items-center mb-2">
          <span class="font-bold">{{ review.user_name }}</span>
          <span class="ml-2 text-sm text-gray-500">{{ review.created_at }}</span>
        </div>
        <p>{{ review.comment }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
//import { fetchReviews } from '@/api/room'

const props = defineProps({
  roomId: {
    type: Number,
    required: true
  }
})

const reviews = ref([])

const loadReviews = async () => {
  try {
    reviews.value = await fetchReviews(props.roomId)
  } catch (err) {
    console.error('Error loading reviews:', err)
  }
}

onMounted(loadReviews)
</script>
